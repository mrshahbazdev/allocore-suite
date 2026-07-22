<?php

namespace Modules\DentalTrack\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\DentalTrack\Enums\OrderStatus;
use Modules\DentalTrack\Enums\ScanEventType;
use Modules\DentalTrack\Models\Order;
use Modules\DentalTrack\Models\Prediction;
use Modules\DentalTrack\Models\ProcessTemplate;
use Modules\DentalTrack\Models\ScanEvent;
use Modules\DentalTrack\Models\Workstation;

class PredictionService
{
    private const MODEL_VERSION = 'v2-weighted-avg';

    private const MIN_HISTORY_COUNT = 3;

    public function predictCompletion(Order $order): ?array
    {
        $productType = $order->productType;
        if ($productType === null) {
            return null;
        }

        $templates = $productType->processTemplates;
        if ($templates->isEmpty()) {
            return null;
        }

        $totalSteps = $order->steps()->count();
        if ($totalSteps === 0) {
            return null;
        }

        $completedSteps = $order->steps()->where('status', 'done')->count();
        $remainingRatio = max(0, ($totalSteps - $completedSteps) / $totalSteps);

        $historicalData = $this->getHistoricalData($productType->id);
        $technicianFactor = $this->getTechnicianSpeedFactor($order);
        $queuePenalty = $this->getQueueDepthPenalty($order);

        if ($historicalData !== null) {
            $basePrediction = $historicalData['avg_minutes'] * $remainingRatio;
            $adjusted = $basePrediction * $technicianFactor + $queuePenalty;
            $predictedMinutes = max(1, (int) round($adjusted));
            $confidence = (int) round($historicalData['std_dev'] * $remainingRatio);
            $basis = "Based on {$historicalData['count']} similar completed orders";
            $similarCount = $historicalData['count'];
        } else {
            $remainingExpected = 0;
            foreach ($templates as $template) {
                if ($template instanceof ProcessTemplate && $template->sort_order > $completedSteps) {
                    $remainingExpected += $template->expected_minutes ?? 0;
                }
            }
            $adjusted = $remainingExpected * $technicianFactor + $queuePenalty;
            $predictedMinutes = max(1, (int) round($adjusted));
            $confidence = (int) round($predictedMinutes * 0.3);
            $basis = 'Based on process template estimates';
            $similarCount = 0;
        }

        $prediction = Prediction::create([
            'dentaltrack_order_id' => $order->id,
            'model_version' => self::MODEL_VERSION,
            'predicted_minutes' => $predictedMinutes,
        ]);

        $order->update([
            'predicted_completion_at' => Carbon::now()->addMinutes($predictedMinutes),
        ]);

        return [
            'prediction' => $prediction,
            'confidence_minutes' => $confidence,
            'basis' => $basis,
            'similar_count' => $similarCount,
        ];
    }

    private function getHistoricalData(int $productTypeId): ?array
    {
        $completedOrders = Order::where('dentaltrack_product_type_id', $productTypeId)
            ->where('status', OrderStatus::Completed)
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->limit(100)
            ->get();

        if ($completedOrders->count() < self::MIN_HISTORY_COUNT) {
            return null;
        }

        $durations = [];
        foreach ($completedOrders as $order) {
            $firstScan = ScanEvent::where('dentaltrack_order_id', $order->id)
                ->orderBy('scanned_at')
                ->value('scanned_at');

            if ($firstScan === null || $order->completed_at === null) {
                continue;
            }

            $durations[] = (float) Carbon::parse($firstScan)->diffInMinutes($order->completed_at);
        }

        if (count($durations) < self::MIN_HISTORY_COUNT) {
            return null;
        }

        $avg = array_sum($durations) / count($durations);
        $variance = array_sum(array_map(fn (float $d) => ($d - $avg) ** 2, $durations)) / count($durations);
        $stdDev = sqrt($variance);

        return [
            'avg_minutes' => $avg,
            'std_dev' => $stdDev,
            'count' => count($durations),
        ];
    }

    private function getTechnicianSpeedFactor(Order $order): float
    {
        $currentStep = $order->currentStep();
        if ($currentStep === null) {
            return 1.0;
        }

        $assignedTo = $currentStep->assigned_to;
        if ($assignedTo === null) {
            return 1.0;
        }

        $techAvg = ScanEvent::where('user_id', $assignedTo)
            ->where('event_type', ScanEventType::Complete)
            ->whereNotNull('duration_seconds')
            ->avg('duration_seconds');

        $globalAvg = ScanEvent::where('event_type', ScanEventType::Complete)
            ->whereNotNull('duration_seconds')
            ->avg('duration_seconds');

        if ($techAvg === null || $globalAvg === null || (float) $globalAvg === 0.0) {
            return 1.0;
        }

        $factor = (float) $techAvg / (float) $globalAvg;

        return max(0.5, min(2.0, $factor));
    }

    private function getQueueDepthPenalty(Order $order): float
    {
        $currentStep = $order->currentStep();
        if ($currentStep === null) {
            return 0;
        }

        $latestEvent = $order->latestScanEvent();
        if ($latestEvent === null) {
            return 0;
        }

        $workstationId = $latestEvent->dentaltrack_workstation_id;
        $queueDepth = ScanEvent::where('dentaltrack_workstation_id', $workstationId)
            ->where('event_type', ScanEventType::Start)
            ->where('scanned_at', '>=', Carbon::now()->subHours(8))
            ->whereNotIn('dentaltrack_order_id', function ($query) {
                $query->select('id')->from('dentaltrack_orders')
                    ->whereIn('status', [OrderStatus::Completed->value, OrderStatus::Cancelled->value]);
            })
            ->distinct('dentaltrack_order_id')
            ->count('dentaltrack_order_id');

        $avgStepDuration = ScanEvent::where('dentaltrack_workstation_id', $workstationId)
            ->where('event_type', ScanEventType::Complete)
            ->whereNotNull('duration_seconds')
            ->avg('duration_seconds');

        if ($queueDepth <= 1 || $avgStepDuration === null) {
            return 0;
        }

        return ($queueDepth - 1) * ((float) $avgStepDuration / 60);
    }

    public function updateAccuracy(Order $order): void
    {
        if ($order->completed_at === null) {
            return;
        }

        $firstScan = ScanEvent::where('dentaltrack_order_id', $order->id)
            ->orderBy('scanned_at')
            ->value('scanned_at');

        if ($firstScan === null) {
            return;
        }

        $actualMinutes = (int) Carbon::parse($firstScan)->diffInMinutes($order->completed_at);

        Prediction::where('dentaltrack_order_id', $order->id)
            ->whereNull('actual_minutes')
            ->each(function (Prediction $prediction) use ($actualMinutes): void {
                $predicted = $prediction->predicted_minutes;
                $accuracy = $predicted > 0
                    ? max(0, 100 - abs($predicted - $actualMinutes) / $predicted * 100)
                    : 0;

                $prediction->update([
                    'actual_minutes' => $actualMinutes,
                    'accuracy_pct' => round($accuracy, 2),
                ]);
            });
    }

    public function getAccuracyStats(): array
    {
        $allWithActual = Prediction::whereNotNull('actual_minutes')->whereNotNull('accuracy_pct');

        $totalPredictions = (clone $allWithActual)->count();
        $avgAccuracy = $totalPredictions > 0 ? round((float) (clone $allWithActual)->avg('accuracy_pct'), 1) : 0.0;

        $recentAccuracy = (clone $allWithActual)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->avg('accuracy_pct');
        $recentAccuracy = $recentAccuracy !== null ? round((float) $recentAccuracy, 1) : 0.0;

        $versions = DB::table('dentaltrack_predictions')
            ->whereNotNull('actual_minutes')
            ->whereNotNull('accuracy_pct')
            ->select('model_version', DB::raw('AVG(accuracy_pct) as avg_acc'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('model_version')
            ->get();

        $byVersion = [];
        foreach ($versions as $v) {
            $byVersion[(string) $v->model_version] = [
                'accuracy' => round((float) $v->avg_acc, 1),
                'count' => (int) $v->cnt,
            ];
        }

        return [
            'avg_accuracy' => $avgAccuracy,
            'total_predictions' => $totalPredictions,
            'recent_accuracy' => $recentAccuracy,
            'by_version' => $byVersion,
        ];
    }

    public function getSmartSuggestions(): array
    {
        $suggestions = [];

        $this->addBottleneckSuggestions($suggestions);
        $this->addTechnicianSuggestions($suggestions);
        $this->addOverloadedStationSuggestions($suggestions);

        return $suggestions;
    }

    private function addBottleneckSuggestions(array &$suggestions): void
    {
        $bottlenecks = DB::table('dentaltrack_scan_events')
            ->where('event_type', ScanEventType::Start->value)
            ->where('scanned_at', '>=', Carbon::now()->subDays(7))
            ->select('dentaltrack_workstation_id as workstation_id', DB::raw('COUNT(DISTINCT dentaltrack_order_id) as queue_size'))
            ->groupBy('dentaltrack_workstation_id')
            ->having('queue_size', '>', 3)
            ->orderByDesc('queue_size')
            ->limit(5)
            ->get();

        foreach ($bottlenecks as $row) {
            $ws = Workstation::find($row->workstation_id);
            if ($ws !== null) {
                $suggestions[] = [
                    'type' => 'bottleneck',
                    'message' => "Station \"{$ws->name}\" has {$row->queue_size} orders queued. Consider redistributing workload.",
                    'priority' => 'high',
                    'data' => ['dentaltrack_workstation_id' => $ws->id, 'queue_size' => (int) $row->queue_size],
                ];
            }
        }
    }

    private function addTechnicianSuggestions(array &$suggestions): void
    {
        $techSpeeds = DB::table('dentaltrack_scan_events')
            ->where('event_type', ScanEventType::Complete->value)
            ->whereNotNull('duration_seconds')
            ->where('scanned_at', '>=', Carbon::now()->subDays(30))
            ->select('user_id', DB::raw('AVG(duration_seconds) as avg_seconds'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('user_id')
            ->having('cnt', '>=', 5)
            ->orderBy('avg_seconds')
            ->limit(3)
            ->get();

        foreach ($techSpeeds as $row) {
            $user = User::find($row->user_id);
            if ($user !== null) {
                $avgMin = round((float) $row->avg_seconds / 60, 1);
                $suggestions[] = [
                    'type' => 'fast_technician',
                    'message' => "{$user->name} averages {$avgMin} min/step. Consider assigning them to priority orders.",
                    'priority' => 'medium',
                    'data' => ['user_id' => $user->id, 'avg_minutes' => $avgMin],
                ];
            }
        }
    }

    private function addOverloadedStationSuggestions(array &$suggestions): void
    {
        $activeOrders = Order::where('status', OrderStatus::InProgress)
            ->with('scanEvents')
            ->get();

        $stationCounts = [];
        foreach ($activeOrders as $order) {
            $latestEvent = $order->scanEvents->first();
            if ($latestEvent instanceof ScanEvent) {
                $wsId = $latestEvent->dentaltrack_workstation_id;
                $stationCounts[$wsId] = ($stationCounts[$wsId] ?? 0) + 1;
            }
        }

        foreach ($stationCounts as $wsId => $count) {
            if ($count >= 3) {
                $ws = Workstation::find($wsId);
                if ($ws !== null) {
                    $suggestions[] = [
                        'type' => 'overloaded_station',
                        'message' => "Station \"{$ws->name}\" currently has {$count} active orders. Consider routing new orders elsewhere.",
                        'priority' => 'high',
                        'data' => ['dentaltrack_workstation_id' => $ws->id, 'active_count' => $count],
                    ];
                }
            }
        }
    }
}

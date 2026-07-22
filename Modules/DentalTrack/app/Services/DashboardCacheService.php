<?php

namespace Modules\DentalTrack\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Modules\DentalTrack\Enums\OrderStatus;
use Modules\DentalTrack\Models\Order;
use Modules\DentalTrack\Models\ScanEvent;
use Modules\DentalTrack\Models\Workstation;

class DashboardCacheService
{
    private const TTL_SECONDS = 60;

    private const PREFIX = 'dentaltrack:dashboard:';

    public function getOrderCounts(?int $companyId = null): array
    {
        $key = self::PREFIX.'order_counts:'.($companyId ?? 'all');

        return Cache::remember($key, self::TTL_SECONDS, function () use ($companyId): array {
            $query = Order::query();
            if ($companyId !== null) {
                $query->where('dentaltrack_company_id', $companyId);
            }

            return [
                'in_progress' => (int) (clone $query)->where('status', OrderStatus::InProgress)->count(),
                'pending' => (int) (clone $query)->where('status', OrderStatus::Pending)->count(),
                'completed' => (int) (clone $query)->where('status', OrderStatus::Completed)->count(),
                'overdue' => (int) (clone $query)->where('due_date', '<', now())
                    ->whereNotIn('status', [OrderStatus::Completed->value, OrderStatus::Cancelled->value])
                    ->count(),
                'total' => (int) $query->count(),
            ];
        });
    }

    public function getWorkstationStatus(?int $labId = null): array
    {
        $key = self::PREFIX.'workstation_status:'.($labId ?? 'all');

        return Cache::remember($key, self::TTL_SECONDS, function () use ($labId): array {
            $query = Workstation::where('is_active', true);
            if ($labId !== null) {
                $query->where('dentaltrack_lab_id', $labId);
            }

            $workstations = $query->get();
            $statuses = [];
            $cutoff = Carbon::now()->subHours(8);

            foreach ($workstations as $ws) {
                $activeCount = ScanEvent::where('dentaltrack_workstation_id', $ws->id)
                    ->where('event_type', 'start')
                    ->where('scanned_at', '>=', $cutoff)
                    ->distinct('dentaltrack_order_id')
                    ->count('dentaltrack_order_id');

                $statuses[] = [
                    'id' => $ws->id,
                    'name' => $ws->name,
                    'active_orders' => $activeCount,
                    'idle' => $activeCount === 0,
                ];
            }

            return $statuses;
        });
    }

    public function invalidateOrderCaches(): void
    {
        Cache::forget(self::PREFIX.'order_counts:all');
        Cache::forget(self::PREFIX.'workstation_status:all');
    }

    public function warmCaches(): void
    {
        $this->getOrderCounts();
        $this->getWorkstationStatus();
    }
}

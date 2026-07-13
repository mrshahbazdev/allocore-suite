<?php

namespace Modules\InvoiceMaker\Services;

class InvoiceCalculationService
{
    public function calculate(array $items, float $discount = 0): array
    {
        $subtotal = 0;
        $taxTotal = 0;
        $calculatedItems = [];

        foreach ($items as $item) {
            $quantity = (float) ($item['quantity'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $taxRate = (float) ($item['tax_rate'] ?? 0);
            $itemDiscount = (float) ($item['discount'] ?? 0);
            $lineSubtotal = $quantity * $unitPrice;
            $taxAmount = $lineSubtotal * ($taxRate / 100);
            $lineTotal = $lineSubtotal + $taxAmount - $itemDiscount;

            $calculatedItems[] = [
                ...$item,
                'tax_amount' => round($taxAmount, 2),
                'total' => round($lineTotal, 2),
            ];
            $subtotal += $lineSubtotal;
            $taxTotal += $taxAmount;
        }

        return [
            'items' => $calculatedItems,
            'subtotal' => round($subtotal, 2),
            'tax_total' => round($taxTotal, 2),
            'discount' => round($discount, 2),
            'grand_total' => round(max(0, $subtotal + $taxTotal - $discount), 2),
        ];
    }
}

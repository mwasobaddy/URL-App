<?php

namespace App\Exports;

use App\Models\Revenue;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Carbon\Carbon;

class RevenueReport implements FromCollection, WithHeadings, ShouldAutoSize, WithMultipleSheets
{
    protected $startDate;
    protected $endDate;
    protected $includeMetrics;

    public function __construct(Carbon $startDate, Carbon $endDate, array $includeMetrics)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->includeMetrics = $includeMetrics;
    }

    public function collection()
    {
        $query = Revenue::whereBetween('created_at', [$this->startDate, $this->endDate]);

        if ($this->includeMetrics['revenue']) {
            $query->with('plan');
        }

        if ($this->includeMetrics['subscriptions']) {
            $query->with('subscription');
        }

        if ($this->includeMetrics['taxes']) {
            $query->with('taxDetails');
        }

        if ($this->includeMetrics['refunds']) {
            $query->with('refunds');
        }

        return $query->get()->map(function ($revenue) {
            $data = [
                'id' => $revenue->id,
                'date' => $revenue->created_at->format('Y-m-d H:i:s'),
            ];

            if ($this->includeMetrics['revenue']) {
                $data = array_merge($data, [
                    'amount' => $revenue->amount,
                    'currency' => $revenue->currency,
                    'plan' => $revenue->plan->name ?? null,
                ]);
            }

            if ($this->includeMetrics['subscriptions']) {
                $data = array_merge($data, [
                    'subscription_id' => $revenue->subscription->id ?? null,
                    'subscription_status' => $revenue->subscription->status ?? null,
                ]);
            }

            if ($this->includeMetrics['taxes']) {
                $data = array_merge($data, [
                    'tax_amount' => $revenue->taxDetails->amount ?? 0,
                    'tax_rate' => $revenue->taxDetails->rate ?? 0,
                    'tax_region' => $revenue->taxDetails->region ?? null,
                ]);
            }

            if ($this->includeMetrics['refunds']) {
                $data = array_merge($data, [
                    'refund_amount' => $revenue->refunds->sum('amount') ?? 0,
                    'refund_reason' => $revenue->refunds->first()->reason ?? null,
                ]);
            }

            return $data;
        });
    }

    public function headings(): array
    {
        $headings = ['ID', 'Date'];

        if ($this->includeMetrics['revenue']) {
            $headings = array_merge($headings, ['Amount', 'Currency', 'Plan']);
        }

        if ($this->includeMetrics['subscriptions']) {
            $headings = array_merge($headings, ['Subscription ID', 'Subscription Status']);
        }

        if ($this->includeMetrics['taxes']) {
            $headings = array_merge($headings, ['Tax Amount', 'Tax Rate', 'Tax Region']);
        }

        if ($this->includeMetrics['refunds']) {
            $headings = array_merge($headings, ['Refund Amount', 'Refund Reason']);
        }

        return $headings;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Main revenue sheet
        $sheets[] = new RevenueReportSheet(
            $this->startDate, 
            $this->endDate, 
            $this->includeMetrics
        );

        // Summary sheet
        $sheets[] = new RevenueSummarySheet(
            $this->startDate, 
            $this->endDate, 
            $this->includeMetrics
        );

        return $sheets;
    }
}

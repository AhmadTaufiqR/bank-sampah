<?php

namespace App\Exports;

use App\Models\WasteTransaction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class WasteRekapPerYearSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $year;

    public function __construct($year)
    {
        $this->year = $year;
    }

    public function collection()
    {
        $transactions = WasteTransaction::selectRaw('DATE(created_at) as tanggal, waste_type, SUM(weight) as total_kg')
            ->whereYear('created_at', $this->year)
            ->groupBy('tanggal', 'waste_type')
            ->orderBy('tanggal')
            ->get();

        $pivot = [];
        $dates = $transactions->pluck('tanggal')->unique();
        $wasteTypes = $transactions->pluck('waste_type')->unique();

        foreach ($dates as $date) {
            $row = ['Tanggal' => $date];

            foreach ($wasteTypes as $type) {
                $matched = $transactions->firstWhere(fn($tx) => $tx->tanggal == $date && $tx->waste_type == $type);
                $row[$type] = $matched ? $matched->total_kg : 0;
            }

            $pivot[] = $row;
        }

        return collect($pivot);
    }

    public function headings(): array
    {
        $types = WasteTransaction::whereYear('created_at', $this->year)
            ->select('waste_type')
            ->distinct()
            ->pluck('waste_type')
            ->toArray();

        return array_merge(['Tanggal'], $types);
    }

    public function title(): string
    {
        return (string) $this->year;
    }
}

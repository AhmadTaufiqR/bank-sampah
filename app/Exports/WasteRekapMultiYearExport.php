<?php

namespace App\Exports;

use App\Models\WasteTransaction;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class WasteRekapMultiYearExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $driver = DB::getDriverName();
        $yearCol = $driver === 'sqlite' ? "strftime('%Y', created_at)" : "YEAR(created_at)";

        $years = WasteTransaction::selectRaw("$yearCol as year")
            ->distinct()
            ->pluck('year');

        $sheets = [];

        foreach ($years as $year) {
            $sheets[] = new WasteRekapPerYearSheet($year);
        }

        return $sheets;
    }
}

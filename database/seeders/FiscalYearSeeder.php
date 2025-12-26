<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FiscalYear;
use Carbon\Carbon;

class FiscalYearSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();
        if ($today->month >= 7) {
            $startYear = $today->year;
            $endYear   = $today->year + 1;
        } else {
            $startYear = $today->year - 1;
            $endYear   = $today->year;
        }

        $startDate = Carbon::create($startYear, 7, 1);
        $endDate   = Carbon::create($endYear, 6, 30);

        $name = "{$startYear}-{$endYear}";

        FiscalYear::firstOrCreate(
            ['name' => $name],
            [
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'is_active'  => true,
            ]
        );
    }
}

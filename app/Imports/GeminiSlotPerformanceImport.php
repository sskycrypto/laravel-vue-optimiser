<?php

namespace App\Imports;

use App\Models\GeminiSlotPerformanceStat;
use App\Vngodev\ResourceImporter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Row;

class GeminiSlotPerformanceImport implements ToArray, WithChunkReading, ShouldQueue, WithHeadingRow, WithEvents, WithBatchInserts
{
    use Importable;

    const CURRENCY_RATE = 0.13;

    /**
     * @param array $array
     */
    public function array(array $rows)
    {
        if (count($rows) > 0) {
            if (config('constants.currency_convert')) {
                foreach ($rows as &$row) {
                    $row['spend'] = $row['spend'] * self::CURRENCY_RATE;
                    $row['average_cpc'] = $row['average_cpc'] * self::CURRENCY_RATE;
                }
            }

            $resource_importer = new ResourceImporter();
            $resource_importer->insertOrUpdate('gemini_slot_performance_stats', $rows, []);
        }
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 500;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 500;
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            // Array callable, refering to a static method.
            AfterImport::class => [self::class, 'afterSheet'],
        ];
    }

    public static function afterSheet(AfterImport $event)
    {
        //
    }
}

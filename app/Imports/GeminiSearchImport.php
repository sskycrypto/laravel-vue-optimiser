<?php

namespace App\Imports;

use App\Models\GeminiSearchStat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class GeminiSearchImport implements OnEachRow, WithChunkReading, ShouldQueue, WithHeadingRow
{
    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row = $row->toArray();

        $gemini_search_stat = GeminiSearchStat::firstOrNew([
            'advertiser_id' => $row['advertiser_id'],
            'campaign_id' => $row['campaign_id'],
            'ad_group_id' => $row['ad_group_id'],
            'ad_id' => $row['ad_id'],
            'keyword_id' => $row['keyword_id'],
            'delivered_match_type' => $row['delivered_match_type'],
            'search_term' => $row['search_term'],
            'device_type' => $row['device_type'],
            'destination_url' => $row['destination_url'],
            'day' => $row['day']
        ]);

        foreach (array_keys($row) as $array_key) {
            $gemini_search_stat->{$array_key} = $row[$array_key];
        }

        $gemini_search_stat->save();
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
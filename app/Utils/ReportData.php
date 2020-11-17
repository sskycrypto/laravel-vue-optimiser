<?php

namespace App\Utils;

class ReportData
{
    public static function avg($report_data, $attribute)
    {
        $length = count($report_data);

        if ($length == 0) {
            return 0;
        }

        $total = 0;

        foreach ($report_data as $data) {
            $total += !empty($data[$attribute]) ? $data[$attribute] : 0;
        }

        return round($total / $length, 2);
    }

    public static function sum($report_data, $attribute)
    {
        $length = count($report_data);

        if ($length == 0) {
            return 0;
        }

        $total = 0;

        foreach ($report_data as $data) {
            $total += !empty($data[$attribute]) ? $data[$attribute] : 0;
        }

        return round($total, 2);
    }
}
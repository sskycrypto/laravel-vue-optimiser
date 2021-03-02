<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class HourOfDay extends Root
{
    public function check($campaign, $redtrack_data, $rule_condition, $calculation_type)
    {
        $hour_of_day = date('H');

        return parent::compare($hour_of_day, $rule_condition->amount, $rule_condition->operation);
    }
}

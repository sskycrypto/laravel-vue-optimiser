<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class CTR extends Root
{
    public function check($redtrack_data, $rule_condition)
    {
        $sum_clicks = ReportData::sum($redtrack_data, 'lp_clicks');
        $sum_views = ReportData::sum($redtrack_data, 'lp_views');

        return parent::compare($sum_clicks / $sum_views * 100, $rule_condition->amount, $rule_condition->operation);
    }
}
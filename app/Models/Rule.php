<?php

namespace App\Models;

use App\Models\RuleLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_PAUSED = 'PAUSED';

    const FREQUENCIES = [
        1 => 'MINUTES',
        2 => 'HOURS',
        3 => 'DAYS',
        4 => 'WEEKS',
        5 => 'MONTHS',
        6 => 'YEARS',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'rule_group_id',
        'from',
        'exclude_day',
        'run_type',
        'is_widget_included',
        'widget',
        'interval_amount',
        'interval_unit',
        'user_id',
        'status'
    ];

    public function ruleConditionGroups()
    {
        return $this->hasMany(RuleConditionGroup::class);
    }

    public function ruleRuleActions()
    {
        return $this->hasMany(RuleRuleAction::class);
    }

    public function timeRange()
    {
        return $this->belongsTo(RuleDataFromOption::class, 'from');
    }

    public function logs()
    {
        return $this->hasMany(RuleLog::class);
    }
}

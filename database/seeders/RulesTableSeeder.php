<?php

namespace Database\Seeders;

use DB;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('rule_condition_type_groups')->insert([[
            'name' => 'Traffic Sources Fields',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Trackers Fields',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Other',
            'created_at' => Carbon::now()
        ]]);

        DB::table('rule_condition_types')->insert([[
            'name' => 'Impressions',
            'rule_condition_type_group_id' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Traffic Source Clicks',
            'rule_condition_type_group_id' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Traffic Source Conversions',
            'rule_condition_type_group_id' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Traffic Source Revenue',
            'rule_condition_type_group_id' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Traffic Source Spent',
            'rule_condition_type_group_id' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Traffic Source EPC',
            'rule_condition_type_group_id' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'CTR',
            'rule_condition_type_group_id' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Traffic Source CVR',
            'rule_condition_type_group_id' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Traffic Source NET',
            'rule_condition_type_group_id' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Traffic Source ROI',
            'rule_condition_type_group_id' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Traffic Source CPA',
            'rule_condition_type_group_id' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Avg. CPC',
            'rule_condition_type_group_id' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Estimated Spent',
            'rule_condition_type_group_id' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Estimated NET',
            'rule_condition_type_group_id' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Estimated ROI',
            'rule_condition_type_group_id' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Tracker Clicks',
            'rule_condition_type_group_id' => 2,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Tracker Conversions',
            'rule_condition_type_group_id' => 2,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Tracker Revenue',
            'rule_condition_type_group_id' => 2,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Landing Page Clicks',
            'rule_condition_type_group_id' => 2,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Tracker EPC',
            'rule_condition_type_group_id' => 2,
            'created_at' => Carbon::now()
        ], [
                'name' => 'Landing Page CTR',
            'rule_condition_type_group_id' => 2,
            'created_at' => Carbon::now()
        ], [
                'name' => 'Tracker CVR',
            'rule_condition_type_group_id' => 2,
            'created_at' => Carbon::now()
        ], [
                'name' => 'Tracker NET',
            'rule_condition_type_group_id' => 2,
            'created_at' => Carbon::now()
        ], [
                'name' => 'Tracker ROI',
            'rule_condition_type_group_id' => 2,
            'created_at' => Carbon::now()
        ], [
                'name' => 'Tracker CPA',
            'rule_condition_type_group_id' => 2,
            'created_at' => Carbon::now()
        ], [
                'name' => 'EPC',
            'rule_condition_type_group_id' => 2,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Hour of Day',
            'rule_condition_type_group_id' => 3,
            'created_at' => Carbon::now()
        ]]);

        DB::table('rule_data_from_options')->insert([[
            'name' => 'Today',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Yesterday',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Last 3 days',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Last 7 days',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Last 14 days',
            'created_at' => Carbon::now()
        ], [
            'name' => 'This month',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Last 30 days',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Last 60 days',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Last 90 days',
            'created_at' => Carbon::now()
        ]]);
    }
}

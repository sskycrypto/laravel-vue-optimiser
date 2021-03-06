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
        DB::table('rule_condition_type_groups')->truncate();
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

        DB::table('rule_condition_types')->truncate();
        DB::table('rule_condition_types')->insert([[
            'name' => 'Impressions',
            'rule_condition_type_group_id' => 1,
            'provider' => 'Impressions',
            'report_source' => 2,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Traffic Source Clicks',
            'rule_condition_type_group_id' => 1,
            'provider' => 'TrafficSourceClicks',
            'report_source' => 2,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Traffic Source Conversions',
            'rule_condition_type_group_id' => 1,
            'provider' => 'TrafficSourceConversions',
            'report_source' => 2,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Traffic Source Revenue',
            'rule_condition_type_group_id' => 1,
            'provider' => 'TrafficSourceRevenue',
            'report_source' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Traffic Source Spent',
            'rule_condition_type_group_id' => 1,
            'provider' => 'TrafficSourceSpent',
            'report_source' => 2,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Traffic Source EPC',
            'rule_condition_type_group_id' => 1,
            'provider' => 'TrafficSourceEPC',
            'report_source' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'CTR',
            'rule_condition_type_group_id' => 1,
            'provider' => 'CTR',
            'report_source' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Traffic Source CVR',
            'rule_condition_type_group_id' => 1,
            'provider' => 'TrafficSourceCVR',
            'report_source' => 2,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Traffic Source NET',
            'rule_condition_type_group_id' => 1,
            'provider' => 'TrafficSourceNET',
            'report_source' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Traffic Source ROI',
            'rule_condition_type_group_id' => 1,
            'provider' => 'TrafficSourceROI',
            'report_source' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Traffic Source CPA',
            'rule_condition_type_group_id' => 1,
            'provider' => 'TrafficSourceCPA',
            'report_source' => 2,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Avg. CPC',
            'rule_condition_type_group_id' => 1,
            'provider' => 'AvgCPC',
            'report_source' => 2,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Estimated Spent',
            'rule_condition_type_group_id' => 1,
            'provider' => 'EstimatedSpent',
            'report_source' => 2,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Estimated NET',
            'rule_condition_type_group_id' => 1,
            'provider' => 'EstimatedNET',
            'report_source' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Estimated ROI',
            'rule_condition_type_group_id' => 1,
            'provider' => 'EstimatedROI',
            'report_source' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Tracker Clicks',
            'rule_condition_type_group_id' => 2,
            'provider' => 'TrackerClicks',
            'report_source' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Tracker Conversions',
            'rule_condition_type_group_id' => 2,
            'provider' => 'TrackerConversions',
            'report_source' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Tracker Revenue',
            'rule_condition_type_group_id' => 2,
            'provider' => 'TrackerRevenue',
            'report_source' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Landing Page Clicks',
            'rule_condition_type_group_id' => 2,
            'provider' => 'LandingPageClicks',
            'report_source' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Tracker EPC',
            'rule_condition_type_group_id' => 2,
            'provider' => 'TrackerEPC',
            'report_source' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Landing Page CTR',
            'rule_condition_type_group_id' => 2,
            'provider' => 'LandingPageCTR',
            'report_source' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Tracker CVR',
            'rule_condition_type_group_id' => 2,
            'provider' => 'TrackerCVR',
            'report_source' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Tracker NET',
            'rule_condition_type_group_id' => 2,
            'provider' => 'TrackerNET',
            'report_source' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Tracker ROI',
            'rule_condition_type_group_id' => 2,
            'provider' => 'TrackerROI',
            'report_source' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Tracker CPA',
            'rule_condition_type_group_id' => 2,
            'provider' => 'TrackerCPA',
            'report_source' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'EPC',
            'rule_condition_type_group_id' => 2,
            'provider' => 'EPC',
            'report_source' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Hour Of Day',
            'rule_condition_type_group_id' => 3,
            'provider' => 'HourOfDay',
            'report_source' => 1,
            'created_at' => Carbon::now()
        ]]);

        DB::table('rule_data_from_options')->truncate();
        DB::table('rule_data_from_options')->insert([[
            'name' => 'Today',
            'excluded_day_type' => 1,
            'provider' => 'Today',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Yesterday',
            'excluded_day_type' => 2,
            'provider' => 'Yesterday',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Last 3 days',
            'excluded_day_type' => 3,
            'provider' => 'Last3days',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Last 7 days',
            'excluded_day_type' => 3,
            'provider' => 'Last7days',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Last 14 days',
            'excluded_day_type' => 3,
            'provider' => 'Last14days',
            'created_at' => Carbon::now()
        ], [
            'name' => 'This month',
            'excluded_day_type' => 3,
            'provider' => 'ThisMonth',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Last 30 days',
            'excluded_day_type' => 3,
            'provider' => 'Last30days',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Last 60 days',
            'excluded_day_type' => 3,
            'provider' => 'Last60days',
            'created_at' => Carbon::now()
        ], [
            'name' => 'Last 90 days',
            'excluded_day_type' => 3,
            'provider' => 'Last90days',
            'created_at' => Carbon::now()
        ]]);

        DB::table('rule_actions')->truncate();
        DB::table('rule_actions')->insert([[
            'name' => 'Block Site',
            'provider' => 'BlockSite',
            'calculation_type' => 2,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Block Widgets / Pushlisher',
            'provider' => 'BlockWidgetsPushlisher',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Un-Block Widgets / Pushlisher',
            'provider' => 'UnBlockWidgetsPushlisher',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Block Apps',
            'provider' => 'BlockApps',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Un-Block Apps',
            'provider' => 'UnBlockApps',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Pause Campaign',
            'provider' => 'PauseCampaign',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Activate Campaign',
            'provider' => 'ActivateCampaign',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Pause Contents',
            'provider' => 'PauseContents',
            'calculation_type' => 3,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Activate Contents',
            'provider' => 'ActivateContents',
            'calculation_type' => 3,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Campaign Bid',
            'provider' => 'ChangeCampaignBid',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Campaign Bid (CAD Only)',
            'provider' => 'ChangeCampaignBidCADOnly',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Campaign Budget',
            'provider' => 'ChangeCampaignBudget',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Pause Target',
            'provider' => 'PauseTarget',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Activate Target',
            'provider' => 'ActivateTarget',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Pause Site',
            'provider' => 'PauseSite',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Activate Site',
            'provider' => 'ActivateSite',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Pause Exchange',
            'provider' => 'PauseExchange',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Activate Exchange',
            'provider' => 'ActivateExchange',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Pause Sections',
            'provider' => 'PauseSections',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Activate Sections',
            'provider' => 'ActivateSections',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Pause AdGroup',
            'provider' => 'PauseAdGroup',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Activate AdGroup',
            'provider' => 'ActivateAdGroup',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Pause Domain',
            'provider' => 'PauseDomain',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Activate Domain',
            'provider' => 'ActivateDomain',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Pause Spot',
            'provider' => 'PauseSpot',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Activate Spot',
            'provider' => 'ActivateSpot',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Target Bid',
            'provider' => 'ChangeTargetBid',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Content Bid',
            'provider' => 'ChangeContentBid',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Section Bid',
            'provider' => 'ChangeSectionBid',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Widget Coefficient',
            'provider' => 'ChangeWidgetCoefficient',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Site Bid',
            'provider' => 'ChangeSiteBid',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Widget Bid',
            'provider' => 'ChangeWidgetBid',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Widget Bid (CAD only)',
            'provider' => 'ChangeWidgetBidCADonly',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Exchange Bid',
            'provider' => 'ChangeExchangeBid',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Site Bid Modifier',
            'provider' => 'ChangeSiteBidModifier',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change AdGroup Bid',
            'provider' => 'ChangeAdGroupBid',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Day Parting',
            'provider' => 'DayParting',
            'calculation_type' => 1,
            'created_at' => Carbon::now()
        ], [
            'name' => 'UnBlock Site',
            'provider' => 'UnBlockSite',
            'calculation_type' => 2,
            'created_at' => Carbon::now()
        ], [
            'name' => 'Change Publisher Bid',
            'provider' => 'ChangePublisherBid',
            'calculation_type' => 4,
            'created_at' => Carbon::now()
        ]]);
    }
}

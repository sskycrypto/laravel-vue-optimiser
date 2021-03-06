<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeminiReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gemini_performance_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->bigInteger('ad_id');
            $table->date('month')->nullable();
            $table->date('week')->nullable();
            $table->date('day')->nullable();
            $table->double('hour')->nullable();
            $table->string('pricing_type')->nullable();
            $table->string('device_type')->nullable();
            $table->string('source_name')->nullable();
            $table->double('impressions')->nullable();
            $table->double('clicks')->nullable();
            $table->double('post_click_conversions')->nullable();
            $table->double('post_click_conversion_value')->nullable();
            $table->double('post_impression_conversions')->nullable();
            $table->double('conversions')->nullable();
            $table->double('total_conversions')->nullable();
            $table->double('spend')->nullable();
            $table->double('average_position')->nullable();
            $table->double('max_bid')->nullable();
            $table->double('ad_extn_impressions')->nullable();
            $table->double('ad_extn_clicks')->nullable();
            $table->double('ad_extn_conversions')->nullable();
            $table->double('ad_extn_spend')->nullable();
            $table->double('average_cpc')->nullable();
            $table->double('average_cpm')->nullable();
            $table->double('ctr')->nullable();
            $table->double('video_starts')->nullable();
            $table->double('video_views')->nullable();
            $table->double('video_25_complete')->nullable();
            $table->double('video_50_complete')->nullable();
            $table->double('video_75_complete')->nullable();
            $table->double('video_100_complete')->nullable();
            $table->double('cost_per_video_view')->nullable();
            $table->double('video_closed')->nullable();
            $table->double('video_skipped')->nullable();
            $table->double('video_after_30_seconds_view')->nullable();
            $table->double('in_app_post_click_convs')->nullable();
            $table->double('in_app_post_view_convs')->nullable();
            $table->double('in_app_post_install_convs')->nullable();
            $table->double('opens')->nullable();
            $table->double('saves')->nullable();
            $table->double('save_rate')->nullable();
            $table->double('forwards')->nullable();
            $table->double('forward_rate')->nullable();
            $table->double('click_outs')->nullable();
            $table->double('click_outs_rate')->nullable();
            $table->string('fact_conversion_counting')->nullable();
            $table->string('interactions')->nullable();
            $table->string('interaction_rate')->nullable();
            $table->double('interactive_impressions_rate')->nullable();
            $table->timestamps();

            $table->unique(['advertiser_id', 'campaign_id', 'ad_group_id', 'ad_id', 'month', 'week', 'day', 'hour', 'pricing_type', 'device_type', 'source_name'], 'performance_stats_unique');
        });

        Schema::create('gemini_slot_performance_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->bigInteger('ad_id');
            $table->date('month')->nullable();
            $table->date('week')->nullable();
            $table->date('day')->nullable();
            $table->double('hour')->nullable();
            $table->string('pricing_type')->nullable();
            $table->string('source')->nullable();
            $table->double('card_id')->nullable();
            $table->double('card_position')->nullable();
            $table->string('ad_format_name')->nullable();
            $table->string('rendered_type')->nullable();
            $table->double('average_cpc')->nullable();
            $table->double('impressions')->nullable();
            $table->double('clicks')->nullable();
            $table->double('post_click_conversions')->nullable();
            $table->double('spend')->nullable();
            $table->double('ctr')->nullable();
            $table->timestamps();

            $table->unique(['advertiser_id', 'campaign_id', 'ad_group_id', 'ad_id', 'month', 'week', 'day', 'hour', 'pricing_type', 'source', 'card_id', 'card_position', 'ad_format_name'], 'slot_performance_stats_unique');
        });

        Schema::create('gemini_site_performance_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->date('day')->nullable();
            $table->string('external_site_name')->nullable();
            $table->string('external_site_group_name')->nullable();
            $table->string('device_type')->nullable();
            $table->double('bid_modifier')->nullable();
            $table->double('average_bid')->nullable();
            $table->double('modified_bid')->nullable();
            $table->double('spend')->nullable();
            $table->double('impressions')->nullable();
            $table->double('clicks')->nullable();
            $table->double('post_click_conversions')->nullable();
            $table->double('post_impression_conversions')->nullable();
            $table->double('conversions')->nullable();
            $table->double('ctr')->nullable();
            $table->double('average_cpc')->nullable();
            $table->double('average_cpm')->nullable();
            $table->string('fact_conversion_counting')->nullable();
            $table->timestamps();

            $table->unique(['advertiser_id', 'campaign_id', 'ad_group_id', 'day', 'external_site_name', 'external_site_group_name', 'device_type'], 'site_performance_stats_unique');
        });

        Schema::create('gemini_campaign_bid_performance_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('section_id');
            $table->bigInteger('ad_group_id');
            $table->date('day')->nullable();
            $table->string('supply_type')->nullable();
            $table->string('group_or_site')->nullable();
            $table->string('group')->nullable();
            $table->double('bid_modifier')->nullable();
            $table->double('average_bid')->nullable();
            $table->double('modified_bid')->nullable();
            $table->double('impressions')->nullable();
            $table->double('clicks')->nullable();
            $table->double('post_click_conversions')->nullable();
            $table->double('post_impression_conversions')->nullable();
            $table->double('conversions')->nullable();
            $table->double('cost')->nullable();
            $table->double('ctr')->nullable();
            $table->double('average_cpc')->nullable();
            $table->string('fact_conversion_counting')->nullable();
            $table->timestamps();

            $table->unique(['advertiser_id', 'campaign_id', 'section_id', 'ad_group_id', 'day', 'supply_type', 'group_or_site', 'group', 'bid_modifier', 'average_bid', 'modified_bid'], 'bid_performance_stats_unique');
        });

        Schema::create('gemini_structured_snippet_extension_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->bigInteger('ad_id');
            $table->bigInteger('keyword_id');
            $table->bigInteger('structured_snippet_extn_id');
            $table->date('month')->nullable();
            $table->date('week')->nullable();
            $table->date('day')->nullable();
            $table->string('pricing_type')->nullable();
            $table->string('source')->nullable();
            $table->string('destination_url')->nullable();
            $table->double('impressions')->nullable();
            $table->double('clicks')->nullable();
            $table->double('conversions')->nullable();
            $table->double('spend')->nullable();
            $table->double('average_position')->nullable();
            $table->double('max_bid')->nullable();
            $table->double('average_cpc')->nullable();
            $table->double('average_cost_per_action')->nullable();
            $table->double('average_cpm')->nullable();
            $table->double('ctr')->nullable();
            $table->timestamps();

            $table->unique(['advertiser_id', 'campaign_id', 'ad_group_id', 'ad_id', 'keyword_id', 'structured_snippet_extn_id', 'month', 'week', 'day', 'pricing_type', 'source', 'destination_url'], 'structured_performance_stats_unique');
        });

        Schema::create('gemini_product_ad_performance_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->bigInteger('product_ad_id');
            $table->date('month')->nullable();
            $table->date('week')->nullable();
            $table->date('day')->nullable();
            $table->date('hour')->nullable();
            $table->string('pricing_type')->nullable();
            $table->string('device_type')->nullable();
            $table->string('source_name')->nullable();
            $table->double('impressions')->nullable();
            $table->double('clicks')->nullable();
            $table->double('post_click_conversions')->nullable();
            $table->double('post_impression_conversions')->nullable();
            $table->double('conversions')->nullable();
            $table->double('total_conversions')->nullable();
            $table->double('spend')->nullable();
            $table->double('average_position')->nullable();
            $table->double('max_bid')->nullable();
            $table->double('ad_extn_impressions')->nullable();
            $table->double('ad_extn_clicks')->nullable();
            $table->double('ad_extn_conversions')->nullable();
            $table->double('ad_extn_spend')->nullable();
            $table->double('average_cpc')->nullable();
            $table->double('average_cpm')->nullable();
            $table->double('ctr')->nullable();
            $table->double('video_starts')->nullable();
            $table->double('video_views')->nullable();
            $table->double('video_25_complete')->nullable();
            $table->double('video_50_complete')->nullable();
            $table->double('video_75_complete')->nullable();
            $table->double('video_100_complete')->nullable();
            $table->double('cost_per_video_view')->nullable();
            $table->double('video_closed')->nullable();
            $table->double('video_skipped')->nullable();
            $table->double('video_after_30_seconds_view')->nullable();
            $table->double('in_app_post_click_convs')->nullable();
            $table->double('in_app_post_view_convs')->nullable();
            $table->double('in_app_post_install_convs')->nullable();
            $table->double('opens')->nullable();
            $table->double('saves')->nullable();
            $table->double('save_rate')->nullable();
            $table->double('forwards')->nullable();
            $table->double('forward_rate')->nullable();
            $table->double('click_outs')->nullable();
            $table->double('click_out_rate')->nullable();
            $table->string('fact_conversion_counting')->nullable();
            $table->timestamps();

            $table->unique(['advertiser_id', 'campaign_id', 'ad_group_id', 'product_ad_id', 'month', 'week', 'day', 'pricing_type', 'device_type', 'source_name'], 'product_ad_performance_stats_unique');
        });

        Schema::create('gemini_adjustment_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->date('day')->nullable();
            $table->string('pricing_type')->nullable();
            $table->string('source_name')->nullable();
            $table->string('is_adjustment')->nullable();
            $table->string('adjustment_type')->nullable();
            $table->double('impressions')->nullable();
            $table->double('clicks')->nullable();
            $table->double('conversions')->nullable();
            $table->double('spend')->nullable();
            $table->double('average_position')->nullable();
            $table->timestamps();

            $table->unique(['advertiser_id', 'campaign_id', 'day', 'pricing_type', 'source_name', 'is_adjustment', 'adjustment_type'], 'adjustment_stats_unique');
        });

        Schema::create('gemini_keyword_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->bigInteger('ad_id');
            $table->bigInteger('keyword_id');
            $table->string('destination_url')->nullable();
            $table->date('day')->nullable();
            $table->string('device_type')->nullable();
            $table->string('source_name')->nullable();
            $table->double('impressions')->nullable();
            $table->double('clicks')->nullable();
            $table->double('post_click_conversions')->nullable();
            $table->double('post_impression_conversions')->nullable();
            $table->double('conversions')->nullable();
            $table->double('total_conversions')->nullable();
            $table->double('spend')->nullable();
            $table->double('average_position')->nullable();
            $table->double('max_bid')->nullable();
            $table->double('average_cpc')->nullable();
            $table->double('average_cpm')->nullable();
            $table->double('ctr')->nullable();
            $table->timestamps();

            $table->unique(['advertiser_id', 'campaign_id', 'ad_group_id', 'ad_id', 'keyword_id', 'destination_url', 'day', 'device_type', 'source_name'], 'keyword_stats_unique');
        });

        Schema::create('gemini_search_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->bigInteger('ad_id');
            $table->bigInteger('keyword_id');
            $table->string('delivered_match_type')->nullable();
            $table->string('search_term')->nullable();
            $table->string('device_type')->nullable();
            $table->string('destination_url')->nullable();
            $table->date('day')->nullable();
            $table->double('impressions')->nullable();
            $table->double('clicks')->nullable();
            $table->double('spend')->nullable();
            $table->double('conversions')->nullable();
            $table->double('post_click_conversions')->nullable();
            $table->double('post_impression_conversions')->nullable();
            $table->double('average_position')->nullable();
            $table->double('max_bid')->nullable();
            $table->double('average_cpc')->nullable();
            $table->double('ctr')->nullable();
            $table->double('impression_share')->nullable();
            $table->double('click_share')->nullable();
            $table->double('conversion_share')->nullable();
            $table->timestamps();

            $table->unique(['advertiser_id', 'campaign_id', 'ad_group_id', 'ad_id', 'keyword_id', 'search_term', 'device_type', 'destination_url', 'day'], 'search_stats_unique');
        });

        Schema::create('gemini_ad_extension_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->bigInteger('ad_id');
            $table->bigInteger('keyword_id');
            $table->bigInteger('ad_extn_id');
            $table->string('device_type')->nullable();
            $table->date('month')->nullable();
            $table->date('week')->nullable();
            $table->date('day')->nullable();
            $table->string('pricing_type')->nullable();
            $table->string('destination_url')->nullable();
            $table->double('impressions')->nullable();
            $table->double('clicks')->nullable();
            $table->double('conversions')->nullable();
            $table->double('spend')->nullable();
            $table->double('average_position')->nullable();
            $table->double('max_bid')->nullable();
            $table->double('call_conversion')->nullable();
            $table->double('average_cpc')->nullable();
            $table->double('average_cost_per_install')->nullable();
            $table->double('average_cpm')->nullable();
            $table->double('ctr')->nullable();
            $table->double('average_call_duration')->nullable();
            $table->timestamps();

            $table->unique(['advertiser_id', 'campaign_id', 'ad_group_id', 'ad_id', 'keyword_id', 'ad_extn_id', 'device_type', 'month', 'week', 'day', 'pricing_type', 'destination_url'], 'ad_extensions_unique');
        });

        Schema::create('gemini_call_extension_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->date('month')->nullable();
            $table->date('week')->nullable();
            $table->date('day')->nullable();
            $table->string('caller_name')->nullable();
            $table->string('caller_area_code')->nullable();
            $table->string('caller_number')->nullable();
            $table->string('call_start_time')->nullable();
            $table->string('call_end_time')->nullable();
            $table->string('call_status')->nullable();
            $table->double('call_duration')->nullable();
            $table->timestamps();

            $table->unique(['advertiser_id', 'campaign_id', 'ad_group_id', 'month', 'week', 'day'], 'call_extensions_unique');
        });

        Schema::create('gemini_user_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('audience_id');
            $table->string('audience_name');
            $table->string('audience_type');
            $table->string('audience_status');
            $table->bigInteger('ad_group_id');
            $table->date('day')->nullable();
            $table->string('pricing_type')->nullable();
            $table->string('source_name')->nullable();
            $table->string('gender')->nullable();
            $table->string('age')->nullable();
            $table->string('device_type')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('zip')->nullable();
            $table->string('dma_woeid')->nullable();
            $table->string('city_woeid')->nullable();
            $table->string('state_woeid')->nullable();
            $table->string('zip_woeid')->nullable();
            $table->string('country_woeid')->nullable();
            $table->string('location_type')->nullable();
            $table->double('impressions')->nullable();
            $table->double('clicks')->nullable();
            $table->double('post_click_conversions')->nullable();
            $table->double('post_impression_conversions')->nullable();
            $table->double('conversions')->nullable();
            $table->double('total_conversions')->nullable();
            $table->double('spend')->nullable();
            $table->double('reblogs')->nullable();
            $table->double('reblog_rate')->nullable();
            $table->double('likes')->nullable();
            $table->double('like_rate')->nullable();
            $table->double('follows')->nullable();
            $table->double('follow_rate')->nullable();
            $table->double('engagements')->nullable();
            $table->double('paid_engagements')->nullable();
            $table->double('engagement_rate')->nullable();
            $table->double('paid_engagement_rate')->nullable();
            $table->double('video_starts')->nullable();
            $table->double('video_views')->nullable();
            $table->double('video_25_complete')->nullable();
            $table->double('video_50_complete')->nullable();
            $table->double('video_75_complete')->nullable();
            $table->double('video_100_complete')->nullable();
            $table->double('cost_per_video_view')->nullable();
            $table->double('video_closed')->nullable();
            $table->double('video_skipped')->nullable();
            $table->double('video_after_30_seconds_view')->nullable();
            $table->double('ad_extn_impressions')->nullable();
            $table->double('ad_extn_clicks')->nullable();
            $table->double('ad_extn_spend')->nullable();
            $table->double('average_position')->nullable();
            $table->string('landing_page_type')->nullable();
            $table->string('fact_conversion_counting')->nullable();
            $table->timestamps();
        });

        Schema::create('gemini_product_ads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->bigInteger('offer_id');
            $table->bigInteger('category_id');
            $table->string('category_name');
            $table->string('device');
            $table->string('product_type');
            $table->string('brand');
            $table->bigInteger('offer_group_id');
            $table->bigInteger('product_id');
            $table->string('product_name');
            $table->string('custom_label_0');
            $table->string('custom_label_1');
            $table->string('custom_label_2');
            $table->string('custom_label_3');
            $table->string('custom_label_4');
            $table->string('source');
            $table->string('device_type');
            $table->date('month')->nullable();
            $table->date('week')->nullable();
            $table->date('day')->nullable();
            $table->double('impressions')->nullable();
            $table->double('clicks')->nullable();
            $table->double('post_view_conversions')->nullable();
            $table->double('post_click_conversions')->nullable();
            $table->double('total_conversions')->nullable();
            $table->double('spend')->nullable();
            $table->double('average_cpc')->nullable();
            $table->double('ctr')->nullable();
            $table->timestamps();

            $table->unique(['advertiser_id', 'campaign_id', 'ad_group_id', 'offer_id', 'category_id', 'device', 'offer_group_id', 'product_id', 'source', 'device_type', 'month', 'week', 'day'], 'product_ads_unique');
        });

        Schema::create('gemini_conversion_rules_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->bigInteger('rule_id');
            $table->string('rule_name');
            $table->string('category_name');
            $table->string('conversion_device');
            $table->bigInteger('keyword_id');
            $table->string('keyword_value');
            $table->string('source_name');
            $table->string('price_type');
            $table->date('day')->nullable();
            $table->double('post_view_conversions')->nullable();
            $table->double('post_click_conversions')->nullable();
            $table->double('conversion_value')->nullable();
            $table->double('post_view_conversion_value')->nullable();
            $table->double('conversions')->nullable();
            $table->double('in_app_post_click_convs')->nullable();
            $table->double('in_app_post_view_convs')->nullable();
            $table->double('in_app_post_install_convs')->nullable();
            $table->string('landing_page_type')->nullable();
            $table->string('fact_conversion_counting')->nullable();
            $table->timestamps();

            $table->unique(['advertiser_id', 'campaign_id', 'ad_group_id', 'rule_id', 'conversion_device', 'keyword_id', 'source_name', 'price_type', 'day'], 'conversion_rule_stats_unique');
        });

        Schema::create('gemini_domain_performance_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->date('day')->nullable();
            $table->double('clicks')->nullable();
            $table->double('spend')->nullable();
            $table->double('impressions')->nullable();
            $table->string('top_domain')->nullable();
            $table->string('package_name')->nullable();
            $table->timestamps();

            $table->unique(['advertiser_id', 'campaign_id', 'ad_group_id', 'top_domain', 'package_name', 'day'], 'domain_stats_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gemini_performance_stats');
        Schema::dropIfExists('gemini_slot_performance_stats');
        Schema::dropIfExists('gemini_site_performance_stats');
        Schema::dropIfExists('gemini_campaign_bid_performance_stats');
        Schema::dropIfExists('gemini_structured_snippet_extension_stats');
        Schema::dropIfExists('gemini_product_ad_performance_stats');
        Schema::dropIfExists('gemini_adjustment_stats');
        Schema::dropIfExists('gemini_keyword_stats');
        Schema::dropIfExists('gemini_search_stats');
        Schema::dropIfExists('gemini_ad_extension_details');
        Schema::dropIfExists('gemini_call_extension_stats');
        Schema::dropIfExists('gemini_user_stats');
        Schema::dropIfExists('gemini_product_ads');
        Schema::dropIfExists('gemini_conversion_rules_stats');
        Schema::dropIfExists('gemini_domain_performance_stats');
    }
}

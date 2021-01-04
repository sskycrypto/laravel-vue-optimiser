<?php

namespace App\Utils\AdVendors;

use App\Endpoints\OutbrainAPI;
use App\Jobs\PullCampaign;
use App\Models\Ad;
use App\Models\Campaign;
use App\Models\OutbrainReport;
use App\Models\Provider;
use App\Models\RedtrackContentStat;
use App\Models\RedtrackReport;
use App\Models\UserTracker;
use App\Vngodev\AdVendorInterface;
use App\Vngodev\Helper;
use Carbon\Carbon;
use DB;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class Outbrain extends Root implements AdVendorInterface
{
    private function api()
    {
        $provider = Provider::where('slug', request('provider'))->first();

        return new OutbrainAPI(auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first());
    }

    public function languages()
    {
        return config('constants.languages');
    }

    public function countries()
    {
        return $this->api()->getCountries();
    }

    /**
     * @return mixed
     * @throws GuzzleException
     */
    public function advertisers()
    {
        return $this->api()->getMarketers();
    }

    public function store()
    {
        $data = [];
        $api = $this->api();

        try {
            $budget_data = $api->createBudget();
            Log::info('OUTBRAIN: Created budget: ' . $budget_data['id']);

            try {
                $campaign_data = $api->createCampaign($budget_data);
                Log::info('OUTBRAIN: Created campaign: ' . $campaign_data['id']);
            } catch (Exception $e) {
                $api->deleteBudget($budget_data);
                throw $e;
            }

            try {

                $ads = [];

                foreach (request('ads') as $ad) {
                    foreach ($ad['titles'] as $title) {
                        foreach ($ad['images'] as $image) {
                            $ads[] = [
                                'text' => $title['title'],
                                'url' => $ad['targetUrl'],
                                'enabled' => true,
                                'imageMetadata' => [
                                    'url' => $image['url']
                                ]
                            ];
                        }
                    }
                }

                $api->createAds($campaign_data, $ads);

                Helper::pullCampaign();
            } catch (Exception $e) {
                $api->deleteCampaign($campaign_data['id']);
                $api->deleteBudget($budget_data);
                throw $e;
            }
        } catch (RequestException $e) {
            $data = [
                'errors' => [$e->getMessage()]
            ];
        } catch (Exception $e) {
            $data = [
                'errors' => [$e->getMessage()]
            ];
        }

        return $data;
    }

    public function update(Campaign $campaign)
    {
        $data = [];
        $api = $this->api();

        try {
            $budget_data = $api->updateBudget(request('budgetId'));
            $campaign_data = $api->updateCampaign($campaign->campaign_id);

            $ads = [];
            $updated_ads = [];

            foreach (request('ads') as $content) {
                foreach ($content['titles'] as $title) {
                    foreach ($content['images'] as $image) {
                        $ad = [
                            'text' => $title['title'],
                            'url' => $content['targetUrl'],
                            'enabled' => true,
                            'imageMetadata' => [
                                'url' => $image['url']
                            ]
                        ];

                        if ($title['existing'] && $image['existing']) {
                            $ad['id'] = $content['aId'];

                            $updated_ads[] = $ad;
                        } else {
                            $ads[] = $ad;
                        }
                    }
                }
            }

            if (count($updated_ads) > 0) {
                $ad_data = $api->updateAds($campaign->campaign_id, $updated_ads);
            }

            if (count($ads) > 0) {
                $ad_data = $api->createAds($campaign_data, $ads);
            }
        } catch (RequestException $e) {
            $data = [
                'errors' => [$e->getMessage()]
            ];
        } catch (Exception $e) {
            $data = [
                'errors' => [$e->getMessage()]
            ];
        }

        return $data;
    }

    public function getCampaignInstance(Campaign $campaign)
    {
        try {
            $api = new OutbrainAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first());

            $instance = $api->getCampaign($campaign->campaign_id);

            $instance['provider'] = $campaign->provider->slug;
            $instance['provider_id'] = $campaign['provider_id'];
            $instance['open_id'] = $campaign['open_id'];
            $instance['instance_id'] = $campaign['id'];
            $instance['ads'] = $api->getPromotedLinks($campaign->campaign_id)['promotedLinks'];

            return $instance;
        } catch (Exception $e) {
            return [];
        }
    }

    public function cloneCampaignName(&$instance)
    {
        $instance['name'] = $instance['name'] . ' - Copy';
    }

    public function status(Campaign $campaign)
    {
        try {
            $api = new OutbrainAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first());
            $campaign->status = $campaign->status == Campaign::STATUS_ACTIVE ? Campaign::STATUS_PAUSED : Campaign::STATUS_ACTIVE;

            $api->updateCampaignStatus($campaign->campaign_id, $campaign->status == Campaign::STATUS_ACTIVE);

            $promoted_links = $api->getPromotedLinks($campaign->campaign_id);

            if ($promoted_links && isset($promoted_links['promotedLinks'])) {
                $promoted_ids = array_column($promoted_links['promotedLinks'], 'id');
                if (count($promoted_ids)) {
                    $api->updatePromotedLinkStatus(implode(',', $promoted_ids), $campaign->status == Campaign::STATUS_ACTIVE);
                }
            }

            $campaign->save();

            return [];
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function pullCampaign($user_provider)
    {
        $api = new OutbrainAPI($user_provider);
        $campaign_ids = [];

        $marketers_response = $api->getMarketers();
        if (array_key_exists('marketers', $marketers_response)) {
            $marketers = $marketers_response['marketers'];
            foreach ($marketers as $key => $marketer) {
                $campaigns_by_marketer = $api->getCampaignsByMarketerId($marketer['id']);
                if (array_key_exists('campaigns', $campaigns_by_marketer)) {
                    $campaigns_by_marketer = $campaigns_by_marketer['campaigns'];
                    foreach ($campaigns_by_marketer as $campaign) {
                        $db_campaign = Campaign::firstOrNew([
                            'campaign_id' => $campaign['id'],
                            'provider_id' => $user_provider->provider_id,
                            'open_id' => $user_provider->open_id,
                            'user_id' => $user_provider->user_id
                        ]);

                        $db_campaign->name = $campaign['name'];
                        $db_campaign->status = $campaign['enabled'] ? 'ACTIVE' : 'PAUSED';
                        $db_campaign->budget = $campaign['budget']['amount'];
                        $db_campaign->advertiser_id = $marketer['id'];
                        $db_campaign->save();
                        $campaign_ids[] = $db_campaign->id;
                    }
                }
            }
        }

        Campaign::where([
            'user_id' => $user_provider->user_id,
            'provider_id' => $user_provider->provider_id,
            'open_id' => $user_provider->open_id
        ])->whereNotIn('id', $campaign_ids)->delete();
    }

    public function pullAdGroup($user_provider)
    {
        //
    }

    public function pullAd($user_provider)
    {
        $ad_ids = [];
        Campaign::where('user_id', $user_provider->user_id)->where('provider_id', 2)->chunk(10, function ($campaigns) use ($user_provider, &$ad_ids) {
            foreach ($campaigns as $key => $campaign) {
                $promoted_links = (new OutbrainAPI($user_provider))->getPromotedLinks($campaign->campaign_id);

                if ($promoted_links && isset($promoted_links['promotedLinks'])) {
                    foreach ($promoted_links['promotedLinks'] as $key => $ad) {
                        $db_ad = Ad::firstOrNew([
                            'ad_id' => $ad['id'],
                            'user_id' => $user_provider->user_id,
                            'provider_id' => $user_provider->provider_id,
                            'campaign_id' => $campaign->campaign_id,
                            'advertiser_id' => $campaign->advertiser_id,
                            'ad_group_id' => 'NA',
                            'open_id' => $user_provider->open_id
                        ]);

                        $db_ad->name = $ad['text'];
                        $db_ad->status = $ad['status'];
                        $db_ad->save();
                        $ad_ids[] = $db_ad->id;
                    }
                }
            }
        });

        Ad::where([
            'user_id' => $user_provider->user_id,
            'provider_id' => $user_provider->provider_id,
            'open_id' => $user_provider->open_id
        ])->whereNotIn('id', $ad_ids)->delete();
    }

    public function pullRedTrack($campaign)
    {
        $tracker = UserTracker::where('provider_id', $campaign->provider_id)
            ->where('provider_open_id', $campaign->open_id)
            ->first();

        if ($tracker) {
            $client = new Client();
            $date = Carbon::now()->format('Y-m-d');
            $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=hour_of_day&sub5=' . $campaign->campaign_id . '&tracks_view=true';
            $response = $client->get($url);

            $data = json_decode($response->getBody(), true);

            foreach ($data as $i => $value) {
                $value['date'] = $date;
                $value['user_id'] = $campaign->user_id;
                $value['campaign_id'] = $campaign->id;
                $value['provider_id'] = $campaign->provider_id;
                $value['open_id'] = $campaign->open_id;
                $redtrack_report = RedtrackReport::firstOrNew([
                    'date' => $date,
                    'sub5' => $campaign->campaign_id,
                    'hour_of_day' => $value['hour_of_day']
                ]);
                foreach (array_keys($value) as $array_key) {
                    $redtrack_report->{$array_key} = $value[$array_key];
                }
                $redtrack_report->save();
            }

            // Content stats
            $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=sub2&sub5=' . $campaign->campaign_id . '&tracks_view=true';
            $response = $client->get($url);

            $data = json_decode($response->getBody(), true);

            foreach ($data as $i => $value) {
                $value['date'] = $date;
                $value['user_id'] = $campaign->user_id;
                $value['campaign_id'] = $campaign->id;
                $value['provider_id'] = $campaign->provider_id;
                $value['open_id'] = $campaign->open_id;
                $redtrack_report = RedtrackContentStat::firstOrNew([
                    'date' => $date,
                    'sub2' => $value['sub2']
                ]);
                foreach (array_keys($value) as $array_key) {
                    $redtrack_report->{$array_key} = $value[$array_key];
                }
                $redtrack_report->save();
            }
        }
    }

    public function getSummaryDataQuery($data)
    {
        $summary_data_query = OutbrainReport::select(
            DB::raw('SUM(JSON_EXTRACT(data, "$.summary.spend")) as total_cost'),
            DB::raw('"N/A" as total_revenue'),
            DB::raw('"N/A" as total_net'),
            DB::raw('"N/A" as avg_roi')
        );
        $summary_data_query->leftJoin('campaigns', function ($join) use ($data) {
            $join->on('campaigns.id', '=', 'outbrain_reports.campaign_id');
            if ($data['provider']) {
                $join->where('campaigns.provider_id', $data['provider']);
            }
            if ($data['account']) {
                $join->where('campaigns.open_id', $data['account']);
            }
        });
        $summary_data_query->whereBetween('date', [request('start'), request('end')]);

        return $summary_data_query;
    }

    public function getWidgetQuery($campaign, $data)
    {
        //
    }

    public function getContentQuery($campaign, $data)
    {
        $contents_query = Ad::select([
            DB::raw('MAX(ads.id) as id'),
            DB::raw('MAX(ads.campaign_id) as campaign_id'),
            DB::raw('MAX(ads.ad_group_id) as ad_group_id'),
            DB::raw('MAX(ads.ad_id) as ad_id'),
            DB::raw('MAX(ads.name) as name'),
            DB::raw('MAX(ads.status) as status'),
            DB::raw('ROUND(SUM(total_revenue)/SUM(total_conversions), 2) as payout'),
            DB::raw('SUM(clicks) as clicks'),
            DB::raw('SUM(lp_views) as lp_views'),
            DB::raw('SUM(lp_clicks) as lp_clicks'),
            DB::raw('SUM(total_conversions) as total_conversions'),
            DB::raw('SUM(total_conversions) as total_actions'),
            DB::raw('ROUND((SUM(total_conversions)/SUM(clicks)) * 100, 2) as total_actions_cr'),
            DB::raw('ROUND((SUM(total_conversions)/SUM(clicks)) * 100, 2) as cr'),
            DB::raw('ROUND(SUM(total_revenue), 2) as total_revenue'),
            DB::raw('ROUND(SUM(cost), 2) as cost'),
            DB::raw('ROUND(SUM(profit), 2) as profit'),
            DB::raw('ROUND((SUM(profit)/SUM(cost)) * 100, 2) as roi'),
            DB::raw('ROUND(SUM(cost)/SUM(clicks), 2) as cpc'),
            DB::raw('ROUND(SUM(cost)/SUM(total_conversions), 2) as cpa'),
            DB::raw('ROUND(SUM(total_revenue)/SUM(clicks), 2) as epc'),
            DB::raw('ROUND((SUM(lp_clicks)/SUM(lp_views)) * 100, 2) as lp_ctr'),
            DB::raw('ROUND((SUM(total_conversions)/SUM(lp_views)) * 100, 2) as lp_views_cr'),
            DB::raw('ROUND((SUM(total_conversions)/SUM(lp_clicks)) * 100, 2) as lp_clicks_cr'),
            DB::raw('ROUND(SUM(cost)/SUM(lp_clicks), 2) as lp_cpc')
        ]);
        $contents_query->leftJoin('redtrack_content_stats', function ($join) use ($data) {
            $join->on('redtrack_content_stats.sub5', '=', 'ads.ad_id')->whereBetween('redtrack_content_stats.date', [$data['start'], $data['end']]);
        });
        $contents_query->where('ads.campaign_id', $campaign->campaign_id);
        $contents_query->where('name', 'LIKE', '%' . $data['search'] . '%');
        $contents_query->groupBy('ads.ad_id');

        return $contents_query;
    }

    public function getDomainQuery($campaign, $data)
    {
        //
    }
}

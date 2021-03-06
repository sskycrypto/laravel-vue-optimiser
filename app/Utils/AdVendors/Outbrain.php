<?php

namespace App\Utils\AdVendors;

use App\Endpoints\OutbrainAPI;
use App\Jobs\PullCampaign;
use App\Models\Ad;
use App\Models\Campaign;
use App\Models\OutbrainReport;
use App\Models\Provider;
use App\Models\RedtrackContentStat;
use App\Models\RedtrackPublisherStat;
use App\Models\RedtrackReport;
use App\Models\UserProvider;
use App\Models\UserTracker;
use App\Models\CreativeSet;
use App\Vngodev\AdVendorInterface;
use App\Vngodev\Helper;
use App\Vngodev\ResourceImporter;
use Carbon\Carbon;
use DB;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class Outbrain extends Root implements AdVendorInterface
{
    use \App\Utils\AdVendors\Attributes\Outbrain;

    private function api()
    {
        $provider = Provider::where('slug', request('provider'))->orWhere('id', request('provider'))->first();

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
            $budget_data = $api->createBudget(request('selectedAdvertiser'), [
                'name' => request('campaignName') . '_' . Carbon::now(),
                'amount' => request('campaignBudget'),
                'startDate' => request('campaignStartDate'),
                'endDate' => request('campaignEndDate'),
                'runForever' => request('campaignEndDate') ? false : true,
                'type' => request('campaignBudgetType'),
                'pacing' => request('campaignPacing')
            ]);

            Log::info('OUTBRAIN: Created budget: ' . $budget_data['id']);

            try {
                $campaign_data = $api->createCampaign([
                    'name' => request('campaignName'),
                    'cpc' => request('campaignCostPerClick'),
                    'enabled' => true,
                    'budgetId' => $budget_data['id'],
                    'creativeFormat' => request('campaignCreativeFormat'),
                    'targeting' => [
                        'platform' => request('campaginPlatform'),
                        'locations' => request('campaignLocation'),
                        'operatingSystems' => request('campaignOperatingSystem'),
                        'browsers' => request('campaignBrowser'),
                        'excludeAdBlockUsers' => request('campaignExcludeAdBlockUsers')
                    ],
                    'suffixTrackingCode' => request('campaignTrackingCode'),
                    'onAirType' => request('campaignStartTime') ? 'StartHour' : 'Scheduled',
                    'startHour' => strtoupper(request('campaignStartTime')),
                    'objective' => request('campaignObjective')
                ]);

                Log::info('OUTBRAIN: Created campaign: ' . $campaign_data['id']);
            } catch (Exception $e) {
                $api->deleteBudget($budget_data);
                throw $e;
            }

            try {
                foreach (request('ads') as $content) {
                    $titles = [];

                    $title_creative_set = null;
                    $image_creative_set = null;
                    $video_creative_set = null;

                    if (isset($content['titleSet']['id'])) {
                        $title_creative_set = CreativeSet::find($content['titleSet']['id']);

                        if ($title_creative_set) {
                            $titles = $title_creative_set->titleSets;
                        } else {
                            throw new Exception('No creative set found.');
                        }
                    } else {
                        $titles = $content['titles'];
                    }

                    foreach ($titles as $title) {
                        $images = [];

                        if (request('campaignCreativeFormat') == 'Standard') {
                            if (isset($content['imageSet']['id'])) {
                                $image_creative_set = CreativeSet::find($content['imageSet']['id']);

                                if ($image_creative_set) {
                                    $images = $image_creative_set->imageSets;
                                } else {
                                    throw new Exception('No creative set found.');
                                }
                            } else {
                                $images = $content['images'];
                            }
                        } else {
                            if (isset($content['videoSet']['id'])) {
                                $video_creative_set = CreativeSet::find($content['videoSet']['id']);

                                if ($video_creative_set) {
                                    $images = $video_creative_set->videoSets;
                                } else {
                                    throw new Exception('No creative set found.');
                                }
                            } else {
                                $images = $content['images'];
                            }
                        }

                        foreach ($images as $image) {
                            $ad = [
                                'text' => $title['title'],
                                'url' => $content['targetUrl'],
                                'enabled' => true
                            ];

                            if (request('campaignCreativeFormat') == 'Standard') {
                                $ad['imageMetadata'] = [
                                    'url' => Helper::encodeUrl($image_creative_set ? env('MIX_APP_URL') . '/storage/images/' . $image['hq_image'] : $image['url'])
                                ];
                            } else {
                                $ad['imageMetadata'] = [
                                    'url' => Helper::encodeUrl($video_creative_set ? env('MIX_APP_URL') . '/storage/images/' . $image['video'] : $image['url'])
                                ];
                            }

                            $ad_data = $api->createAd($campaign_data['id'], $ad);

                            $db_ad = Ad::firstOrNew([
                                'ad_id' => $ad_data['id'],
                                'user_id' => auth()->id(),
                                'provider_id' => 2,
                                'campaign_id' => $campaign_data['id'],
                                'advertiser_id' => request('selectedAdvertiser'),
                                'ad_group_id' => 'NA',
                                'open_id' => request('account')
                            ]);

                            $db_ad->name = $title['title'];
                            $db_ad->image = $ad_data['imageMetadata']['originalImageUrl'];
                            $db_ad->status = $ad_data['approvalStatus']['status'];

                            $db_ad->save();

                            $db_ad->creativeSets()->detach();

                            if ($title_creative_set) {
                                $db_ad->creativeSets()->save($title_creative_set);
                            }

                            if ($image_creative_set) {
                                $db_ad->creativeSets()->save($image_creative_set);
                            }
                        }
                    }
                }

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

    public function storeAd($campaign, $ad_group_id = null)
    {
        $api = $this->api();

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

            foreach ($ads as $key => $ad) {
                $ad_data = $api->createAd($campaign->campaign_id, $ad);
                Log::info('OUTBRAIN: Created ad: ' . $ad_data['id']);
            }
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }

        return [];
    }

    public function update(Campaign $campaign)
    {
        $data = [];
        $api = $this->api();

        try {
            $budget_data = $api->updateBudget(request('budgetId'));
            $campaign_data = $api->updateCampaign($campaign->campaign_id);

            foreach (request('ads') as $content) {
                $titles = [];

                $title_creative_set = null;
                $image_creative_set = null;
                $video_creative_set = null;

                if (isset($content['titleSet']['id'])) {
                    $title_creative_set = CreativeSet::find($content['titleSet']['id']);

                    if ($title_creative_set) {
                        $titles = $title_creative_set->titleSets;
                    } else {
                        throw new Exception('No creative set found.');
                    }
                } else {
                    $titles = $content['titles'];
                }

                foreach ($titles as $title) {
                    $images = [];
                    if (request('campaignCreativeFormat') == 'Standard') {
                        if (isset($content['imageSet']['id'])) {
                            $image_creative_set = CreativeSet::find($content['imageSet']['id']);

                            if ($image_creative_set) {
                                $images = $image_creative_set->imageSets;
                            } else {
                                throw new Exception('No creative set found.');
                            }
                        } else {
                            $images = $content['images'];
                        }
                    } else {
                        if (isset($content['videoSet']['id'])) {
                            $video_creative_set = CreativeSet::find($content['videoSet']['id']);

                            if ($video_creative_set) {
                                $images = $video_creative_set->videoSets;
                            } else {
                                throw new Exception('No creative set found.');
                            }
                        } else {
                            $images = $content['images'];
                        }
                    }

                    foreach ($images as $image) {
                        if (isset($content['id'])) {
                            $ad = [
                                'id' => $content['id'],
                                'text' => $title['title'],
                                'enabled' => true
                            ];

                            if (request('campaignCreativeFormat') == 'Standard') {
                                $ad['imageMetadata'] = [
                                    'url' => Helper::encodeUrl($image_creative_set ? env('MIX_APP_URL') . '/storage/images/' . $image['hq_image'] : $image['url'])
                                ];
                            } else {
                                $ad['imageMetadata'] = [
                                    'url' => Helper::encodeUrl($video_creative_set ? env('MIX_APP_URL') . '/storage/images/' . $image['video'] : $image['url'])
                                ];
                            }

                            $ad_data = $api->updateAd($campaign->campaign_id, [$ad]);

                            foreach ($ad_data as $item) {
                                if (isset($item['operationStatus']) && $item['operationStatus']['status'] == 'Failure') {
                                    throw new Exception($item['operationStatus']['reason'][0]);
                                }
                            }

                            $ad_data = $ad_data[0]['promotedLink'];
                        } else {
                            $ad = [
                                'text' => $title['title'],
                                'url' => $content['targetUrl'],
                                'enabled' => true
                            ];

                            if (request('campaignCreativeFormat') == 'Standard') {
                                $ad['imageMetadata'] = [
                                    'url' => Helper::encodeUrl($image_creative_set ? env('MIX_APP_URL') . '/storage/images/' . $image['hq_image'] : $image['url'])
                                ];
                            } else {
                                $ad['imageMetadata'] = [
                                    'url' => Helper::encodeUrl($video_creative_set ? env('MIX_APP_URL') . '/storage/images/' . $image['video'] : $image['url'])
                                ];
                            }

                            $ad_data = $api->createAd($campaign->campaign_id, $ad);
                        }

                        $db_ad = Ad::firstOrNew([
                            'ad_id' => $ad_data['id'],
                            'user_id' => auth()->id(),
                            'provider_id' => 2,
                            'campaign_id' => $campaign->campaign_id,
                            'advertiser_id' => request('selectedAdvertiser'),
                            'ad_group_id' => 'NA',
                            'open_id' => request('account')
                        ]);

                        $db_ad->name = $title['title'];
                        $db_ad->image = $ad_data['imageMetadata']['originalImageUrl'];
                        $db_ad->status = $ad_data['approvalStatus']['status'];

                        $db_ad->save();

                        $db_ad->creativeSets()->detach();

                        if ($title_creative_set) {
                            $db_ad->creativeSets()->save($title_creative_set);
                        }

                        if ($image_creative_set) {
                            $db_ad->creativeSets()->save($image_creative_set);
                        }
                    }
                }
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

            foreach ($instance['ads'] as &$ad) {
                $db_ad = Ad::where('ad_id', $ad['id'])->first();

                if ($db_ad) {
                    $image_set = $db_ad->creativeSets()->where('type', 1)->first();
                    if ($image_set) {
                        $ad['imageSet'] = $image_set;
                        $ad['imageSet']['sets'] = $image_set->imageSets;
                    }

                    $video_set = $db_ad->creativeSets()->where('type', 2)->first();
                    if ($video_set) {
                        $ad['videoSet'] = $video_set;
                        $ad['videoSet']['sets'] = $video_set->videoSets;
                    }

                    $title_set = $db_ad->creativeSets()->where('type', 3)->first();
                    if ($title_set) {
                        $ad['titleSet'] = $title_set;
                        $ad['titleSet']['sets'] = $title_set->titleSets;
                    }
                }
            }

            return $instance;
        } catch (Exception $e) {
            return [];
        }
    }

    public function getAdInstance(Campaign $campaign, $ad_group_id, $ad_id)
    {
        try {
            $api = new OutbrainAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first());

            $ads = $api->getPromotedLinks($campaign->campaign_id)['promotedLinks'];

            $ad = [];

            foreach ($ads as $item) {
                if ($item['id'] == $ad_id) {
                    $ad = $item;
                    break;
                }
            }

            $ad['open_id'] = $campaign['open_id'];

            return $ad;
        } catch (Exception $e) {
            return [];
        }
    }

    public function cloneCampaignName(&$instance)
    {
        $instance['name'] = $instance['name'] . ' - Copy';
    }

    public function cloneAdName(&$instance)
    {
        $instance['text'] = $instance['text'] . ' - Copy';
    }

    public function status(Campaign $campaign)
    {
        try {
            $api = new OutbrainAPI(UserProvider::where(['provider_id' => $campaign->provider_id, 'open_id' => $campaign->open_id])->first());
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
        $db_campaigns = [];
        $campaigns = [];
        $offset = 0;

        $resource_importer = new ResourceImporter();

        $updated_at = Carbon::now();

        foreach ($user_provider->advertisers as $key => $marketer) {
            $campaigns_by_marketer = $api->getCampaignsByMarketerId($marketer, $offset);
            if (array_key_exists('campaigns', $campaigns_by_marketer)) {
                $campaigns = $campaigns_by_marketer['campaigns'];
                if (array_key_exists('totalCount', $campaigns_by_marketer) && count($campaigns) < $campaigns_by_marketer['totalCount']) {
                    while ($offset < $campaigns_by_marketer['totalCount']) {
                        $offset += 25;
                        $loop_campaigns_by_marketer = $api->getCampaignsByMarketerId($marketer, $offset);
                        foreach ($loop_campaigns_by_marketer['campaigns'] as $index => $campaign) {
                            array_push($campaigns, $campaign);
                        }
                    }
                }
            }
            foreach ($campaigns as $campaign) {
                $db_campaigns[] = [
                    'campaign_id' => $campaign['id'],
                    'provider_id' => $user_provider->provider_id,
                    'open_id' => $user_provider->open_id,
                    'user_id' => $user_provider->user_id,
                    'advertiser_id' => $marketer,
                    'name' => $campaign['name'],
                    'status' => $campaign['enabled'] ? 'ACTIVE' : 'PAUSED',
                    'budget' => $campaign['budget']['amount'],
                    'updated_at' => $updated_at
                ];
            }
        }

        if (count($db_campaigns)) {
            $resource_importer->insertOrUpdate('campaigns', $db_campaigns, ['campaign_id', 'provider_id', 'user_id', 'open_id', 'advertiser_id']);

            Campaign::where([
                'user_id' => $user_provider->user_id,
                'provider_id' => $user_provider->provider_id,
                'open_id' => $user_provider->open_id
            ])->where('updated_at', '<>', $updated_at)->delete();
        }
    }

    public function pullAdGroup($user_provider)
    {
        //
    }

    public function pullAd($user_provider)
    {
        $api = new OutbrainAPI($user_provider);
        $db_ads = [];

        $resource_importer = new ResourceImporter();

        $updated_at = Carbon::now();

        Campaign::where('user_id', $user_provider->user_id)->where('provider_id', 2)->chunk(10, function ($campaigns) use ($resource_importer, $api, $user_provider, &$db_ads, $updated_at) {
            foreach ($campaigns as $key => $campaign) {
                $promoted_links = $api->getPromotedLinks($campaign->campaign_id);

                if ($promoted_links && isset($promoted_links['promotedLinks'])) {
                    foreach ($promoted_links['promotedLinks'] as $key => $ad) {
                        $db_ads[] = [
                            'ad_id' => $ad['id'],
                            'user_id' => $user_provider->user_id,
                            'provider_id' => $user_provider->provider_id,
                            'campaign_id' => $campaign->campaign_id,
                            'advertiser_id' => $campaign->advertiser_id,
                            'ad_group_id' => 'NA',
                            'open_id' => $user_provider->open_id,
                            'name' => $ad['text'],
                            'status' => $ad['approvalStatus']['status'],
                            'image' => $ad['imageMetadata']['originalImageUrl'],
                            'updated_at' => $updated_at
                        ];
                    }
                }
            }
        });

        if (count($db_ads)) {
            $resource_importer->insertOrUpdate('ads', $db_ads, ['ad_id', 'user_id', 'provider_id', 'campaign_id', 'advertiser_id', 'ad_group_id', 'open_id']);
            Ad::where([
                'user_id' => $user_provider->user_id,
                'provider_id' => $user_provider->provider_id,
                'open_id' => $user_provider->open_id
            ])->where('updated_at', '<>', $updated_at)->delete();
        }
    }

    public function adStatus(Campaign $campaign, $ad_group_id, $ad_id, $status = null)
    {
        $api = new OutbrainAPI(UserProvider::where(['provider_id' => $campaign->provider_id, 'open_id' => $campaign->open_id])->first());

        if ($status == null) {
            $status = request('status') == Campaign::STATUS_ACTIVE ? Campaign::STATUS_PAUSED : Campaign::STATUS_ACTIVE;
        }

        $ad = Ad::where('ad_id', $ad_id)->first();
        $ad->status = $status;
        $ad->save();

        $api->updatePromotedLinkStatus($ad_id, $status == Campaign::STATUS_ACTIVE);
    }

    public function pullRedTrack($user_provider, $target_date = null)
    {
        $tracker = UserTracker::where('provider_id', $user_provider->provider_id)->where('provider_open_id', $user_provider->open_id)->first();

        if ($tracker) {
            $client = new Client();
            $date = Carbon::now()->format('Y-m-d');
            if ($target_date) {
                $date = $target_date;
            }
            $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=sub5,hour_of_day&=sub9=Outbrain&tracks_view=true';
            $response = $client->get($url);

            $data = json_decode($response->getBody(), true);
            if (count($data)) {
                foreach ($data as $key => $value) {
                    $campaigns = Campaign::where('campaign_id', $value['sub5'])->get();
                    foreach ($campaigns as $index => $campaign) {
                        $value['date'] = $date;
                        $value['user_id'] = $campaign->user_id;
                        $value['campaign_id'] = $campaign->id;
                        $value['provider_id'] = $campaign->provider_id;
                        $value['open_id'] = $campaign->open_id;
                        $value['advertiser_id'] = $campaign->advertiser_id;
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
                }

                // Content stats
                $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=sub5,sub2&sub9=Outbrain&tracks_view=true';
                $response = $client->get($url);

                $data = json_decode($response->getBody(), true);
                foreach ($data as $key => $value) {
                    $campaigns = Campaign::where('campaign_id', $value['sub5'])->get();
                    foreach ($campaigns as $index => $campaign) {
                        $value['date'] = $date;
                        $value['user_id'] = $campaign->user_id;
                        $value['campaign_id'] = $campaign->id;
                        $value['provider_id'] = $campaign->provider_id;
                        $value['open_id'] = $campaign->open_id;
                        $value['advertiser_id'] = $campaign->advertiser_id;
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

                // Publishers stats
                $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=sub5,sub3,sub4,sub6,sub7&sub9=Outbrain&tracks_view=true';
                $response = $client->get($url);

                $data = json_decode($response->getBody(), true);
                foreach ($data as $key => $value) {
                    $campaigns = Campaign::where('campaign_id', $value['sub5'])->get();
                    foreach ($campaigns as $index => $campaign) {
                        $value['date'] = $date;
                        $value['user_id'] = $campaign->user_id;
                        $value['campaign_id'] = $campaign->id;
                        $value['provider_id'] = $campaign->provider_id;
                        $value['open_id'] = $campaign->open_id;
                        $value['advertiser_id'] = $campaign->advertiser_id;
                        $redtrack_report = RedtrackPublisherStat::firstOrNew([
                            'date' => $date,
                            'campaign_id' => $campaign->id,
                            'sub3' => $value['sub3'],
                            'sub7' => $value['sub7']
                        ]);
                        foreach (array_keys($value) as $array_key) {
                            $redtrack_report->{$array_key} = $value[$array_key];
                        }
                        $redtrack_report->save();
                    }
                }
            }
        }
    }

    public function getSummaryDataQuery($data, $campaign = null)
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
            if ($data['advertiser']) {
                $join->where('campaigns.advertiser_id', $data['advertiser']);
            }
        });
        $summary_data_query->whereBetween('date', [request('start'), request('end')]);

        return $summary_data_query;
    }

    public function getCampaignQuery($data)
    {
        $campaigns_query = Campaign::select([
            DB::raw('MAX(campaigns.id) AS id'),
            DB::raw('campaigns.campaign_id AS campaign_id'),
            DB::raw('MAX(campaigns.name) AS name'),
            DB::raw('MAX(campaigns.status) AS status'),
            DB::raw('MAX(campaigns.budget) AS budget'),
            DB::raw('SUM(JSON_EXTRACT(outbrain_reports.data, "$.summary.impressions")) as impressions'),
            DB::raw('SUM(JSON_EXTRACT(outbrain_reports.data, "$.summary.clicks")) as clicks'),
            DB::raw('ROUND(SUM(JSON_EXTRACT(outbrain_reports.data, "$.summary.spend")), 2) as cost')
        ]);
        $campaigns_query->leftJoin('outbrain_reports', function ($join) use ($data) {
            $join->on('outbrain_reports.campaign_id', '=', 'campaigns.id')->whereBetween('outbrain_reports.date', [$data['start'], $data['end']]);
        });
        if ($data['provider']) {
            $campaigns_query->where('campaigns.provider_id', $data['provider']);
        }
        if ($data['account']) {
            $campaigns_query->where('campaigns.open_id', $data['account']);
        }
        if ($data['advertiser']) {
            $campaigns_query->where('campaigns.advertiser_id', $data['advertiser']);
        }
        if ($data['search']) {
            $campaigns_query->where('name', 'LIKE', '%' . $data['search'] . '%');
        }
        $campaigns_query->whereIn('campaigns.id', Campaign::select(DB::raw('MAX(campaigns.id) AS id'))->groupBy('campaign_id'));
        $campaigns_query->groupBy('campaigns.id', 'campaigns.campaign_id');

        return $campaigns_query;
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
            DB::raw('MAX(ads.image) as image'),
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
            $join->on('redtrack_content_stats.sub2', '=', 'ads.ad_id')->whereBetween('redtrack_content_stats.date', [$data['start'], $data['end']]);
        });
        $contents_query->where('ads.campaign_id', $campaign->campaign_id);
        $contents_query->where('name', 'LIKE', '%' . $data['search'] . '%');
        $contents_query->groupBy('ads.ad_id');

        return $contents_query;
    }

    public function getPublisherQuery($campaign, $data)
    {
        $publishers_query = RedtrackPublisherStat::select([
            DB::raw('MAX(redtrack_publisher_stats.id) as id'),
            'sub3',
            'sub7',
            DB::raw('MAX(campaigns.campaign_id) as campaign_id'),
            DB::raw('MAX(campaigns.name) as name'),
            DB::raw('MAX(campaigns.status) as status'),
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
        $publishers_query->leftJoin('campaigns', function ($join) use ($data) {
            $join->on('campaigns.id', '=', 'redtrack_publisher_stats.campaign_id');
        });
        $publishers_query->whereBetween('redtrack_publisher_stats.date', [$data['start'], $data['end']]);
        $publishers_query->where('campaigns.campaign_id', $campaign->campaign_id);
        $publishers_query->where('name', 'LIKE', '%' . $data['search'] . '%');
        $publishers_query->groupBy(['sub3', 'sub7']);

        return $publishers_query;
    }

    public function getPublisherSelections($campaign) {
        $publishers = RedtrackPublisherStat::groupBy('sub6', 'sub3')->select('sub3', 'sub6')->where('sub6', '<>', '')->where('campaign_id', $campaign->id)->get();

        $results = [];

        foreach ($publishers as $publisher) {
            $results[] = [
                'id' => $publisher['sub6'],
                'text' => $publisher['sub3']
            ];
        }

        return $results;
    }

    public function getAdGroupQuery($campaign, $data)
    {
        //
    }

    public function getDomainQuery($campaign, $data)
    {
        //
    }

    public function getPerformanceQuery($campaign, $data)
    {
        //
    }

    public function getPerformanceData($campaign, $time_range)
    {
        return $campaign->outbrainReports()->whereBetween('date', [$time_range[0]->format('Y-m-d'), $time_range[1]->format('Y-m-d')])->get();
    }

    public function getDomainData($campaign, $time_range)
    {
        return $campaign->redtrackPublisherStats()->groupBy('sub4')
            ->selectRaw('sum(approved) as approved, sum(attribution) as attribution, sum(baddevice) as baddevice,
                sum(blacklist) as blacklist, sum(clicks) as clicks, sum(conversions) as conversions, sum(convtype1) as convtype1,
                sum(convtype2) as convtype2, sum(convtype3) as convtype3, sum(convtype4) as convtype4, sum(convtype5) as convtype5,
                sum(convtype6) as convtype6, sum(convtype7) as convtype7, sum(convtype8) as convtype8, sum(convtype9) as convtype9,
                sum(convtype10) as convtype10, sum(cost) as cost, sum(cpa) as cpa, sum(cpc) as cpc, sum(cpt) as cpt, sum(cr) as cr,
                sum(ctr) as ctr, sum(datacenter) as datacenter, sum(declined) as declined, sum(epc) as epc, sum(hour_of_day) as hour_of_day,
                sum(impressions) as impressions, sum(impressions_visible) as impressions_visible, sum(lp_clicks) as lp_clicks,
                sum(lp_ctr) as lp_ctr, sum(lp_views) as lp_views, sum(ok) as ok, sum(other) as other, sum(pending) as pending,
                sum(prelp_views) as prelp_views, sum(prelp_clicks) as prelp_clicks, sum(profit) as profit, sum(pubrevenue) as pubrevenue,
                sum(revenue) as revenue, sum(revenuetype1) as revenuetype1, sum(revenuetype2) as revenuetype2, sum(revenuetype3) as revenuetype3,
                sum(revenuetype4) as revenuetype4, sum(revenuetype5) as revenuetype5, sum(revenuetype6) as revenuetype6, sum(revenuetype7) as revenuetype7,
                sum(revenuetype8) as revenuetype8, sum(revenuetype9) as revenuetype9, sum(revenuetype10) as revenuetype10, sum(roi) as roi,
                sum(total_conversions) as total_conversions, sum(total_revenue) as total_revenue, sum(tr) as tr, sum(transactions) as transactions,
                sum(unique_clicks) as unique_clicks, sub4')
            ->whereBetween('date', [$time_range[0]->format('Y-m-d'), $time_range[1]->format('Y-m-d')])->get();
    }

    public function addSiteBlock($campaign, $data)
    {
        $api = new OutbrainAPI(UserProvider::where(['provider_id' => $campaign->provider_id, 'open_id' => $campaign->open_id])->first());

        $campaign_data = $api->getCampaign($campaign->campaign_id, 'BlockedSites');

        $blocked_publishers = $campaign_data['blockedSites']['blockedPublishers'] ?? [];

        $blocked_publishers[] = [
            'id' => $data['sub4']
        ];

        $api->updateCampaignData($campaign->campaign_id, [
            'blockedSites' => [
                'blockedPublishers' => $blocked_publishers
            ]
        ]);
    }

    public function removeSiteBlock($campaign, $data)
    {
        $api = new OutbrainAPI(UserProvider::where(['provider_id' => $campaign->provider_id, 'open_id' => $campaign->open_id])->first());

        $campaign_data = $api->getCampaign($campaign->campaign_id, 'BlockedSites');

        if (isset($campaign_data['blockedSites']) && isset($campaign_data['blockedSites']['blockedPublishers'])) {
            $blocked_publishers = $campaign_data['blockedSites']['blockedPublishers'];

            foreach ($blocked_publishers as $key => $blocked_publisher) {
                if ($blocked_publisher['id'] == $data['sub4']) {
                    array_splice($blocked_publishers, $key, 1);
                    break;
                }
            }

            $api->updateCampaignData($campaign->campaign_id, [
                'blockedSites' => [
                    'blockedPublishers' => $blocked_publishers
                ]
            ]);
        }
    }

    public function blockSite($campaign, $domain_id)
    {
        $data = $campaign->redtrackPublisherStats()->find($domain_id);

        if ($data) {
            $this->addSiteBlock($campaign, $data);
        }

        return [];
    }

    public function unBlockSite($campaign, $domain_id)
    {
        $data = $campaign->redtrackPublisherStats()->find($domain_id);

        if ($data) {
            $this->removeSiteBlock($campaign, $data);
        }

        return [];
    }

    public function targets(Campaign $campaign)
    {
        //
    }

    public function blockWidgets(Campaign $campaign, $widgets)
    {
        //
    }

    public function unblockWidgets(Campaign $campaign, $widgets)
    {
        //
    }

    public function changeBugget(Campaign $campaign, $data)
    {
        $api = new OutbrainAPI(UserProvider::where(['provider_id' => $campaign->provider_id, 'open_id' => $campaign->open_id])->first());
        $campaign_data = $api->getCampaign($campaign->campaign_id);

        $budget = 0;

        if (!isset($data->budgetSetType) || $data->budgetSetType == 1) {
            $budget = $data->budget;
        } else {
            $budget_data = $api->getBudget($campaign_data['budget']['id']);

            if ($data->budgetSetType == 2) {
                $budget = $budget_data['amount'] + ($data->budgetUnit == 1 ? $data->budget : $budget_data['amount'] * $data->budget / 100);

                if (!empty($data->budgetMax) && $budget > $data->budgetMax) {
                    $budget = $data->budgetMax;
                }
            } else {
                $budget = $budget_data['amount'] - ($data->budgetUnit == 1 ? $data->budget : $budget_data['amount'] * $data->budget / 100);

                if (!empty($data->budgetMin) && $budget < $data->budgetMin) {
                    $budget = $data->budgetMin;
                }
            }
        }

        $api->updateBudgetAmount($campaign_data['budget']['id'], $budget);
    }

    public function changeCampaignBid(Campaign $campaign, $data)
    {
        $api = new OutbrainAPI(UserProvider::where(['provider_id' => $campaign->provider_id, 'open_id' => $campaign->open_id])->first());

        $api->updateCampaignData($campaign->campaign_id, [
            'cpc' => $data->bid
        ]);
    }

    private function isCampaignGeneration($vendor) {
        if (count($vendor['campaigns']) == 0) {
            return false;
        }

        foreach ($vendor['campaigns'] as $campaign) {
            if (isset($campaign['id'])) {
                return true;
            }
        }

        return false;
    }

    public function storeCampaignVendors($vendor) {
        return $this->isCampaignGeneration($vendor) ? $this->generateAdVendors($vendor) : $this->createCampaignVendors($vendor);
    }

    private function generateAdVendors($vendor) {
        try {
            foreach ($vendor['campaigns'] as $campaign) {
                if (isset($campaign['id'])) {
                    $campaign_db = Campaign::find($campaign['id']);

                    if (!$campaign_db) {
                        continue;
                    }

                    $api = new OutbrainAPI(UserProvider::where([
                        'provider_id' => $campaign_db->provider_id,
                        'open_id' => $campaign_db->open_id
                    ])->first());

                    $campaign_data = $api->getCampaign($campaign_db->campaign_id);

                    foreach (request('contents') as $content) {
                        $titles = [];

                        $title_creative_set = null;
                        $image_creative_set = null;

                        $title_creative_set = CreativeSet::find($content['titleSet']['id']);

                        if ($title_creative_set) {
                            $titles = $title_creative_set->titleSets;
                        } else {
                            throw new Exception('No creative set found.');
                        }

                        foreach ($titles as $title) {
                            $images = [];

                            $image_creative_set = CreativeSet::find($content['imageSet']['id']);

                            if ($image_creative_set) {
                                $images = $image_creative_set->imageSets;
                            } else {
                                throw new Exception('No creative set found.');
                            }

                            foreach ($images as $image) {
                                $ad_data = $api->createAd($campaign_data['id'], [
                                    'text' => $title['title'],
                                    'url' => $content['targetUrl'],
                                    'enabled' => true,
                                    'imageMetadata' => [
                                        'url' => Helper::encodeUrl(env('MIX_APP_URL') . '/storage/images/' . $image['hq_image'])
                                    ]
                                ]);

                                $db_ad = Ad::firstOrNew([
                                    'ad_id' => $ad_data['id'],
                                    'user_id' => auth()->id(),
                                    'provider_id' => 2,
                                    'campaign_id' => $campaign_data['id'],
                                    'advertiser_id' => $vendor['selectedAdvertiser'],
                                    'ad_group_id' => 'NA',
                                    'open_id' => $vendor['selectedAccount']
                                ]);

                                $db_ad->name = $title['title'];
                                $db_ad->image = $ad_data['imageMetadata']['originalImageUrl'];
                                $db_ad->status = $ad_data['approvalStatus']['status'];

                                $db_ad->save();

                                $db_ad->creativeSets()->detach();

                                if ($title_creative_set) {
                                    $db_ad->creativeSets()->save($title_creative_set);
                                }

                                if ($image_creative_set) {
                                    $db_ad->creativeSets()->save($image_creative_set);
                                }

                                Log::info('OUTBRAIN: Created ad: ' . $ad_data['id']);
                            }
                        }
                    }
                }
            }

            Helper::pullCampaign();

            event(new \App\Events\CampaignVendorCreated(auth()->id(), [
                'success' => 1,
                'vendor' => 'outbrain',
                'vendorName' => 'Outbrain'
            ]));

            return [];
        } catch (RequestException $e) {
            event(new \App\Events\CampaignVendorCreated(auth()->id(), [
                'errors' => [$e->getMessage()],
                'vendor' => 'outbrain',
                'vendorName' => 'Outbrain'
            ]));

            return [
                'errors' => [$e->getMessage()]
            ];
        } catch (Exception $e) {
            event(new \App\Events\CampaignVendorCreated(auth()->id(), [
                'errors' => [$e->getMessage()],
                'vendor' => 'outbrain',
                'vendorName' => 'Outbrain'
            ]));

            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    private function createCampaignVendors($vendor) {
        $api = new OutbrainAPI(UserProvider::where(['provider_id' => 2, 'open_id' => $vendor['selectedAccount']])->first());

        try {
            $budget_data = $api->createBudget($vendor['selectedAdvertiser'], [
                'name' => request('campaignName') . '_' . Carbon::now(),
                'amount' => $vendor['campaignBudget'],
                'startDate' => $vendor['campaignStartDate'],
                'endDate' => $vendor['campaignEndDate'] ?? null,
                'runForever' => isset($vendor['campaignEndDate']) && $vendor['campaignEndDate'] ? false : true,
                'type' => $vendor['campaignBudgetType'],
                'pacing' => $vendor['campaignPacing']
            ]);
            Log::info('OUTBRAIN: Created budget: ' . $budget_data['id']);

            try {
                $campaign_data = $api->createCampaign([
                    'name' => request('campaignName'),
                    'cpc' => $vendor['campaignCostPerClick'],
                    'enabled' => true,
                    'budgetId' => $budget_data['id'],
                    'targeting' => [
                        'platform' => $vendor['campaginPlatform'] ?? [],
                        'locations' => $vendor['campaignLocation'] ?? [],
                        'operatingSystems' => $vendor['campaignOperatingSystem'] ?? [],
                        'browsers' => $vendor['campaignBrowser'] ?? [],
                        'excludeAdBlockUsers' => $vendor['campaignExcludeAdBlockUsers']
                    ],
                    'suffixTrackingCode' => $vendor['campaignTrackingCode'] ?? null,
                    'onAirType' => $vendor['campaignStartTime'] ? 'StartHour' : 'Scheduled',
                    'startHour' => strtoupper($vendor['campaignStartTime']) ?? '',
                    'objective' => $vendor['campaignObjective']
                ]);

                Log::info('OUTBRAIN: Created campaign: ' . $campaign_data['id']);
            } catch (Exception $e) {
                $api->deleteBudget($budget_data);
                throw $e;
            }

            try {
                foreach (request('contents') as $content) {
                    $titles = [];

                    $title_creative_set = null;
                    $image_creative_set = null;

                    $title_creative_set = CreativeSet::find($content['titleSet']['id']);

                    if ($title_creative_set) {
                        $titles = $title_creative_set->titleSets;
                    } else {
                        throw new Exception('No creative set found.');
                    }

                    foreach ($titles as $title) {
                        $images = [];

                        $image_creative_set = CreativeSet::find($content['imageSet']['id']);

                        if ($image_creative_set) {
                            $images = $image_creative_set->imageSets;
                        } else {
                            throw new Exception('No creative set found.');
                        }

                        foreach ($images as $image) {
                            $ad_data = $api->createAd($campaign_data['id'], [
                                'text' => $title['title'],
                                'url' => $content['targetUrl'],
                                'enabled' => true,
                                'imageMetadata' => [
                                    'url' => Helper::encodeUrl(env('MIX_APP_URL') . '/storage/images/' . $image['hq_image'])
                                ]
                            ]);

                            $db_ad = Ad::firstOrNew([
                                'ad_id' => $ad_data['id'],
                                'user_id' => auth()->id(),
                                'provider_id' => 2,
                                'campaign_id' => $campaign_data['id'],
                                'advertiser_id' => $vendor['selectedAdvertiser'],
                                'ad_group_id' => 'NA',
                                'open_id' => $vendor['selectedAccount']
                            ]);

                            $db_ad->name = $title['title'];
                            $db_ad->image = $ad_data['imageMetadata']['originalImageUrl'];
                            $db_ad->status = $ad_data['approvalStatus']['status'];

                            $db_ad->save();

                            $db_ad->creativeSets()->detach();

                            if ($title_creative_set) {
                                $db_ad->creativeSets()->save($title_creative_set);
                            }

                            if ($image_creative_set) {
                                $db_ad->creativeSets()->save($image_creative_set);
                            }

                            Log::info('OUTBRAIN: Created ad: ' . $ad_data['id']);
                        }
                    }
                }
            } catch (Exception $e) {
                $api->deleteCampaign($campaign_data['id']);
                $api->deleteBudget($budget_data);
                throw $e;
            }

            Helper::pullCampaign();

            event(new \App\Events\CampaignVendorCreated(auth()->id(), [
                'success' => 1,
                'vendor' => 'outbrain',
                'vendorName' => 'Outbrain'
            ]));

            return [];
        } catch (RequestException $e) {
            event(new \App\Events\CampaignVendorCreated(auth()->id(), [
                'errors' => [$e->getMessage()],
                'vendor' => 'outbrain',
                'vendorName' => 'Outbrain'
            ]));

            return [
                'errors' => [$e->getMessage()]
            ];
        } catch (Exception $e) {
            event(new \App\Events\CampaignVendorCreated(auth()->id(), [
                'errors' => [$e->getMessage()],
                'vendor' => 'outbrain',
                'vendorName' => 'Outbrain'
            ]));

            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function delete($campaign) {

    }

    public function changePublishserBid($campaign, $data) {
        $api = new OutbrainAPI(UserProvider::where(['provider_id' => $campaign->provider_id, 'open_id' => $campaign->open_id])->first());

        $sections = [];

        foreach ($data->sections as $section) {
            $sections[] = [
                'sectionId' => $section,
                'cpcAdjustment' => $data->cpcAdjustment
            ];
        }

        $api->updateCampaignData($campaign->campaign_id, [
            'bids' => [
                'bySection' => $sections
            ]
        ]);
    }
}

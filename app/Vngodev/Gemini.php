<?php

namespace App\Vngodev;

use App\Imports\GeminiReportImport;
use App\Models\Campaign;
use App\Models\GeminiJob;
use App\Models\User;
use App\Vngodev\Token;
use Carbon\Carbon;
use Excel;
use Exception;
use GuzzleHttp\Client;

/**
 * Gemini
 */
class Gemini
{
    public function __construct()
    {
        //
    }

    public static function crawl()
    {
        $date = Carbon::now()->format('Y-m-d');
        foreach (User::all() as $key => $user) {
            foreach ($user->campaigns as $index => $campaign) {
                if (!GeminiJob::where('user_id', $user->id)->where('campaign_id', $campaign->campaign_id)->where('advertiser_id', $campaign->advertiser_id)->where('status', 'submitted')->exists()) {
                    $job = [];
                    $user_info = $user->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first();
                    Token::refresh($user_info, function () use ($campaign, $user_info, $date, &$job) {
                        $job = self::getDataByCampaign($user_info, $date, $campaign->campaign_id, $campaign->advertiser_id);
                    });
                    $gemini_job = GeminiJob::firstOrNew([
                        'user_id' => $user->id,
                        'campaign_id' => $campaign->campaign_id,
                        'advertiser_id' => $campaign->advertiser_id,
                        'status' => 'submitted'
                    ]);
                    $gemini_job->job_id = $job['response']['jobId'];
                    $gemini_job->job_response = $job['response']['jobResponse'];
                    $gemini_job->submited_at = $job['timestamp'];

                    $gemini_job->save();
                }
            }
        }
    }

    public static function checkJobs()
    {
        foreach (GeminiJob::where('status', 'submitted')->get() as $key => $job) {
            $campaign = Campaign::where('campaign_id', $job->campaign_id)->first();
            $user = User::find($job->user_id);
            $user_info = $user->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first();
            $job_status = self::getJobStatus($user_info, $job->job_id, $campaign->advertiser_id);

            $job->status = $job_status['response']['status'];
            $job->job_response = $job_status['response']['jobResponse'];
            $job->save();

            if ($job->status === 'completed') {
                $report_file = file_get_contents($job->job_response);
                $file_name = $job->user_id . '_' . $job->campaign_id . '_' . $job->advertiser_id . '_' . $job->job_id . '.csv';
                file_put_contents(public_path('reports/' . $file_name), $report_file);
                Excel::queueImport(new GeminiReportImport, public_path('reports/' . $file_name));
            }
        }
    }

    private static function getDataByCampaign($user_info, $date, $campaign_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('POST', env('BASE_URL') . '/v3/rest/reports/custom', [
            'body' => json_encode([
                'cube' => 'performance_stats',
                'fields' => [
                    ['field' => 'Advertiser ID'],
                    ['field' => 'Campaign ID'],
                    ['field' => 'Ad Group ID'],
                    ['field' => 'Ad ID'],
                    ['field' => 'Month'],
                    ['field' => 'Week'],
                    ['field' => 'Day'],
                    ['field' => 'Hour'],
                    ['field' => 'Pricing Type'],
                    ['field' => 'Device Type'],
                    ['field' => 'Source Name'],
                    ['field' => 'Post Click Conversions'],
                    ['field' => 'Post Impression Conversions'],
                    ['field' => 'CTR'],
                    ['field' => 'Average CPC'],
                    ['field' => 'Average CPM'],
                    ['field' => 'Fact Conversion Counting'],
                    ['field' => 'Impressions'],
                    ['field' => 'Clicks'],
                    ['field' => 'Conversions'],
                    ['field' => 'Total Conversions'],
                    ['field' => 'Average Position'],
                    ['field' => 'Max Bid'],
                    ['field' => 'Ad Extn Impressions'],
                    ['field' => 'Spend'],
                    ['field' => 'Native Bid']
                ],
                'filters' => [
                    ['field' => 'Advertiser ID', 'operator' => '=', 'value' => $advertiser_id],
                    ['field' => 'Campaign ID', 'operator' => '=', 'value' => $campaign_id],
                    ['field' => 'Day', 'operator' => 'between', 'from' => $date, 'to' => $date]
                ]
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    private static function getJobStatus($user_info, $job_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('GET', env('BASE_URL') . '/v3/rest/reports/custom/' . $job_id . '?advertiserId=' . $advertiser_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }
}
<?php

namespace App\Vngodev;

use App\Jobs\PullOutbrainReport;
use DB;

/**
 * Outbrain
 */
class Outbrain
{
    public function __construct()
    {
        //
    }

    public static function getReport()
    {
        DB::table('user_providers')->where('provider_id', 2)->chunkById(5, function ($user_providers) {
            foreach ($user_providers as $user_provider) {
                $campaign_accounts = DB::table('campaigns')->select('open_id', 'provider_id', 'advertiser_id')->groupBy('open_id', 'provider_id', 'advertiser_id')->where([
                    'provider_id' => $user_provider->provider_id,
                    'open_id' => $user_provider->open_id
                ])->get();

                foreach ($campaign_accounts as $campaign_account) {
                    DB::table('campaigns')->where([
                        'advertiser_id' => $campaign_account->advertiser_id
                    ])->chunkById(20, function ($campaigns) use ($campaign_account) {
                        foreach ($campaigns as $campaign) {
                            PullOutbrainReport::dispatch($campaign);
                            sleep(10);
                        }
                    });
                }
            }
        });
    }
}

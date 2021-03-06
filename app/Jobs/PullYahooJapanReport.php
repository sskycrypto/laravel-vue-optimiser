<?php

namespace App\Jobs;

use DB;

use App\Endpoints\YahooJPAPI;
use App\Models\Campaign;
use App\Models\YahooJapanReport;
use App\Models\UserProvider;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullYahooJapanReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $date;

    const CURRENCY_RATE = 0.0091;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($date)
    {
        $this->date = $date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $target_date = Carbon::now()->format('Ymd');
        if ($this->date) {
            $target_date = $this->date;
        }
        DB::table('campaigns')->where('provider_id', 5)->chunkById(20, function ($campaigns) use ($target_date) {
            $campaign_ids = [];
            foreach ($campaigns as $campaign) {
                $campaign_ids[] = $campaign->campaign_id;
            }
            $api = new YahooJPAPI(UserProvider::where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first());

            $report_data = $api->getReport($campaign->advertiser_id, $campaign_ids, $target_date, $target_date);

            if ($report_data['rval']) {
                foreach ($report_data['rval']['values'] as $item) {
                    $stats = $item['campaignStatsValue'];

                    $report = YahooJapanReport::firstOrNew([
                        'campaign_id' => Campaign::where('campaign_id', $stats['campaignId'])->first()->id,
                        'date' => $target_date
                    ]);

                    if (config('constants.currency_convert')) {
                        $stats['stats']['cost'] = $stats['stats']['cost'] * self::CURRENCY_RATE;
                        $stats['stats']['avgCpc'] = $stats['stats']['avgCpc'] * self::CURRENCY_RATE;
                        $stats['stats']['cpa'] = $stats['stats']['cpa'] * self::CURRENCY_RATE;
                        $stats['stats']['conversionValue'] = $stats['stats']['conversionValue'] * self::CURRENCY_RATE;
                        $stats['stats']['valuePerConversions'] = $stats['stats']['valuePerConversions'] * self::CURRENCY_RATE;
                        $stats['stats']['allConversionValue'] = $stats['stats']['allConversionValue'] * self::CURRENCY_RATE;
                        $stats['stats']['valuePerConversionsViaAdClick'] = $stats['stats']['valuePerConversionsViaAdClick'] * self::CURRENCY_RATE;
                        $stats['stats']['cpaViaAdClick'] = $stats['stats']['cpaViaAdClick'] * self::CURRENCY_RATE;
                        $stats['stats']['allCpa'] = $stats['stats']['allCpa'] * self::CURRENCY_RATE;
                        $stats['stats']['averageCpv'] = $stats['stats']['averageCpv'] * self::CURRENCY_RATE;
                    }

                    foreach (array_keys($stats['stats']) as $key) {
                        $report->{preg_replace('/([A-Z])/', '_$1', $key)} = $stats['stats'][$key];
                    }

                    $report->save();
                }
            }
        });
    }
}

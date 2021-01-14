<?php

namespace App\Jobs;

use App\Endpoints\OutbrainAPI;
use App\Models\Campaign;
use App\Models\OutbrainReport;
use App\Models\UserProvider;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullOutbrainReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campaign;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $date = Carbon::now()->format('Y-m-d');
        $api = new OutbrainAPI(UserProvider::where('provider_id', $this->campaign->provider_id)->where('open_id', $this->campaign->open_id)->first());

        $report = OutbrainReport::firstOrNew([
            'campaign_id' => $this->campaign->id,
            'date' => $date
        ]);
        $report->data = json_encode($api->getPerformanceReport($this->campaign, $date));
        $report->save();
    }
}

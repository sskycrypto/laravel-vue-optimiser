<?php
namespace App\Vngodev;

use App\Models\Campaign;

interface AdVendorInterface
{
    public function getSummaryDataQuery(array $data, $campaign = null);

    public function getCampaignQuery(array $data);

    public function getWidgetQuery(Campaign $campaign, array $data);

    public function getContentQuery(Campaign $campaign, array $data);

    public function getAdGroupQuery(Campaign $campaign, array $data);

    public function getDomainQuery(Campaign $campaign, array $data);

    public function getPerformanceQuery(Campaign $campaign, array $data);
}
<?php

namespace App\Endpoints;

use DateTime;
use Exception;

use Carbon\Carbon;

use App\Helpers\GeminiClient;

use Hborras\TwitterAdsSDK\TwitterAds;
use Hborras\TwitterAdsSDK\TwitterAds\Account;
use Hborras\TwitterAdsSDK\TwitterAds\Campaign\LineItem;
use Hborras\TwitterAdsSDK\TwitterAds\Campaign\Campaign;
use Hborras\TwitterAdsSDK\TwitterAds\Campaign\FundingInstrument;

class TwitterAPI
{
    private $client;

    private $account_id;

    public function __construct($user_info, $account_id)
    {
        $this->account_id = $account_id;
        $this->client = TwitterAds::init(env('TWITTER_CLIENT_ID'), env('TWITTER_CLIENT_SECRET'), $user_info->token, $user_info->secret_token, $account_id, env('TWITTER_SANDBOX'));
    }

    public function getAdvertisers()
    {
        return $this->client->getAccounts()->getCollection();
    }

    public function getCountries()
    {
        return $this->client->get('targeting_criteria/locations', ['location_type' => 'COUNTRIES'])->getBody()->data;
    }

    public function createAccount()
    {
        return (new Account())->save();
    }

    public function getFundingInstruments()
    {
        $account = new Account($this->account_id);
        $account->read();

        return $account->getFundingInstruments()->getCollection();
    }

    public function createCampaign()
    {
        try {
            $account = new Account($this->account_id);
            $account->read();

            $campaign = new Campaign();
            $campaign->setFundingInstrumentId(request('fundingInstrument'));
            $campaign->setDailyBudgetAmountLocalMicro(request('campaignDailyBudgetAmountLocalMicro') * 1E6);
            $campaign->setName(request('campaignName'));
            $campaign->setEntityStatus(request('campaignStatus'));
            $campaign->setStartTime(request('campaignStartTime'));
            return $campaign->save();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function createLineItem($campaign)
    {
        try {
            $account = new Account($this->account_id);
            $account->read();

            $line_item = new LineItem();
            $line_item->setCampaignId($campaign->getId());
            $line_item->setName(request('adGroupName'));
            $line_item->setProductType(request('adGroupProductType'));
            $line_item->setPlacements(request('adGroupPlacements'));
            $line_item->setObjective(request('adGroupObjective'));
            $line_item->setBidAmountLocalMicro(request('adGroupBidAmountLocalMicro') * 1E6);
            $line_item->setEntityStatus(request('adGroupStatus'));
            $line_item->setCategories(request('adGroupCategory'));
            $line_item->setAdvertiserDomain(request('adGroupAdvertiserDomain'));
            return $line_item->save();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function createCard()
    {
        try {

        } catch (Exception $e) {
            var_dump($e);exit;
            throw $e;
        }
    }
}
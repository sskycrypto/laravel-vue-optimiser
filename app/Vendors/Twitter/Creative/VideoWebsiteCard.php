<?php

namespace App\Vendors\Twitter\Creative;

use Hborras\TwitterAdsSDK\TwitterAds\Resource;
use App\Vendors\Twitter\Field\VideoWebsiteCardFields;

/**
 * Class VideoWebsiteCard
 * @package Hborras\TwitterAdsSDK\TwitterAds\Creative
 */
class VideoWebsiteCard extends Resource
{
    const RESOURCE_COLLECTION = 'accounts/{account_id}/cards/video_website';
    const RESOURCE = 'accounts/{account_id}/cards/video_website/{id}';

    /** Read Only */
    protected $id;
    protected $card_uri;
    protected $created_at;
    protected $updated_at;
    protected $deleted;

    protected $properties = [
        VideoWebsiteCardFields::NAME,
        VideoWebsiteCardFields::TITLE,
        VideoWebsiteCardFields::WEBSITE_URL,
        VideoWebsiteCardFields::MEDIA_KEY,
    ];

    /** Writable */
    protected $name;
    protected $title;
    protected $media_key;
    protected $website_url;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param array $properties
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getWebsiteUrl()
    {
        return $this->website_url;
    }

    /**
     * @param mixed $website_url
     */
    public function setWebsiteUrl($website_url)
    {
        $this->website_url = $website_url;
    }

    /**
     * @return mixed
     */
    public function getCardUri()
    {
        return $this->card_uri;
    }

    public function getMediaKey()
    {
        return $this->media_key;
    }

    public function setMediaKey($media_key)
    {
        $this->media_key = $media_key;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @return mixed
     */
    public function getDeleted()
    {
        return $this->deleted;
    }
}

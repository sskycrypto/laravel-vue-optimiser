<?php

namespace App\Utils\AdVendors\Attributes;

trait Yahoo
{
    public function impressions($data, $calculation_type)
    {
        return $data['impressions'];
    }

    public function spend($data, $calculation_type)
    {
        if (isset($data['spend'])) {
            return $data['spend'];
        } else if (isset($data['cost'])) {
            return $data['cost'];
        } else {
            throw new Exception('No attribute was found.');
        }
    }

    public function clicks($data, $calculation_type)
    {
        return $data['clicks'];
    }

    public function lpClicks($data, $calculation_type)
    {
        if (isset($data['lp_clicks'])) {
            return $data['lp_clicks'];
        } else {
            throw new Exception('No attribute was found.');
        }
    }

    public function lpViews($data, $calculation_type)
    {
        if (isset($data['lp_views'])) {
            return $data['lp_views'];
        } else {
            throw new Exception('No attribute was found.');
        }
    }

    public function revenue($data, $calculation_type)
    {
        if (isset($data['revenue'])) {
            return $data['revenue'];
        } else {
            throw new Exception('No attribute was found.');
        }
    }

    public function profit($data, $calculation_type)
    {
        if (isset($data['profit'])) {
            return $data['profit'];
        } else {
            throw new Exception('No attribute was found.');
        }
    }

    public function cost($data, $calculation_type)
    {
        if (isset($data['spend'])) {
            return $data['spend'];
        } else if (isset($data['cost'])) {
            return $data['cost'];
        } else {
            throw new Exception('No attribute was found.');
        }
    }

    public function conversions($data, $calculation_type)
    {
        if (isset($data['conversions'])) {
            return $data['conversions'];
        } else {
            throw new Exception('No attribute was found.');
        }
    }
}
<?php

namespace packages\sitemap;

use packages\base;
use packages\base\Date;

class Item
{
    public const always = 'always';
    public const hourly = 'hourly';
    public const daily = 'daily';
    public const weekly = 'weekly';
    public const monthly = 'monthly';
    public const yearly = 'yearly';
    public const never = 'never';

    private $url;
    private $uri;
    private $changefreq;
    private $lastmodified;
    private $priority;

    public function setURL($url)
    {
        $this->url = $url;
        $this->uri = base\url($this->url, ['@hostname' => ''], true);
    }

    public function setURI($uri)
    {
        $this->uri = $uri;
    }

    public function getURI()
    {
        return $this->uri;
    }

    public function SetChangeFreq($changefreq)
    {
        if (in_array($changefreq, [
            self::always,
            self::hourly,
            self::daily,
            self::weekly,
            self::monthly,
            self::yearly,
            self::never,
        ])) {
            $this->changefreq = $changefreq;
        } else {
            throw new \Exception($changefreq);
        }
    }

    public function getChangeFreq()
    {
        return $this->changefreq;
    }

    public function setLastModified($time)
    {
        if (is_string($time)) {
            $time = strtotime($time);
        }
        if (is_numeric($time)) {
            if ($time <= Date::time()) {
                $this->lastmodified = $time;
            } else {
                throw new \Exception($time);
            }
        } else {
            throw new \Exception($time);
        }
    }

    public function getLastModified()
    {
        return $this->lastmodified ? date('c', $this->lastmodified) : null;
    }

    public function setPriority($priority)
    {
        if ($priority > 0 and $priority <= 1) {
            $this->priority = $priority;
        } else {
            throw new \Exception($priority);
        }
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function isAllowedByDomain(array $domains)
    {
        $domain = parse_url($this->uri, PHP_URL_HOST);
        if ('www.' == substr($domain, 0, 4)) {
            $domain = substr($domain, 4);
        }

        return in_array($domain, $domains);
    }
}

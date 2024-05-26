<?php

namespace packages\sitemap\Events;

use packages\base\Event;
use packages\sitemap\FileException;
use packages\sitemap\Item;

class SiteMap extends Event
{
    private $files = [];
    private $items = [];

    public function fromFile($file)
    {
        if (!is_file($file) or !is_readable($file)) {
            throw new FileException();
        }
        $this->files[] = $file;
    }

    public function add(Item $item)
    {
        $this->items[] = $item;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function getItems()
    {
        return $this->items;
    }
}

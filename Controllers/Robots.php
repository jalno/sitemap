<?php

namespace packages\sitemap\Controllers;

use packages\base;
use packages\base\Json;
use packages\base\Packages;
use packages\sitemap\Controller;

class Robots extends Controller
{
    protected $items = [];

    public function build()
    {
        $this->getRobots();

        $this->response->setMimeType('text/plain');
        $code = 'Sitemap: '.base\url('sitemap.xml', ['@lang' => ''], true)."\n";
        foreach ($this->items as $item) {
            if ('disallow' == $item['type']) {
                $code .= "User-agent: {$item['user-agent']}\n";
                $code .= 'Disallow: '.base\url($item['url'])."\n";
            }
        }
        $this->response->rawOutput($code);

        return $this->response;
    }

    public function getRobots()
    {
        $packages = Packages::get();
        foreach ($packages as $package) {
            if ($robotOption = $package->getOption('robots')) {
                if (is_array($robotOption)) {
                    foreach ($robotOption as $source) {
                        if ('static' == $source['type']) {
                            if (isset($source['file']) and $path = $package->getFilePath($source['file'])) {
                                $this->importRobotsFromFile($path);
                            } else {
                                throw new \Exception();
                            }
                        } elseif ('dynamic' == $source['type']) {
                            if (isset($source['controller'])) {
                                list($controller, $method) = explode('@', $source['controller'], 2);
                                $controller = '\\packages\\'.$package->getName()."\\$controller";
                                if (class_exists($controller) and method_exists($controller, $method)) {
                                    $controllerClass = new $controller();
                                    $items = $controllerClass->$method();
                                    if (is_array($items)) {
                                        foreach ($items as $citem) {
                                            if ('disallow' == $citem['type']) {
                                                if (isset($citem['user-agent'], $citem['url'])) {
                                                    $this->items[] = [
                                                        'type' => $citem['type'],
                                                        'user-agent' => $citem['user-agent'],
                                                        'url' => $citem['url'],
                                                    ];
                                                } else {
                                                    throw new \Exception();
                                                }
                                            } else {
                                                throw new \Exception();
                                            }
                                        }
                                    } else {
                                        throw new \Exception();
                                    }
                                } else {
                                    throw new \Exception();
                                }
                            } else {
                                throw new \Exception();
                            }
                        }
                    }
                } elseif ($path = $package->getFilePath($robotOption)) {
                    $this->importRobotsFromFile($path);
                }
            }
        }
    }

    public function importRobotsFromFile($file)
    {
        if (is_file($file) and is_readable($file) and $contents = file_get_contents($file)) {
            if ($contents = json\decode($contents)) {
                if (isset($contents['items'])) {
                    if (is_array($contents['items'])) {
                        foreach ($contents['items'] as $citem) {
                            if ('disallow' == $citem['type']) {
                                if (isset($citem['user-agent'], $citem['url'])) {
                                    $this->items[] = [
                                        'type' => $citem['type'],
                                        'user-agent' => $citem['user-agent'],
                                        'url' => $citem['url'],
                                    ];
                                } else {
                                    throw new \Exception();
                                }
                            } else {
                                throw new \Exception();
                            }
                        }
                    } else {
                        throw new \Exception();
                    }
                }
            } else {
                throw new \Exception();
            }
        } else {
            throw new \Exception();
        }
    }
}

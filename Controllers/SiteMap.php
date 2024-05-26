<?php
namespace packages\sitemap\Controllers;
use \packages\base\Packages;
use \packages\base\Json;
use \packages\base\HTTP;
use \packages\base\Events;
use \packages\sitemap\Item;
use \packages\sitemap\Controller;
use \packages\sitemap\Events\SiteMap as SiteMapEvent;
use \packages\sitemap\FileException;
use \packages\sitemap\JsonParseException;
class SiteMap extends Controller{
	protected $allowedDomains = [];
	protected $items = array();
	public function build(){
		$this->addAllowedDomain(HTTP::$request['hostname']);
		$this->getSiteMaps();

		$this->response->setMimeType('text/xml');
		$code = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$code .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'."\n";
		usort($this->items, function($a, $b){
			$a = $a->getPriority();
			$b = $b->getPriority();
			if($a > $b){
				return -1;
			}elseif($a < $b){
				return 1;
			}else{
				return 0;
			}
		});
		foreach($this->items as $item){
			$code .= "<url>\n";
			$code .= "\t<loc>".$item->getURI()."</loc>\n";
			$changefreq = $item->getChangeFreq();
			$priority = $item->getPriority();
			$lastmodified = $item->getLastModified();
			if($changefreq){
				$code .= "\t<changefreq>{$changefreq}</changefreq>\n";
			}
			if($priority !== null){
				$code .= "\t<priority>{$priority}</priority>\n";
			}
			if($lastmodified){
				$code .= "\t<lastmod>{$lastmodified}</lastmod>\n";
			}
			$code .= "</url>\n";
		}
		$code .= "</urlset>";
		$this->response->rawOutput($code);
		return $this->response;
	}
	private function sendEvent(){
		$event = new SiteMapEvent();
		Events::trigger($event);
		foreach($event->getFiles() as $file){
			$this->importSitemapFromFile($file);
		}
		foreach($event->getItems() as $item){
			if($item->isAllowedByDomain($this->allowedDomains)){
				$this->items[] = $item;

			}
		}
	}
	public function getSiteMaps(){
		$this->sendEvent();
		$packages = Packages::get();
		foreach($packages as $package){
			if($sitemapOption = $package->getOption('sitemap')){
				if(is_array($sitemapOption)){
					foreach($sitemapOption as $source){
						if($source['type'] == 'static'){
							if(isset($source['file']) and $path = $package->getFilePath($source['file'])){
								$this->importSitemapFromFile($path);
							}else{
								throw new \Exception();
							}
						}elseif($source['type'] == 'dynamic'){
							if(isset($source['controller'])){
								list($controller, $method) = explode('@', $source['controller'],2);
								$controller = "\\packages\\".$package->getName()."\\$controller";
								if(class_exists($controller) and method_exists($controller, $method)){
									$controllerClass = new $controller();
									$items = $controllerClass->$method();
									if(is_array($items)){
										foreach($items as $item){
											if($item instanceof Item){
												if($item->isAllowedByDomain($this->allowedDomains)){
													$this->items[] = $item;
												}
											}else{
												throw new \Exception;
											}
										}
									}else{
										throw new \Exception;
									}
								}else{
									throw new \Exception;
								}
							}else{
								throw new \Exception();
							}
						}
					}
				}elseif($path = $package->getFilePath($sitemapOption)){
					$this->importSitemapFromFile($path);
				}
			}
		}
	}
	public function importSitemapFromFile($file){
		if(is_file($file) and is_readable($file) and $contents = file_get_contents($file)){
			if($contents = Json\Decode($contents)){
				if(isset($contents['items'])){
					if(is_array($contents['items'])){
						foreach($contents['items'] as $citem){
							$item = new Item();
							$item->setURL($citem['url']);
							if(isset($citem['changefreq'])){
								$item->SetChangeFreq($citem['changefreq']);
							}
							if(isset($citem['lastmodified'])){
								$item->setLastModified($citem['lastmodified']);
							}
							if(isset($citem['priority'])){
								$item->setPriority($citem['priority']);
							}
							if($item->isAllowedByDomain($this->allowedDomains)){
								$this->items[] = $item;
							}
						}
					}else{
						throw new \Exception();
					}
				}
			}else{
				throw new JsonParseException();
			}
		}else{
			throw new FileException();
		}
	}
	protected function addAllowedDomain(string $domain){
		if(substr($domain, 0, 4) == "www."){
			$domain = substr($domain, 4);
		}
		if(!in_array($domain, $this->allowedDomains)){
			$this->allowedDomains[] = $domain;
		}
	}
}

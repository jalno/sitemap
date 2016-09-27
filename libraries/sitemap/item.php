<?php
namespace packages\sitemap;
use \packages\base;
use \packages\base\date;
class item{
	const always = 'always';
	const hourly = 'hourly';
	const daily = 'daily';
	const weekly = 'weekly';
	const monthly = 'monthly';
	const yearly = 'yearly';
	const never = 'never';

	private $url;
	private $uri;
	private $changefreq;
	private $lastmodified;
	public function setURL($url){
		$this->url = $url;
		$this->uri = base\url($this->url, array(), true);
	}
	public function setURI($uri){
		$this->uri = $uri;
	}
	public function getURI(){
		return  $this->uri;
	}
	public function SetChangeFreq($changefreq){
		if(in_array($changefreq, array(
			self::always,
			self::hourly,
			self::daily,
			self::weekly,
			self::monthly,
			self::yearly,
			self::never
		))){
			$this->changefreq = $changefreq;
		}else{
			throw new \Exception($changefreq);
		}
	}
	public function getChangeFreq(){
		return $this->changefreq;
	}
	public function setLastModified($time){
		if(is_string($time)){
			$time = strtotime($time);
		}
		if(is_numeric($time)){
			if($time <= date::time()){
				$this->lastmodified = $time;
			}else{
				throw new \Exception($time);
			}
		}else{
			throw new \Exception($time);
		}
	}
	public function getLastModified(){
		return $this->lastmodified ? date('c', $this->lastmodified) : null;
	}
	public function setPriority($priority){
		if($priority > 0 and $priority <= 1){
			$this->priority = $priority;
		}else{
			throw new \Exception($priority);
		}
	}
	public function getPriority(){
		return $this->priority;
	}
}

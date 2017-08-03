<?php
namespace packages\sitemap\events;
use \packages\base\event;
use \packages\sitemap\item;
use \packages\sitemap\FileException;
class sitemap extends event{
	private $files = array();
	private $items = array();
	public function fromFile($file){
		if(!is_file($file) or !is_readable($file)){
			throw new FileException();
		}
		$this->files[] = $file;
	}
	public function add(item $item){
		$this->items[] = $item;
	}
	public function getFiles(){
		return $this->files;
	}
	public function getItems(){
		return $this->items;
	}
}
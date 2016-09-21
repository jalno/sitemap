<?php
namespace packages\sitemap;

use \packages\base\response;
class controller extends \packages\base\controller{
	protected $response;
	function __construct(){
		$this->response = new response();
	}
}

<?php
namespace packages\sitemap;

use \packages\base\Response;
class Controller extends \packages\base\Controller{
	protected $response;
	function __construct(){
		$this->response = new Response();
	}
}

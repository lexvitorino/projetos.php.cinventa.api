<?php

namespace Controllers;

use \Core\Controller;
use DateTime;

class HomeController extends Controller
{
	public function index()
	{
		$data = array(
			"statusCode" => 200,
			"statusMessage" => "Service in start!",
			"date" => new DateTime(),
		);

		echo $this->toJson($data);
	}
}

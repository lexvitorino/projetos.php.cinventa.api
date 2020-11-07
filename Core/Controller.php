<?php

namespace Core;

use \Models\Session;

class Controller
{
	public $dataToken = array();

	public function method()
	{
		return $_SERVER['REQUEST_METHOD'];
	}

	public function isPost(): bool
	{
		return $this->method() == 'POST';
	}

	public function isGet(): bool
	{
		return $this->method() == 'GET';
	}

	public function isPut(): bool
	{
		return $this->method() == 'PUT';
	}

	public function isDelete(): bool
	{
		return $this->method() == 'DELETE';
	}

	public function isLogged(): bool
	{
		if (!isset(getallheaders()['Authorization']) && !isset(getallheaders()['authorization'])) {
			return false;
		}

		$token = "";
		if (isset(getallheaders()['Authorization'])) {
			$token = getallheaders()['Authorization'];
			if (empty($token)) {
				return false;
			}
		} else {
			if (isset(getallheaders()['authorization'])) {
				$token = getallheaders()['authorization'];
				if (empty($token)) {
					return false;
				}
			}
		}

		$this->dataToken = Session::validateJwt($token);
		if (empty($this->dataToken)) {
			return false;
		}
		return true;
	}

	public function data()
	{
		switch ($this->method()) {
			case 'GET':
				return $_GET;
				break;
			case 'PUT':
			case 'POST':
			case 'DELETE':
				$data = json_decode(file_get_contents('php://input'));
				if (is_null($data)) {
					$data = $_POST;
				}
				return (array) $data;
				break;
			default:
				break;
		}
	}

	public function toJson($array)
	{
		echo json_encode($array);
		exit;
	}

	private function utf8_converter($array)
	{
		array_walk_recursive($array, function (&$item, $key) {
			if (!mb_detect_encoding($item, 'utf-8', true)) {
				$item = utf8_encode($item);
			}
		});

		return $array;
	}


	public function converter($arrayJson)
	{
		$arrayJson = $this->utf8_converter($arrayJson);
		$var = json_encode($arrayJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		return $var;
	}
}

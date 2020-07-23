<?php
require 'environment.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

date_default_timezone_set('America/Sao_Paulo');

$config = array();
if (ENVIRONMENT == 'development') {
	define("BASE_URL", "http://localhost/projetos/cinventa/api");
	$config['dbname'] = 'cinventa';
	$config['host'] = 'localhost';
	$config['dbuser'] = 'root';
	$config['dbpass'] = '';
	$config['jwt_secret_key'] = "abC123!";
	$config['jwt_valid_per'] = 7;
} else {
	define("BASE_URL", "http://meusite.com.br");
	$config['dbname'] = 'crudoo';
	$config['host'] = 'localhost';
	$config['dbuser'] = 'root';
	$config['dbpass'] = 'root';
	$config['jwt_secret_key'] = "abC123!";
	$config['jwt_valid_per'] = 7;
}

global $db;
try {
	$db = new PDO("mysql:dbname=" . $config['dbname'] . ";host=" . $config['host'], $config['dbuser'], $config['dbpass']);
} catch (PDOException $e) {
	echo "ERRO: " . $e->getMessage();
	exit;
}

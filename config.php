<?php
require 'environment.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-type: text/html; charset=utf-8");

date_default_timezone_set('America/Sao_Paulo');

// CONFIGRAÇÕES DO DATABASE #################
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
	$db = new PDO(
		"mysql:dbname=" . $config['dbname'] . ";host=" . $config['host'],
		$config['dbuser'],
		$config['dbpass'],
		array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
	);
} catch (PDOException $e) {
	echo "ERRO: " . $e->getMessage();
	exit;
}

// CONFIGRAÇÕES DO EMAIL ####################
define('EMAIL_CHARSET', 'UTF-8');
define('EMAIL_HOST', 'mail.mi7dev.com.br');
define('EMAIL_SMTP_AUTH', true);
define('EMAIL_USERNAME', 'contato@mi7dev.com.br');
define('EMAIL_PASSWORD', 'EG3VlX0fD7qD');
define('EMAIL_SMTP_SECURE', 'ssl');
define('EMAIL_PORT', 587);
define('EMAIL_FROM', 'contato@mi7dev.com.br');
define('EMAIL_FROM_NAME', 'Contato');


// SESSAO PARA REQUISICAO
define('SESSION_CONTROL_REQUEST', 0);
define('SESSION_QTDE', 1);
define('SESSION_SECOND', 60);

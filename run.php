#!/usr/bin/php
<?php
require_once('wildflyData.php');
require_once('excelReport.php');
require_once('config.php');
global $esbServers;
$excel = new excelReport();
$excel->createSheet('Модули');
$moduleSheet = 0;
$excel->createHeader(array('Сервер'=>array('A','B'),'Модули'=>array('C','D','F')));
$excel->createSheet('Datasources');
$datasourceSheet = 1;
$excel->createHeader(array('Сервер'=>array('A','B'),'База'=>array('C','D'),'URL'=>array('E','F','G','H'),'Драйвер'=>array('I','J'),'Логин'=>array('K','L','M'),'Пароль'=>array('N','O','P')));
$excel->createSheet('JMS');
$jmsSheet=2;
$excel->createHeader(array('Сервер'=>array('A','B'),'Очередь'=>array('C','D','E'),'JNDI'=>array('F','G','H')));
foreach($esbServers as $esbServer=>$properties)
{
	$login = DEFAULT_LOGIN;
	$password = DEFAULT_PASSWORD;
	if(array_key_exists('login', $properties) && !empty($properties['login']))
	{
		$login = $properties['login'];

	}
	if(array_key_exists('password', $properties) && !empty($properties['password']))
	{
		$password = $properties['password'];
	}
	$data = new wildflyData($esbServer,$login,$password);
	#print_r($data->result);
	$excel->createReport($data->result,$moduleSheet,$datasourceSheet,$jmsSheet);
}
$excel->saveReport();



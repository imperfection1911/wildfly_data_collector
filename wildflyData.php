<?php

class wildflyData
{
	public function __construct($server,$login,$password)
	{
		$this->url = 'http://'.$server.':9990/management';
		$this->login = $login;
		$this->password = $password;
		$deployments = $this->getDeployments();
		$datasources=$this->getJDBCdatasources();
		$jms = $this->getJMSqueues();
		$this->result = array($server =>array(
		'modules'=>$deployments,
		'datasources'=>$datasources,
		'jms'=>$jms));

	}



//запрос к апи wildfly
	private function wildflyRequest($request)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($request));
		curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
		curl_setopt($ch,CURLOPT_USERPWD,$this->login.":".$this->password);
		curl_setopt($ch,CURLOPT_HTTPAUTH,CURLAUTH_DIGEST);
		try{
			if(!$response = curl_exec($ch))
			{
				throw new Exception("ошибка при запросе к wildfly ".curl_error($ch));
			}
			$info = curl_getinfo($ch);
			if($info['http_code']!=200)
			{
				throw new Exception("ошибка при запросе к wildfly. Код ответа ".$info['http_code']);
			}
		curl_close($ch);
		$response = json_decode($response, true);
		return $response;
		}
		catch(Exception $e)
		{
			echo $e->getMessage(),PHP_EOL;
		}



	}

//запрос списка задеплоеных приложений
	private function getDeployments()
	{
		$request = array(
			'operation'=>'read-resource',
			'address'=>array('deployment'),
			'json.pretty'=>1);
		$response = $this->wildflyRequest($request);
		$deployments= array();
		foreach($response['result']['deployment'] as $key=>$value)
		{
			$enabled = $this->getApplicationStatus($key);
			$deployments[$key] = array('enabled'=>$enabled);
		}
		return $deployments;
	}

	private function getApplicationStatus($application)
	{
		$request = array(
			'operation'=>'read-resource',
			'address'=>array('deployment',$application),
			'json.pretty'=>1);
		$response = $this->wildflyRequest($request);
		return $response['result']['enabled'];
	}


//получить датасурсы
	private function getJDBCdatasources()
	{
		$request = array(
			'operation'=>'read-resource',
			'recursive'=>'true',
			'address'=>array('subsystem','datasources'),
			'json.pretty'=>1);
		$response = $this->wildflyRequest($request);
		$datasources = array();
		foreach($response['result']['data-source'] as $key=>$value)
		{
			$datasources[$key]=array(
				'url'=>$value['connection-url'],
				'enabled'=>$value['enabled'],
				'driver'=>$value['driver-name'],
				'login'=>$value['user-name'],
				'password'=>$value['password']
				);

		}
		return $datasources;

	}



	private function getJMSqueues()
	{
		$request = array(
			'operation'=>'read-resource',
			'recursive'=>'true',
			'address'=>array('subsystem','messaging','hornetq-server','default','jms-queue'),
			'json.pretty'=>1);
		$response = $this->wildflyRequest($request);
		$jms = array();
		foreach($response['result']['jms-queue'] as $key=>$value)
		{
			$jms[$key] = array(
				'jndiName'=>$value['entries'],
				'durable'=>$value['durable']
				);


		}
		return $jms;
	}



}
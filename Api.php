<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;		
define('BASE_DIR', __DIR__);

class Api {
	
	protected static $_client;
	protected static $_config;
	protected static $_authConfig;

	public static function config() {
		if (!self::$_config) {
			self::$_config = json_decode(
					file_get_contents(BASE_DIR . '/conf/config.json'), 1
				);
		}
		return self::$_config;
	}

	public static function setAuth(&$params) {
		if (!$params) {
			$params = [];
		}

		$config = self::config();

		if (isset($config['authorization']) && $config['authorization']) {

			switch ($config['authorization']) {

				case 'basic':
						$credentials = base64_encode(
							$config['api_key'] 
							. ':' 
							. $config['api_secret']
						);



						$params['headers'] = [
								"Authorization" => "Basic " . $credentials
							]
						;
					break;

				case 'digest':
						self::$_authConfig = [
							$config['api_key'], 
							$config['api_secret'], 
							'digest'
						];
					break;


				case 'defualt':
						$params['auth'] = [
							$config['api_key'], 
							$config['api_secret']
						];
					break;
				

			}
		}
	}

	public static function client() {
		$config = self::config();

		if (!self::$_client) {
			self::$_client = new Client([// Base URI is used with relative requests
			    'base_uri' => $config['api_end_point'],
			    // You can set any number of default request options.
			    'timeout'  => 5.0
			   ]);	
		}

		return self::$_client;
	}



	public static function request($method = 'GET', $endpoint, $params = null) {

		$query = [
				'query'	=> $params
			];

		self::setAuth($query);

		try {

			$response = self::client()->request($method, $endpoint, $query);

			return self::parseResponse($response);

		} catch (RequestException $e) {

		    echo Psr7\str($e->getRequest());
		    
		    if ($e->hasResponse()) {
		        echo Psr7\str($e->getResponse());
		    }
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	public static function parseResponse($response) {

		$code = $response->getStatusCode(); // 200
		$reason = $response->getReasonPhrase(); // OK
		
		if ($code == 200) {
			$body = $response->getBody();
			$json = $body->getContents();
		
			return json_decode($json);
		} else {
			throw new Exception($reason);				
		}

	}
}

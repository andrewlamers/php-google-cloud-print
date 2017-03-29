<?php
/**
 * Created by PhpStorm.
 * User: andrewlamers
 * Date: 2/20/17
 * Time: 4:43 PM
 */

namespace Andrewlamers\PhpGoogleCloudPrint;

use Andrewlamers\PhpGoogleCloudPrint\Exceptions\Exception;
use Andrewlamers\PhpGoogleCloudPrint\Exceptions\InvalidCredentialsException;
use Andrewlamers\PhpGoogleCloudPrint\Exceptions\MissingConfigValue;
use Andrewlamers\PhpGoogleCloudPrint\Storage\StorageInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Post\PostBody;

class CloudPrintApi
{
	private $config;
	private $client;
	private $authorization_code;
	private $access_token;
	private $refresh_token;
	private $callback_uri;
	private $configDefaults = [
		'access_type' => 'offline',
	    'approval_prompt' => 'force',
	    'scopes' => [
	    	"https://www.googleapis.com/auth/cloudprint"
	    ],
	    'api_urls' => [
	    	'submit' => 'https://www.google.com/cloudprint/submit',
	        'list' => 'https://www.google.com/cloudprint/list',
	        'search' => 'https://www.google.com/cloudprint/search',
	        'processinvite' => 'https://www.google.com/cloudprint/processinvite',
	        'printers' => 'https://www.google.com/cloudprint/printers',
	        'printer' => 'https://www.google.com/cloudprint/printer',
	        'jobs' => 'https://www.google.com/cloudprint/jobs',
	        'deletejob' => 'https://www.google.com/cloudprint/deletejob'
	    ]
	];
	private $baseHttpClient;
	private $response;

	public function __construct($config = []) {

		$config = array_merge($this->configDefaults, $config);
		$this->storage = new Storage\Storage();

		if(!array_key_exists('file', $config)) {
			throw new MissingConfigValue('Must specify a config file for google client.');
		}

		if(array_key_exists('storageEngine', $config)) {
			if($config['storageEngine'] instanceof StorageInterface) {
				$this->storage = $config['storageEngine'];
			} else {
				throw new Exception('Storage engine must implement Storage\StorageInterface');
			}
		}

		$this->config = $config;
		$this->client = new \Google_Client();
		$this->client->setAuthConfig($config['file']);

		foreach($config['scopes'] as $scope) {
			$this->client->addScope($scope);
		}

		if(isset($config['redirect_uri']))
			$this->client->setRedirectUri($config['redirect_uri']);

		if(isset($config['access_type']))
			$this->client->setAccessType($config['access_type']);

		if(isset($config['approval_prompt']))
			$this->client->setApprovalPrompt($config['approval_prompt']);

		if(isset($config['callback_uri']))
			$this->callback_uri = $config['callback_uri'];

	}

	public function getUrl($key) {
		if(isset($this->config['api_urls'][$key]))
			return $this->config['api_urls'][$key];

		return $key;
	}

	public function authorize() {
		header('Location: '.filter_var($this->getAuthUrl(), FILTER_SANITIZE_URL));
	}

	public function authenticate() {
		$this->authorization_code = $_GET['code'];
		$this->client->fetchAccessTokenWithAssertion();
		$this->refresh_token = $this->client->getRefreshToken();
		$this->storage->saveRefreshToken($this->refresh_token);
	}

	public function getAuthUrl() {
		return $this->client->createAuthUrl();
	}

	public function getRefreshToken() {
		return $this->storage->getRefreshToken();
	}

	public function getAccessToken() {

		if($this->client->isAccessTokenExpired()) {
			$this->client->fetchAccessTokenWithAssertion();
		}

		if ( ! ($accessToken = $this->client->getAccessToken())) {
			throw new InvalidCredentialsException();
		}

		$this->access_token = $accessToken['access_token'];

		return $this->access_token;
	}

	public function getCallbackUri() {
		return $this->callback_uri;
	}

	public function authHandler() {

		if(!isset($_GET['code'])) {
			$this->authorize();
		}
		else if(isset($_GET['error'])) {
			throw new Exception('Error from authorize: '.$_GET['error']);
		}
		else {
			$this->authenticate();
			header("Location: ".$this->getCallbackUri());
		}

	}

	public function getBaseHttpClient() {
		$this->baseHttpClient = new Client(
			[
				'headers' => [
					'Authorization' => 'Bearer '.$this->getAccessToken()
				]
			]
		);

		return $this->baseHttpClient;
	}

	protected function getResponse() {
		return json_decode($this->response->getBody());
	}

	public function post($service, $data = [], $headers = []) {
		$client = $this->getBaseHttpClient();
		$url = $this->getUrl($service);
		//$headers['Content-Type'] = 'multipart/form-data';
		//$body = new PostBody();
		//$body->forceMultipartUpload(true);
		//$body->replaceFields($data);

		$multipart = [];
		foreach($data as $k => $v) {
			$multipart[] = [
				'name' => $k,
			    'contents' => $v
			];
		}

		$this->response = $client->request('POST', $url, ['multipart' => $multipart, 'headers' => $headers]);

		//$this->response = $client->send($this->request);
		return $this->response;
	}

	public function search($query = '', $data = []) {
		$data['q'] = $query;
		$this->response = $this->post('search', $data);
		return $this->getResponse();
	}

	public function printers($proxy, $extra_fields = '') {
		$this->response = $this->post('list', ['proxy' => $proxy, 'extra_fields' => $extra_fields]);
		return $this->getResponse();
	}

	public function printer($printerid, $client = '', $extra_fields = '') {
		$this->response = $this->post('printer', [
			'printerid' => $printerid,
			'extra_fields' => $extra_fields,
			'client' => $client
		]);
		return $this->getResponse();
	}

	public function jobs($params = []) {
		$this->post('jobs', $params);
		return $this->getResponse();
	}

	public function deleteJob($jobid) {
		$this->post('deletejob', ['jobid' => $jobid]);
		return $this->getResponse();
	}

	public function submit($printerid, $title, $content, $params = []) {
		$params['printerid'] = $printerid;
		$params['title'] = $title;
		$params['content'] = $content;
		$params['ticket'] = json_encode($params['ticket']);

		$this->post('submit', $params);
		return $this->getResponse();
	}

	public function processInvite($printerid, $accept = 'true') {
		$payload = [
			'printerid' => $printerid,
			'accept' => $accept
		];
		$this->post('processinvite', $payload);

		return $this->getResponse();
	}
}
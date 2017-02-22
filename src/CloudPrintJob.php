<?php
/**
 * Created by PhpStorm.
 * User: andrewlamers
 * Date: 2/22/17
 * Time: 12:23 PM
 */

namespace Andrewlamers\PhpGoogleCloudPrint;


use Andrewlamers\PhpGoogleCloudPrint\Exceptions\Exception;
use Andrewlamers\PhpGoogleCloudPrint\Exceptions\FileNotFoundException;

class CloudPrintJob
{
	protected $content;
	protected $contentType;
	protected $printerid;
	protected $ticket;
	protected $tags = [];
	protected $title;
	protected $api;

	public function __construct($contentType = false, $printerid = false, $config = []) {

		if($contentType)
			$this->contentType = $contentType;

		if($printerid)
			$this->setPrinterId($printerid);

		$this->api = new CloudPrintApi($config);

		return $this;
	}

	protected function setContentType($contentType) {
		$this->contentType = $contentType;
		return $this;
	}

	protected function setPrinterId($printerid) {
		$this->printerid = $printerid;
		return $this;
	}

	public function getTitle() {
		if($this->title)
			return $this->title;

		return 'print-job-'.date('YmdHis');
	}

	public function printer($printerid) {
		$this->printerid = $printerid;

		return $this;
	}

	public function getPrinterId() {
		return $this->printerid;
	}

	public function getContent() {
		return $this->content;
	}

	public function getContentType() {
		return $this->contentType;
	}

	public function getTags() {
		return join(',', $this->tags);
	}

	public function getTicket() {
		return $this->formatTicket();
	}

	public function content($data) {
		$this->content = $data;
		return $this;
	}

	public function url($url) {
		$this->setContentType('url');
		$this->content($url);

		return $this;
	}

	public function file($filePath) {
		if(!file_exists($filePath)) {
			throw new FileNotFoundException('File doesn\'t exist.');
		} else {
			$fileData = file_get_contents($filePath);
			$this->content($fileData);
		}

		return $this;
	}

	public function ticket($key, $value) {
		$this->ticket[$key] = $value;
		return $this;
	}

	public function title($title) {
		$this->title = $title;
		return $this;
	}

	public function tag($tag) {
		$this->tags[] = $tag;
		return $this;
	}

	public function marginInMillimeters($top, $left = false, $bottom = false, $right = false) {
		return $this->marginInMicrons($top, $left, $bottom, $right, 1000);
	}

	public function marginInMicrons($top, $left = false, $bottom = false, $right = false, $multiplier = 1) {

		if(!$left) {
			$left = $top;
			$bottom = $top;
			$right = $top;
		}

		$this->ticket('margins', [
			'top_microns' => $top * $multiplier,
		    'left_microns' => $left * $multiplier,
		    'bottom_microns' => $bottom * $multiplier,
		    'right_microns' => $right * $multiplier
		]);

		return $this;
	}

	public function layout($layout) {
		$this->ticket('page_orientation', ['type' => $layout]);
		return $this;
	}

	public function mediaSizeInches($width, $height) {
		$inchToMicron = 0.000039370;
		$this->mediaSizeMicrons(floor($width/$inchToMicron), floor($height/$inchToMicron));

		return $this;
	}

	public function mediaSizeMicrons($width, $height) {
		$this->ticket('media_size', [
			'width_microns' => $width,
		    'height_microns' => $height
		]);

		return $this;
	}

	protected function formatTicket() {
		return [
			'version' => '1.0',
		    'print' => $this->ticket
		];
	}

	protected function formatPostData() {
		$postData = [
		    'contentType' => $this->getContentType(),
		    'ticket' => json_encode($this->getTicket()),
		    'tag' => $this->getTags()
		];

		return $postData;
	}

	public function send() {
		$response = $this->api->submit($this->getPrinterId(),
	                    $this->getTitle(),
	                    $this->getContent(),
                        $this->formatPostData());

		if(!$response->success && $response->errorCode == 8) {
			$invite = $this->api->processInvite($this->getPrinterId());

			if($invite->success == true)
				return $this->send();

			throw new Exception('Not authorized to print on this printer.');
		}

		return $response;
	}
}
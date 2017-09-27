<?php
/**
 * Created by PhpStorm.
 * User: andrewlamers
 * Date: 2/20/17
 * Time: 10:16 AM
 */

namespace Andrewlamers\PhpGoogleCloudPrint;

class CloudPrint extends CloudPrintApi
{
	protected $config;

	public function __construct($config = []) {
		$this->config = $config;
		parent::__construct($config);

		return $this;
	}

	public function html($html) {
		$job = new CloudPrintJob('text/html', false, $this->config);
		$job->content($html);

		return $job;
	}

	public function text() {
		return new CloudPrintJob('text/plain', false, $this->config);
	}

	public function url() {
		return new CloudPrintJob('url', false, $this->config);
	}

	public function job($contentType) {
		return new CloudPrintJob($contentType, false, $this->config);
	}

	public function pdf($pdf) {
		$job = new CloudPrintJob('application/pdf', false, $this->config);
		$job->setContentTransferEncoding('base64');
		$job->content($pdf);

		return $job;
	}
}
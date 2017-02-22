<?php
/**
 * Created by PhpStorm.
 * User: andrewlamers
 * Date: 2/20/17
 * Time: 4:33 PM
 */

namespace Andrewlamers\PhpGoogleCloudPrint\Storage;


class Storage implements StorageInterface
{
	public function __construct() {

	}

	public function saveRefreshToken($data)
	{
		$_SESSION['refresh_token'] = $data;
	}

	public function getRefreshToken()
	{
		return $_SESSION['refresh_token'];
	}

	public function getAccessToken()
	{
		if(isset($_SESSION['access_token']))
			return $_SESSION['access_token'];

		return false;
	}

	public function saveAccessToken($access_token) {
		$_SESSION['access_token'] = $access_token;
	}
}
<?php
/**
 * Created by PhpStorm.
 * User: andrewlamers
 * Date: 2/20/17
 * Time: 4:33 PM
 */

namespace Andrewlamers\PhpGoogleCloudPrint\Storage;


interface StorageInterface
{
	function saveRefreshToken($refresh_token);
	function getRefreshToken();
	function saveAccessToken($access_token);
	function getAccessToken();
}
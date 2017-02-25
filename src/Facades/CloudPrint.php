<?php
namespace Andrewlamers\PhpGoogleCloudPrint\Facades;

class CloudPrint extends \Illuminate\Support\Facades\Facade
{
	/**
	 * {@inheritDoc}
	 */
	protected static function getFacadeAccessor()
	{
		return 'print';
	}
}

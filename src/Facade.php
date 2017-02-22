<?php
namespace Andrewlamers\PhpGoogleCloudPrint;

class Facade extends \Illuminate\Support\Facades\Facade
{
	/**
	 * {@inheritDoc}
	 */
	protected static function getFacadeAccessor()
	{
		return 'print';
	}
}

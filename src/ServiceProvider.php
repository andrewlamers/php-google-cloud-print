<?php
/**
 * Created by PhpStorm.
 * User: andrewlamers
 * Date: 2/21/17
 * Time: 11:46 AM
 */

namespace Andrewlamers\PhpGoogleCloudPrint;


class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
	protected $defer = true;

	public function register() {
		$configPath = __DIR__ . '/config/google-cloud-print.php';
		$this->mergeConfigFrom($configPath, 'google-cloud-print');

		$this->publishes([$configPath => config_path('google-cloud-print.php')]);

		$this->app->singleton('print', function ($app) {
			$cloudPrint = new CloudPrint($app['config']->get('google-cloud-print'));
			return $cloudPrint;
		});

		$this->app->alias('print', 'Andrewlamers\PhpGoogleCloudPrint\PhpGoogleCloudPrint');
	}

	/**
	 * Publish the config file
	 *
	 * @param  string $configPath
	 */
	protected function publishConfig($configPath)
	{
		$this->publishes([$configPath => config_path('google-cloud-print.php')], 'config');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['print'];
	}
}
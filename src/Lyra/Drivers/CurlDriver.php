<?php namespace Lyra\Drivers;

use Lyra\Contracts\DriverInterface;

class CurlDriver implements DriverInterface
{

	private $settings;
	private $handler;

	public function __construct(\Lyra\Collection\Collection $settings)
	{
		$this->settings = $settings;
	}

	public function prepareRequest($url, $method = 'GET', array $data = array())
	{

	}

	public function send()
	{
		
	}
}
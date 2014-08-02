<?php namespace Lyra\Drivers;

use Lyra\Response;
use Lyra\Contracts\DriverInterface;

class CurlDriver implements DriverInterface
{
	/**
	 * Settings Store
	 * 
	 * @var \Lyra\Collection\Collection
	 */
	private $settings;

	/**
	 * cUrl Handler
	 * 
	 * @var Resource (cURL handler)
	 */
	private $handler;

	/**
	 * Request method.
	 * 
	 * @var string
	 */
	private $requestMethod = "GET";

	/**
	 * Stores request data
	 * 
	 * @var array
	 */
	private $requestData = array();

	/**
	 * Destination URL
	 * 
	 * @var string
	 */
	private $url;

	/**
	 * Creates an instance of the driver
	 * 
	 * @param \Lyra\Collection\Collection $settings
	 * @return void
	 */
	public function __construct(\Lyra\Collection\Collection $settings)
	{
		$this->settings = $settings;
	}

	/**
	 * Checks whether the system supports
	 * this driver in particular. If this 
	 * function returns false an exception will
	 * be thrown by the system
	 * 
	 * @return bool
	 */
	public function isSupported()
	{
		return function_exists('curl_version');
	}

	/**
	 * Prepare whatever in-memory objects that
	 * you might need in order for the communicator to work.
	 * 
	 * @param string $url
	 * @param string $method
	 * @param array $data
	 * @return void
	 */
	public function prepareRequest($url, $method = 'GET', array $data = array())
	{
		$allowedMethods = $this->getAllowedRequestMethods();

		$this->handler = curl_init();
		$this->url = $url;
		
		if ( !in_array($method, $allowedMethods) )
		{
			throw new \InvalidArgumentException("Invalid method '{$method}'");
		}

		$this->requestMethod = $method;
		$this->requestData = $data;

		curl_setopt($this->handler, CURLOPT_HEADER, TRUE);
		curl_setopt($this->handler, CURLOPT_RETURNTRANSFER, TRUE);

		if ( $this->settings->get('allow_redirects') )
		{
			curl_setopt($this->handler, CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($this->handler, CURLOPT_MAXREDIRS, 16);
		}

		if ( $timeout = $this->settings->get('timeout') )
		{
			curl_setopt($this->handler, CURLOPT_TIMEOUT, $timeout);
		}

		if ( $userAgent = $this->settings->get('user_agent') )
		{
			curl_setopt($this->handler, CURLOPT_USERAGENT, $userAgent);
		}

		if ( $headerList = $this->settings->get('headers') )
		{
			$headers = $this->parseHeaders($headerList);
			curl_setopt($this->handler, CURLOPT_HTTPHEADER, $headers);
		}

		if ( $method == 'GET' )
		{
			
		}
	}

	/**
	 * Sends the request to the prepared server
	 * and retrieves and parses the response data.
	 * 
	 * @return \Lyra\Response
	 */
	public function send()
	{
		$httpResponse = curl_exec( $this->handler );
		curl_close( $this->handler );


	}

	/**
	 * Return the raw driver handler
	 * 
	 * @return Resource
	 */
	public function getDriverRaw()
	{
		return $this->handler;
	}

	/**
	 * Retrieves a list of allowed request methods
	 * 
	 * @return array
	 */
	private function getAllowedRequestMethods()
	{
		return array("GET", "POST", "PUT", "DELETE");
	}

	/**
	 * Parses the header array into cURL type headers
	 * 
	 * @param array $headers
	 * @return array
	 */
	private function parseHeaders(array $headers)
	{
		if ( count($headers) < 1 ) { return $headers; }

		$curlHeaders = array();
		foreach($headers as $key => $value)
		{
			// Check if the key is an actual header key
			if ( is_numeric($key) )
			{
				$curlHeaders[] = $value;
			}
			else
			{
				$curlHeaders[] = trim($key) . ': ' . trim($value);
			}
		}

		return $curlHeaders;
	}
}
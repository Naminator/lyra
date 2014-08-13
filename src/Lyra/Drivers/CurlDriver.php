<?php namespace Lyra\Drivers;

use Lyra\Response;
use Lyra\Contracts\DriverInterface;

class CurlDriver implements DriverInterface
{

	/**
	 * The Driver's Name
	 * 
	 * @var string
	 */
	protected $driverName = "cURL Driver";

	/**
	 * The Driver's Version
	 * 
	 * @var string
	 */
	const VERSION = "1.0.0";

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
	 * @throws \Lyra\Exceptions\UnsupportedHTTPMethodException
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

		if ( $forbidCache = $this->settings->get('forbid_cache') )
		{
			curl_setopt($this->handler, CURLOPT_FORBID_REUSE, TRUE); 
			curl_setopt($this->handler, CURLOPT_FRESH_CONNECT, TRUE);
		}
		else
		{
			curl_setopt($this->handler, CURLOPT_FORBID_REUSE, FALSE); 
			curl_setopt($this->handler, CURLOPT_FRESH_CONNECT, FALSE);
		}

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

		switch($method)
		{
			case 'GET':
				// Check for request data
				if ( !is_null($this->requestData) )
				{
					$queryString = http_build_query( $this->requestData );
					$glue = strpos($this->url, "?") !== false ? '&' : '?';

					$this->url .= $glue . $queryString;
				}

				curl_setopt_array($this->handler, array(
					CURLOPT_URL			=> $this->url,
					CURLOPT_HTTPGET		=> TRUE
				));
				break;
			case 'POST':
				curl_setopt_array($this->handler, array(
					CURLOPT_URL			=> $this->url,
					CURLOPT_POST		=> TRUE,
					CURLOPT_POSTFIELDS	=> $this->requestData
				));
				break;
			case 'PUT':
			case 'DELETE':
				curl_setopt_array($this->handler, array(
					CURLOPT_URL 		=> $this->url,
					CURLOPT_CUSTOMREQUEST => $method,
				));
				break;
			default:
				throw new \Lyra\Exceptions\UnsupportedHTTPMethodException("Lyra's cURL driver does not support the '{$method}'' HTTP method.");
				break;
		}

		return $this;
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
		$curlInfo = curl_getinfo( $this->handler );
		curl_close( $this->handler );

		return new Response($this->url, $httpResponse, $curlInfo);
	}

	/**
	 * Checks whether the given method is supported
	 * by our driver or not
	 * 
	 * @param string $method HTTP Method
	 * @return bool
	 */
	public function isMethodSupported($method = 'GET')
	{
		return in_array($method, $this->getAllowedRequestMethods());
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
	 * Retrieves the driver's name in 
	 * a string form
	 * 
	 * @return string
	 */
	public function getDriverName()
	{
		return $this->name;
	}

	/**
	 * Returns the version string
	 * 
	 * @return string
	 */
	public function getDriverVersion()
	{
		return static::VERSION;
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
	 * Parses the header array into cURL type headers.
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

		// Exclude the content type header on POST
		if ( $this->requestMethod == "POST" )
		{
			foreach($curlHeaders as $index => $header)
			{
				if ( strpos( strtolower($header), 'content-type') !== FALSE )
				{
					unset($curlHeaders[$index]);
				}
			}

			reset($curlHeaders);
		}
		
		return $curlHeaders;
	}
}
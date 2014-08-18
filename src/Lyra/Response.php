<?php namespace Lyra;

use Lyra\Collection\Collection;
use Lyra\Contracts\ArrayableInterface;
use Lyra\Contracts\JsonableInterface;

class Response implements ArrayableInterface, JsonableInterface
{
	/**
	 * List of response headers
	 * 
	 * @var \Lyra\Collection\Collection
	 */
	protected $headers;

	/**
	 * List of request settings
	 * 
	 * @var \Lyra\Collection\Collection
	 */
	protected $info;

	/**
	 * The time this request took
	 * 
	 * @var float
	 */
	protected $queryTime = 0;

	/**
	 * The response parse time
	 * 
	 * @var float
	 */
	protected $responseParseTime = 0;

	/**
	 * Response's status code
	 * 
	 * @var int
	 */
	protected $statusCode = 0;

	/**
	 * The message body, without header information
	 * 
	 * @var string
	 */
	protected $body;

	/**
	 * Creates an instance of the response object
	 * and parses all the data
	 * 
	 * @param string $url
	 * @param string $httpData
	 * @param float $queryTime
	 * @return void
	 */
	public function __construct($url, $httpData, array $requestInfo = array())
	{
		$this->parseRequestInfo($requestInfo);

		$start = microtime(TRUE);
		$this->parseHeaders($httpData);
		$this->responseParseTime = microtime(TRUE) - $start;

		$this->parseBody($httpData);

		$this->statusCode = $this->getInfo('http_code');
	}

	/**
	 * Parses and creates the header collection
	 * 
	 * @param string $httpData
	 * @return void
	 */
	private function parseHeaders($httpData)
	{
		$this->headers = new Collection( http_parse_headers($httpData) );
	}

	/**
	 * Creates the info collection
	 * 
	 * @param array $requestInfo
	 * @return void
	 */
	private function parseRequestInfo(array $requestInfo = array())
	{
		$this->info = new Collection($requestInfo);
	}

	/**
	 * Functionality thanks to Enyby from StackOverflow
	 * http://stackoverflow.com/a/17971689
	 * 
	 * @param string $httpData
	 * @return void
	 */
	private function parseBody($httpData)
	{
		$parts = explode("\r\n\r\nHTTP/", $httpData);
		$parts = (count($parts) > 1 ? 'HTTP/' : '') . array_pop($parts);
		list($headers, $body) = explode("\r\n\r\n", $parts, 2);

		$this->body = $body;
	}

	/**
	 * Returns an array of setting defaults.
	 * All required settings will have their defaults.
	 * 
	 * @return array
	 */
	private function getInfoDefaults()
	{
		return array(
			'http_code'		=> 200,
			'total_time'	=> 0,
		);
	}

	/**
	 * Retrieves request information. 
	 * 
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	private function getInfo($key, $default = NULL)
	{
		if ( is_null($default) )
		{
			$default = $this->getInfoDefaults()['http_code'];
		}

		return $this->info->get($key, $default);
	}

	/**
	 * Returns the response's HTTP code
	 * 
	 * @return int
	 */
	public function getCode()
	{
		return (int)$this->statusCode;
	}

	/**
	 * Returns an array of response headers
	 * 
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->headers->toArray();
	}

	/**
	 * Returns the response's body
	 * 
	 * @param mixed $default
	 * @return mixed;
	 */
	public function getBody($default = NULL)
	{
		return is_null($this->body) ? $default : $this->body;
	}

	/**
	* Retrieves instance as an array.
	* 
	* @return array
	*/
	public function toArray()
	{
		return array();
	}

	/**
	* Retrieves the current object instance as a JSON string
	* 
	* @return string
	*/
	public function toJson($options = 0)
	{
		return '[]';
	}
}
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
	 * Creates an instance of the response object
	 * and parses all the data
	 * 
	 * @param string $url
	 * @param string $httpData
	 * @param float $queryTime
	 * @return void
	 */
	public function __construct($url, $httpData, $queryTime = 0)
	{
		$start = microtime(TRUE);
		$this->parseHeaders($httpData);
		$this->responseParseTime = microtime(TRUE) - $start;
		$this->queryTime = $queryTime;

		$this->parseBody($httpData);
	}

	private function parseHeaders($httpData)
	{
		print_r($httpData); exit;
	}

	public function toArray()
	{
		return array();
	}

	public function toJson($options = 0)
	{
		return '[]';
	}
}
<?php namespace Lyra\Contracts;

interface DriverInterface {

	/**
	 * Properly initialize the object
	 * 
	 * @return void
	 */
	public function __construct(\Lyra\Collection\Collection $settings);

	/**
	 * Prepare whatever in-memory objects that
	 * you might need in order for the communicator to work.
	 * 
	 * @param string $url
	 * @param string $method
	 * @param array $data
	 * @return void
	 */
	public function prepareRequest($url, $method = 'GET', array $data = array());

	/**
	 * Sends the request to the prepared server
	 * and retrieves and parses the response data.
	 * 
	 * @return \Lyra\Response
	 */
	public function send();
}
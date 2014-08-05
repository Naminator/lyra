<?php namespace Lyra\Contracts;

interface DriverInterface {

	/**
	 * Properly initialize the object
	 * 
	 * @return void
	 */
	public function __construct(\Lyra\Collection\Collection $settings);

	/**
	 * Checks whether the system supports
	 * this driver in particular. If this 
	 * function returns false an exception will
	 * be thrown by the system
	 * 
	 * @return bool
	 */
	public function isSupported();

	/**
	 * Checks whether the given method is supported
	 * by our driver or not
	 * 
	 * @param string $method HTTP Method
	 * @return bool
	 */
	public function isMethodSupported($method = 'GET');

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

	/**
	 * Return the raw driver handler
	 * 
	 * @return mixed
	 */
	public function getDriverRaw();

	/**
	 * Retrieves the driver's name in 
	 * a string form
	 * 
	 * @return string
	 */
	public function getDriverName();

	/**
	 * Returns the version string
	 * 
	 * @return string
	 */
	public function getDriverVersion();
}
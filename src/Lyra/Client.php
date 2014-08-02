<?php namespace Lyra;

use Lyra\Collection\Collection;
use Lyra\Exceptions\UnsupportedDriverException;

class Client
{

	/**
	 * Client Version
	 * 
	 * @var string
	 */
	const VERSION = "1.0.0";

	/**
	 * The driver we start the app with
	 * 
	 * @var DriverInterface
	 */
	private $driver;

	/**
	 * The URL to send request to
	 * 
	 * @var string
	 */
	private $url;

	/**
	 * Settings store
	 * 
	 * @var \Lyra\Collection\Collection
	 */
	private $settings;

	/**
	 * Create instance of the object
	 * 
	 * @param string $url
	 * @param array $settings
	 * @return void
	 */
	public function __construct($url, array $settings = array())
	{
		if ( !$this->isUrl($url) )
		{
			throw new \InvalidArgumentException("Invalid URL given");
		}

		$this->url = $this->removeTrailingSlash($url);
		$this->parseSettings($settings);

		$driver = strtolower( ucfirst($this->settings['driver']) );
		$driverClassName = 'Lyra\\Drivers\\' . $driver . 'Driver';
		if ( !class_exists($driverClassName) )
		{
			throw new \InvalidArgumentException("Unknown driver '{$driver}'");
		}

		$this->driver = new $driverClassName($this->settings);
		if ( !$this->driver->isSupported() )
		{
			throw new UnsupportedDriverException("Your system doesn't support the '{$driver}' driver.");
		}
	}

	/**
	 * Sends a POST request
	 * 
	 * @throws \InvalidArgumentException
	 * @return Lyra\Response
	 */
	public function post()
	{
		switch( func_num_args() )
		{
			case 1:
				return $this->send("POST", NULL, func_get_arg(0));
				break;
			case 2:
				list($subRoute, $data) = func_get_args();
				return $this->send("POST", $subRoute, $data);
				break;
			default:
				throw new \InvalidArgumentException("Bad parameters given for POST method.");
				break;
		}
	}

	/**
	 * Sends a POST request
	 * 
	 * @throws \InvalidArgumentException
	 * @return Lyra\Response
	 */
	public function get()
	{
		switch( func_num_args() )
		{
			case 1:
				return $this->send("GET", NULL, func_get_arg(0));
				break;
			case 2:
				list($subRoute, $data) = func_get_args();
				return $this->send("GET", $subRoute, $data);
				break;
			default:
				throw new \InvalidArgumentException("Bad parameters given for GET method.");
				break;
		}
	}

	/**
	 * Builds and sends a request
	 * 
	 * @param string $method
	 * @param string $subRoute
	 * @param array $data
	 * @return Lyra\Response
	 */
	public function send($method = "GET", $subRoute = NULL, array $data = array())
	{
		if ( !is_null($subRoute) )
		{
			$this->url .= '/' . $subRoute;
		}


		$this->driver->prepareRequest($this->url, $method, $data);
		return $this->driver->send();
	}

	/**
	 * Whether the given URL is an URL or not
	 * 
	 * @param string $url
	 * @return bool
	 */
	private function isUrl($url)
	{
		return (filter_var($url, FILTER_VALIDATE_URL) !== FALSE);
	}

	/**
	 * Removes the trailing slash off of an url
	 * 
	 * @param string $url
	 * @return string
	 */
	private function removeTrailingSlash($url)
	{
		return rtrim($url, '/');
	}

	/**
	 * Parses the settings into class members.
	 * 
	 * @param array $settings
	 * @return void
	 */
	private function parseSettings(array $settings = array())
	{
		$defaultSettings = $this->getDefaultSettings();
		$this->settings = new Collection(array_replace($defaultSettings, $settings));
	}

	protected function getDefaultSettings()
	{
		$settings = array(
			'driver'	=> 'curl',
			'timeout'	=> 10,
			'allow_redirects' => TRUE,
			'user_agent' => static::getDefaultUserAgent(),
			'headers'	=> array());

		return $settings;
	}

	/**
	 * Default User-Agent string
	 * 
	 * @return string
	 */
	public static function getDefaultUserAgent()
	{
		static $userAgent;

		if ( !$userAgent )
		{
			$userAgent = 'Lyra/' . static::VERSION . ' PHP ' . PHP_VERSION;
		}

		return $userAgent;
	}
}

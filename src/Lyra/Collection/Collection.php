<?php namespace Lyra\Collection;

use Closure;
use Countable;
use ArrayAccess;
use ArrayIterator;
use CachingIterator;
use JsonSerializable;
use IteratorAggregate;
use Lyra\Contracts\ArrayableInterface;
use Lyra\Contracts\JsonableInterface;

class Collection implements ArrayAccess, ArrayableInterface, Countable, IteratorAggregate, JsonableInterface, JsonSerializable
{
	/**
	 * Data storage variable
	 * 
	 * @var array
	 */
	protected $items = array();

	/**
	 * Creates a new collection.
	 * 
	 * @return void
	 */
	public function __construct(array $data = array())
	{
		$this->items = $data;
	}

	/**
	 * Retrieves the current collection data
	 * as an array
	 * 
	 * @return array
	 */
	public function all()
	{
		return $this->items;
	}

	/**
	 * Pushes a value at the end of the collection
	 * 
	 * @param mixed $value
	 * @return void
	 */
	public function push($value)
	{
		$this->items[] = $value;
	}

	/**
	 * Prepends an element to the beginning of the 
	 * collection
	 * 
	 * @param mixed $value
	 * @return void
	 */
	public function prepend($value)
	{
		array_unshift($this->items, $value);
	}

	/**
	 * Pushes a value at the end of the collection
	 * with a given key/value
	 * 
	 * @param mixed $key
	 * @param mixed $value
	 * @return void
	 */
	public function put($key, $value)
	{
		$this->items[$key] = $value;
	}

	/**
	 * Checks whether the given key exists in the collection
	 * or not.
	 * 
	 * @param mixed $key
	 * @return bool
	 */
	public function has($key)
	{
		return array_key_exists($key, $this->items);
	}

	/**
	 * Retrieves data from the collection by given key.
	 * If no second param is specified, returns NULL
	 * if no data has been found.
	 * 
	 * @param mixed $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($key, $default = NULL)
	{
		if ( $this->has($key) )
		{
			return $this->items[$key];
		}

		return ( $default instanceof Closure ) ? $default() : $default;
	}

	/**
	 * Determine if an item exists at an offset.
	 *
	 * @param  mixed  $key
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return array_key_exists($key, $this->items);
	}

	/**
	 * Get an item at a given offset.
	 *
	 * @param  mixed  $key
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->items[$key];
	}

	/**
	 * Set the item at a given offset.
	 *
	 * @param  mixed  $key
	 * @param  mixed  $value
	 * @return void
	 */
	public function offsetSet($key, $value)
	{
		if (is_null($key))
		{
			$this->items[] = $value;
		}
		else
		{
			$this->items[$key] = $value;
		}
	}

	/**
	 * Unset the item at a given offset.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function offsetUnset($key)
	{
		unset($this->items[$key]);
	}

	/**
	 * Get an iterator for the items.
	 *
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->items);
	}

	/**
	 * Get the amount of objects currently
	 * in the collection
	 * 
	 * @return int
	 */
	public function count()
	{
		return count($this->items);
	}

	/**
	 * Convert the object into something JSON serializable.
	 *
	 * @return array
	 */
	public function jsonSerialize()
	{
		return $this->toArray();
	}

	/**
	 * Get the collection of items as a plain array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return array_map(function($value)
		{
			return $value instanceof ArrayableInterface ? $value->toArray() : $value;

		}, $this->items);
	}

	public function toJson($options = 0)
	{
		return json_encode($this->toArray(), $options);
	}

	public function __toString()
	{
		return $this->toJson();
	}
}
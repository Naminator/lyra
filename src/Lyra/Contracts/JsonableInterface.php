<?php namespace Lyra\Contracts;

interface JsonableInterface {
	
	/**
	* Retrieves the current object instance as a JSON string
	* 
	* @return string
	*/
	public function toJson($options = 0);
}

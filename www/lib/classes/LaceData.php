<?php

class LaceData 
{
	var $file  = '';	
	var $data  = '';
	var $hash  = '';
	var $_hash = '';
	
	function __construct()
	{
		$this->LaceData();
	}
	
	function LaceData()
	{
		$this->getData();
		register_shutdown_function(array(&$this, 'storeData'));
		
		return true;
	}
	
	function setFile($file)
	{
		$this->file = $file;
		return true;
	}
	
	function getData()
	{
		if (is_array($this->data))
		{
			return true;
		}
		
		$data = file($this->file);
		
		if ($data === false)
		{
			$this->data = array();
		} 
		else 
		{
			$this->prepareDataForRetrieval($data);
		}
		
		$this->_hash = $this->computeHash();
		$this->hash = $this->_hash;
	}
	
	function storeData()
	{
		$data = $this->prepareDataForStorage();		
		file_put_contents($this->file, $data);

		return true;
	}
	
	function prepareDataForRetrieval($data)
	{
		return $data;
	}
	
	function prepareDataForStorage()
	{
		return $this->data;
	}
	
	function computeHash()
	{
		return md5(serialize($this->data));
	}

	function getHash()
	{	
		if ($this->hash)
			return $this->hash;
		
		return false;
	}
	
	function setHash()
	{
		$this->hash = $this->computeHash();		
	}
	
	function keyExists($key)
	{
		$found = (is_array($this->data) && array_key_exists($key, $this->data));
		return $found;
	}
	
	function deleteByKey($key)
	{
		if (array_key_exists($key, $this->data))
		{
			unset($this->data[$key]);
			$this->setHash();
		}
		
		return true;
	}
	
	function add($key, $value)
	{
		if (is_null($key))
		{
			$this->data[] = $value;
		}
		else
		{
			if (is_array($this->data) && array_key_exists($key, $this->data))
			{
				return $this->update($key, $value);
			}
			
			$this->data[$key] = $value;
		}
		
		$this->setHash();
		
		return true;
	}
	
	function update($key, $value)
	{
		if (is_array($this->data) && array_key_exists($key, $this->data))
		{
			$this->data[$key] = $value;
		}
		else
		{
			return $this->add($key, $value);
		}
		
		$this->setHash();
		
		return true;
	}
	
	function getValueByKey($key)
	{	
		if (is_array($this->data) && array_key_exists($key, $this->data))
		{	
			return $this->data[$key];
		}
		
		return false;
	}	
}

?>
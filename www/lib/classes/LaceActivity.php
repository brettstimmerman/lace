<?php

class LaceActivity extends LaceData
{
	var $expiry = '';

	function __construct()
	{
		$this->LaceActivity();
	}

	function LaceActivity()
	{
		$this->setExpiry(time() - (LACE_TIMEOUT * 60));
		$this->setFile(LACE_ACTIVITY_FILE);

		$this->getData();
		register_shutdown_function(array(&$this, 'storeData'));
	}

	function setExpiry($expiry)
	{
		$this->expiry = $expiry;
	}

	function prepareDataForRetrieval($data)
	{
		if (is_array($data))
		{
			foreach ($data as $row)
			{
				list($name, $last_post) = array_map('trim', explode('|', $row));
				$this->add($name, $last_post);
			}

			$this->purgeExpired();
		}
		else
		{
			$this->data = array();
		}
	}

	function prepareDataForStorage()
	{
		$data = '';

		if (is_array($this->data))
		{
			foreach($this->data as $name => $last_post)
			{
				$data .= $name.'|'.$last_post."\n";
			}
		}

		return $data;
	}

	function purgeExpired()
	{
		if (is_array($this->data))
		{
			foreach ($this->data as $name => $last_post)
			{
				if ($last_post < $this->expiry)
				{
					$this->deleteByKey($name);
				}
			}
		}
	}

	function changeName($from, $to)
	{
		if (is_array($this->data) && array_key_exists($from, $this->data))
		{
			$this->deleteByKey($from);
		}

		$this->add($to, time());
	}

	function update($name)
	{
		return parent::update($name, time());
	}

	function getUsers()
	{
		$users = (count($this->data) > 0) ? array_keys($this->data) : array();
		natcasesort($users);
		return $users;
	}

	function computeHash()
	{
		$hashVal = is_array($this->data) ? array_keys($this->data) : uniqid(rand(0,10));
		return md5(serialize($hashVal));
	}
}
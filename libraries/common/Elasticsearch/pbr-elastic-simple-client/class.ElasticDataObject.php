<?php

class ElasticDataObject
{
	private $id;
	private $index;
	private $type;
	private $version;
	private $dataArr;


	public function getId()
	{
		return $this->id;
	}

	public function setId($val)
	{
		$this->id = $val;
	}

	public function getIndex()
	{
		return $this->index;
	}

	public function setIndex($val)
	{
		$this->index = $val;
	}

	public function getType()
	{
		return $this->type;
	}

	public function setType($val)
	{
		$this->type = $val;
	}

	public function getVersion()
	{
		return $this->version;
	}

	public function setVersion($val)
	{
		$this->version = $val;
	}

	public function getData()
	{
		return $this->dataArr;
	}

	public function setData(array $val)
	{
		$this->dataArr = $val;
	}

}
<?php

require_once ('pbr-lib-common/src/Caller/class.Caller.php');
require_once ('pbr-lib-common/src/Elasticsearch/pbr-elastic-simple-client/class.ElasticDataObject.php');
require_once ('pbr-lib-common/src/Elasticsearch/pbr-elastic-simple-client/exception.PbrElasticException.php');


class PBR_Elastic_Client
{
	private $hostname;
	private $index;
	private $type;

	public function __construct($hostname, $index = null, $type = null)
	{
		$this->hostname = $hostname;
		$this->index = $index;
		$this->type = $type;

	}


	public function put($id, array $arrayObj, $index = null, $type = null)
	{
		$index = is_null($index) ? $this->index : $index;
		$type = is_null($type) ? $this->type : $type;
		$url = $this->hostname . "/" . $index . "/" . $type . "/" . $id;
			
		$caller = new Caller($url, "IGNORE");
		$caller->setCurlOpt(CURLOPT_CUSTOMREQUEST, 'POST');
		$caller->setPost(json_encode($arrayObj), 'application/json');
		$res = $caller->call(array(), true);
		if (in_array($caller->getResultCode(), array(200, 201)))
		{
			$resArr = json_decode($res, true);
			$obj = new ElasticDataObject();
			$obj->setIndex($resArr['_index']);
			$obj->setType($resArr['_type']);
			$obj->setVersion($resArr['_version']);
			$obj->setId($resArr['_id']);
			$obj->setData($arrayObj);
			return $obj;
		} 
		else
		{
			throw new Pbr_Elastic_Exception("BAD HTTP RESPONSE CODE PUT|" . $caller->getResultCode() . "|" . $res);
		}

		return null;
	}


	public function getDataObjById($id, $index = null, $type = null)
	{
		$index = is_null($index) ? $this->index : $index;
		$type = is_null($type) ? $this->type : $type;
		$url = $this->hostname . "/" . $index . "/" . $type . "/" . $id;
		$caller = new Caller($url, "IGNORE");
		$res = json_decode($caller->call(), true);
		if ($caller->getResultCode() == 200)
		{
			if (is_array($res) && isset($res["found"]) && $res["found"] == 'true')
			{
				$obj = new ElasticDataObject();
				$obj->setId($res['_id']);
				$obj->setIndex($res['_index']);
				$obj->setType($res['_type']);
				$obj->setVersion($res['_version']);
				$obj->setData($res['_source']);
				return $obj;
			}
		}
		else
		{
			throw new Pbr_Elastic_Exception("BAD HTTP RESPONSE CODE GET|" . $caller->getResultCode() . "|" . $res);
		}
		return null;
	}

	public function getDataObj(ElasticDataObject $obj, $index = null, $type = null)
	{
		return $this->getDataObjById($obj->getId(), $obj->getIndex(), $obj->getType()); 
	}

	public function putDataObj(ElasticDataObject $obj)
	{
		$url = $this->hostname . "/" . $obj->getIndex() . "/" . $obj->getType() . "/" . $obj->getId() . "?" . 'version=' . $obj->getVersion();

		$caller = new Caller($url, "IGNORE");
		$caller->setCurlOpt(CURLOPT_CUSTOMREQUEST, 'POST');
		$caller->setPost(json_encode($obj->getData()), 'application/json');
		$ret = $caller->call(array(), true);

		if (in_array($caller->getResultCode(), array(200, 201)))
		{
			$res = json_decode($ret, true);
			$obj->setVersion($res['_version']);
			return $obj;
		} 
		else
		{
			throw new Pbr_Elastic_Exception("BAD HTTP RESPONSE CODE PUT|" . $caller->getResultCode() . "|" . $ret);
		}
		return null;
	}



}
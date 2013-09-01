<?php

namespace Tyrell;

use Tonic\Resource,
    Tonic\Response,
    Tonic\ConditionException;

/**
 * The obligitory Hello World example
 *
 * The @uri annotation routes requests that match that URL to this resource. Multiple
 * annotations allow this resource to match multiple URLs.
 *
 * @uri /crud
 * @uri /crud/:method
 * @uri /crud.fr/:method
 * @uri /crud.json/:method
 */

class Crud extends Resource
{
    /**
     * The @provides annotation makes method only match requests that have a suitable accept
     * header or URL extension (ie: /hello.json) and causes the response to automatically
     * contain the correct content-type response header.
     *
     * @method GET
	 * @param  str $method
     * @provides application/json
     * @json
     * @return Tonic\Response
     */
    public function selectMethod($method = null)
    {	
        //if (is_callable(array($this, $method . "Method")))
            //return call_user_func(array($this, $method . "Method"));
		
        $db = new \PDO('pgsql:host=donar.immo;dbname=mobile_commerce','inform','l!j@cneg');
			//var_dump($db);
			$result = $db->query("SELECT * FROM service order by service_id");
			$data = array();
			while($row = $result->fetch())
			{
				$data[] = $row;
			}
			return new Response(200, array(
				'success' => true,
				'message' => 'Loaded data',
				'data' => $data,
			));
        
        return new Response(400, array());
    }
    
    
     private function viewMethod($method = null)
    {
        $db = new \PDO('pgsql:host=donar.immo;dbname=mobile_commerce','inform','l!j@cneg');
			//var_dump($db);
			$result = $db->query("SELECT * FROM service order by service_id");
			$data = array();
			while($row = $result->fetch())
			{
				$data[] = $row;
			}
			return new Response(200, array(
				'success' => true,
				'message' => 'Loaded data',
				'data' => $data,
			));
    }
    
    /**
     * The @provides annotation makes method only match requests that have a suitable accept
     * header or URL extension (ie: /hello.json) and causes the response to automatically
     * contain the correct content-type response header.
     *
     * @method POST
	 * @param  str $method
     * @provides application/json
     * @json
     * @return Tonic\Response
     */
    public function createMethod($method = null)
    {	
        
        return new Response(400, array());
    }
    
    /**
     * The @provides annotation makes method only match requests that have a suitable accept
     * header or URL extension (ie: /hello.json) and causes the response to automatically
     * contain the correct content-type response header.
     *
     * @method DELETE
	 * @param  str $method
     * @provides application/json
     * @json
     * @return Tonic\Response
     */
    public function deleteMethod($method = null)
    {	
        
        return new Response(400, array('asd' => '123'));
    }

    /**
     * Condition method to turn output into JSON.
     *
     * This condition sets a before and an after filter for the request and response. The
     * before filter decodes the request body if the request content type is JSON, while the
     * after filter encodes the response body into JSON.
     */
    protected function json()
    {
        $this->before(function ($request) {
            if ($request->contentType == "application/json") {
                $request->data = json_decode($request->data);
            }
        });
        $this->after(function ($response) {
            $response->contentType = "application/json";
            if (isset($_GET['jsonp'])) {
                $response->body = $_GET['jsonp'].'('.json_encode($response->body).');';
            } else {
                $response->body = json_encode($response->body);
            }
        });
    }

}

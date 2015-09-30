<?php

    //pbr-lib-common/src/Riak/riak-php-client/
    require_once('Basho/Riak/Riak.php');
    require_once('Basho/Riak/Bucket.php');
    require_once('Basho/Riak/Exception.php');
    require_once('Basho/Riak/Link.php');
    require_once('Basho/Riak/MapReduce.php');
    require_once('Basho/Riak/Object.php');
    require_once('Basho/Riak/StringIO.php');
    require_once('Basho/Riak/Utils.php');
    require_once('Basho/Riak/Link/Phase.php');
    require_once('Basho/Riak/MapReduce/Phase.php');

    # Connect to Riak
    $client = new Basho\Riak\Riak('vps2236.mtu.immo', 8098);

    # Choose a bucket name
    $bucket = $client->bucket('test');

    # Supply a key under which to store your data
    $person = $bucket->newObject('riak_developer_1', array(
        'name' => "John Smith",
        'age' => 28,
        'company' => "Facebook"
    ));

    # Save the object to Riak
    try
    {
        $person->store();
    }
    catch (Basho\Riak\Exception $e)
    {
       var_dump("!!!!!!!!!!!!!!!!!!!");
       var_dump($e);
       die();
    }
    # Fetch the object
    $person = $bucket->get('riak_developer_2');
    var_dump($person);
    if ($person->getData() == null)
    {
        var_dump("NOT FOUND!!!!");
    }
    // curl -v http://vps2236.mtu.immo:8098/buckets/test/keys/riak_developer_1
    //{"name":"John Smith","age":28,"company":"Facebook"}
    //$person->data['company'] = "Google";
    //$person->store();
 
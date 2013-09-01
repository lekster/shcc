<?php
include '../../../bootstrap.php';
// Подключаем сервис
chdir(GIRAR_BASE_DIR);
// load autoloader (delete as appropriate)
if (@include('pbr-lib-common/src/RestApiServer/src/Tonic/Autoloader.php')) { // use Tonic autoloader
    #new Tonic\Autoloader('myNamespace'); // add another namespace
} elseif (!@include('../vendor/autoload.php')) { // use Composer autoloader
    die('Could not find autoloader');
}

$config = array(
    'load' => array(
        'pbr-wserv-rv/htdocs/web/rest/src/*.php', // load example resources
        //'../vendor/peej/tonic/src/Tyrell/*.php' // load examples from composer's vendor directory
    ),
    #'mount' => array('Tyrell' => '/nexus'), // mount in example resources at URL /nexus
    #'cache' => new Tonic\MetadataCacheFile('/tmp/tonic.cache') // use the metadata cache
    #'cache' => new Tonic\MetadataCacheAPC // use the metadata cache
);

$app = new Tonic\Application($config);

#echo $app; die;

$request = new Tonic\Request();

#echo $request; die;

try {
	//var_dump($request);
    $resource = $app->getResource($request);

    #echo $resource; die;

    $response = $resource->exec();

} catch (Tonic\NotFoundException $e) {
    $response = new Tonic\Response(404, $e->getMessage());

} catch (Tonic\UnauthorizedException $e) {
    $response = new Tonic\Response(401, $e->getMessage());
    $response->wwwAuthenticate = 'Basic realm="My Realm"';

} catch (Tonic\Exception $e) {
    $response = new Tonic\Response($e->getCode(), $e->getMessage());
}

#echo $response;

$response->output();

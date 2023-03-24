<?php

use Aharon\Api;
use Aharon\App;

require 'vendor/autoload.php';

$app    = new App( require( 'config.php' ) );
$output = match ($_REQUEST['action']) {
    'insert' => $app->api->insertUsers(),
    'get'    => $app->api->getUsers(),
    default  => $app->api->prepareResponse([], Api::RESPONSE_ERROR ),
};

echo $output;

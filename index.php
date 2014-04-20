<?php

require_once __DIR__ . "/bootstrap.php";

use rtens\dox\Configuration;
use rtens\dox\web\RootResource;
use watoki\cfg\Loader;
use watoki\curir\WebApplication;
use watoki\factory\Factory;

$factory = new Factory();

$loader = new Loader($factory);
$loader->loadConfiguration(Configuration::$CLASS, __DIR__ . '/user/UserConfiguration.php');

WebApplication::quickStart(RootResource::$CLASS, $factory);
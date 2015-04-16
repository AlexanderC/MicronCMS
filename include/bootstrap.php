<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/15/15
 * Time: 21:59
 */

define("__CONTENT_DIR__", __DIR__ . '/_content');

$application = new \MicronCMS\Application(__CONTENT_DIR__);
$application->setCache(true);

$response = $application->dispatch();

$response->send();
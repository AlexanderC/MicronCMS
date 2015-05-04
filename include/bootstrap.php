<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/15/15
 * Time: 21:59
 */

define("__CONTENT_DIR__", __DIR__ . '/_content');
define("__UPLOAD_PATH__", '/_/upload');
define("__OTP_SETUP_PATH__", '/_/otp_setup');
define("__SECRET__", \MicronCMS\Util\Environment::get('micron_cms_auth_secret'));

$application = new \MicronCMS\Application(__CONTENT_DIR__);
$application->setCache(true);

$otpSetup = (new \MicronCMS\Helper\OTPSetup($application, __SECRET__))
    ->listen(__OTP_SETUP_PATH__);

(new \MicronCMS\Management\UploadListener($application))
    ->secure($otpSetup->getPolicy())
    ->listen(__UPLOAD_PATH__);

$response = $application->dispatch();

$response->send();
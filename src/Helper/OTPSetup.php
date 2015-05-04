<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 5/4/15
 * Time: 16:39
 */

namespace MicronCMS\Helper;

use MicronCMS\Application;
use MicronCMS\CompilableInterface;
use MicronCMS\Exception\Exception;
use MicronCMS\HttpKernel\Request;
use MicronCMS\HttpKernel\Response;
use MicronCMS\Security\OTP;
use MicronCMS\Security\Policy\OTPPolicy;
use MicronCMS\Security\Policy\PolicyInterface;


/**
 * Class OTPSetup
 * @package MicronCMS\Helper
 */
class OTPSetup implements CompilableInterface
{
    use CompilableDefaults;

    const PARAMETER_NAME = '_secret_hash';

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var OTPPolicy
     */
    protected $policy;

    /**
     * @var bool
     */
    protected $isListening;

    /**
     * @var string
     */
    protected $pathToListen;

    /**
     * @param Application $application
     * @param string $secret
     */
    public function __construct(Application $application, $secret)
    {
        $this->application = $application;
        $this->secret = $secret;
        $this->policy = new OTPPolicy(
            OTP::create(__SECRET__)
        );
    }

    /**
     * @return boolean
     */
    public function isListening()
    {
        return $this->isListening;
    }

    /**
     * @param boolean $isListening
     * @return $this
     */
    public function setIsListening($isListening)
    {
        $this->isListening = $isListening;
        return $this;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @return OTPPolicy
     */
    public function getPolicy()
    {
        return $this->policy;
    }

    /**
     * @param string $pathToListen
     * @return $this
     */
    public function listen($pathToListen = null)
    {
        if ($this->isListening) {
            throw new Exception("OTP Setup listener is already listening");
        }

        $this->pathToListen = $pathToListen;

        $this->application->addHook(Application::BEFORE, [$this, 'manage']);

        return $this;
    }

    /**
     * @param Hook $hook
     * @param Application $application
     * @param Response|null $response
     * @param Request $request
     */
    public function manage(Hook $hook, Application $application, & $response, Request $request)
    {
        if ($request->matchPath($this->pathToListen)) {
            $testParameterName = OTPPolicy::PARAMETER_NAME;

            if (!$this->matchSecret($request->get(self::PARAMETER_NAME))) {
                $response = $application->createNotAuthorizedResponse('Wrong secret hash provided');
                $hook->setStopped(true);
                return;
            }

            $otpCheck = 'NOT SUBMITTED';

            if ($request->get($testParameterName)) {
                $otpCheck = PolicyInterface::ALLOW === $this->policy->apply($request) ? 'OK' : 'FAIL';
            }

            $response = new Response($application->render(
                '/system/_otp_setup.html',
                [
                    'otpCheck' => $otpCheck,
                    'testParameterName' => $testParameterName,
                    'provisioningUrl' => rawurlencode($this->policy->getOtp()->getProvisioningUri())
                ]
            ));

            $hook->setStopped(true);
        }
    }

    /**
     * @param string $secretHash
     * @param string $algorithm
     * @return bool
     */
    public function matchSecret($secretHash, $algorithm = 'md5')
    {
        return hash($algorithm, $this->secret) === $secretHash;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 5/4/15
 * Time: 12:10
 */

namespace MicronCMS\Security\Policy;

use MicronCMS\CompilableInterface;
use MicronCMS\Helper\CompilableDefaults;
use MicronCMS\HttpKernel\Request;
use MicronCMS\Security\OTP;


/**
 * Class OTPPolicy
 * @package MicronCMS\Security\Policy
 */
class OTPPolicy implements PolicyInterface, CompilableInterface
{
    use CompilableDefaults;

    const PARAMETER_NAME = '_token';

    /**
     * @var OTP
     */
    protected $otp;

    /**
     * @param OTP $otp
     */
    public function __construct(OTP $otp)
    {
        $this->otp = $otp;
    }

    /**
     * @return OTP
     */
    public function getOtp()
    {
        return $this->otp;
    }

    /**
     * @param Request $request
     * @return int
     */
    public function apply(Request $request)
    {
        $token = $request->get(self::PARAMETER_NAME);

        if (empty($token)) {
            return self::DENY;
        }

        return $this->otp->verify($token) ? self::ALLOW : self::DENY;
    }
}
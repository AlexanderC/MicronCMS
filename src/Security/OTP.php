<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 5/4/15
 * Time: 12:26
 */

namespace MicronCMS\Security;

use Base32\Base32;
use MicronCMS\CompilableInterface;
use MicronCMS\Helper\CompilableDefaults;
use OTPHP\TOTP;


/**
 * Class OTP
 * @package MicronCMS\Security
 */
class OTP extends TOTP implements CompilableInterface
{
    use CompilableDefaults;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var string
     */
    protected $label = 'MicronCMS';

    /**
     * @var string
     */
    protected $issuer = 'MicronCMS';

    /**
     * @var bool
     */
    protected $issuerIncludedAsParameter = true;

    /**
     * @var int
     */
    protected $digits = 6;

    /**
     * @var string
     */
    protected $digest = 'sha1';

    /**
     * @var int
     */
    protected $interval = 60;

    /**
     * @param string $secret
     * @param bool $fromRaw
     */
    public function __construct($secret, $fromRaw = true)
    {
        if ($fromRaw) {
            $this->setRawSecret($secret);
        } else {
            $this->setSecret($secret);
        }
    }

    /**
     * @param int $otp
     * @param int $timestamp
     * @param int $window
     * @return bool
     */
    public function verify($otp, $timestamp = null, $window = null)
    {
        return parent::verify((int)$otp, $timestamp, $window);
    }

    /**
     * @param string $secret
     * @return static
     */
    public static function create($secret)
    {
        return new static($secret);
    }

    /**
     * @param string $secret
     * @return $this
     */
    public function setRawSecret($secret)
    {
        $this->secret = trim(Base32::encode($secret), '=');
        return $this;
    }

    /**
     * @param string $secret
     * @return $this
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
        return $this;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @param string $issuer
     * @return $this
     */
    public function setIssuer($issuer)
    {
        $this->issuer = $issuer;
        return $this;
    }

    /**
     * @param boolean $issuerIncludedAsParameter
     * @return $this
     */
    public function setIssuerIncludedAsParameter($issuerIncludedAsParameter)
    {
        $this->issuerIncludedAsParameter = $issuerIncludedAsParameter;
        return $this;
    }

    /**
     * @param int $digits
     * @return $this
     */
    public function setDigits($digits)
    {
        $this->digits = $digits;
        return $this;
    }

    /**
     * @param string $digest
     * @return $this
     */
    public function setDigest($digest)
    {
        $this->digest = $digest;
        return $this;
    }

    /**
     * @param int $interval
     * @return $this
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;
        return $this;
    }

    /**
     * @return string The secret of the OTP
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @return string The label of the OTP
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string The issuer
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * @return bool If true, the issuer will be added as a parameter in the provisioning URI
     */
    public function isIssuerIncludedAsParameter()
    {
        return $this->issuerIncludedAsParameter;
    }

    /**
     * @return int Number of digits in the OTP
     */
    public function getDigits()
    {
        return $this->digits;
    }

    /**
     * @return string Digest algorithm used to calculate the OTP. Possible values are 'md5', 'sha1', 'sha256' and 'sha512'
     */
    public function getDigest()
    {
        return $this->digest;
    }

    /**
     * @return int Get the interval of time for OTP generation (a non-null positive integer, in second)
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @return array
     */
    public static function compileDependencies()
    {
        return [
            Base32::class
        ];
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/15/15
 * Time: 23:39
 */

namespace MicronCMS\HttpKernel;

use MicronCMS\CompilableInterface;
use MicronCMS\Helper\CompilableDefaults;
use MicronCMS\HttpKernel\Exception\UnableToSendResponseException;


/**
 * Class Response
 * @package MicronCMS\HttpKernel
 */
class Response implements CompilableInterface
{
    use CompilableDefaults;

    const NO_CONTENT = 204;
    const SUCCESS = 200;
    const NOT_FOUND = 404;
    const ERROR = 500;
    const PERMANENT_REDIRECT = 301;
    const TEMPORARY_REDIRECT = 302;
    const ACCESS_DENIED = 403;

    /**
     * @var int
     */
    public $httpCode;

    /**
     * @var string
     */
    public $content;

    /**
     * @param string $content
     * @param int $httpCode
     */
    public function __construct($content, $httpCode = self::SUCCESS)
    {
        $this->httpCode = $httpCode;
        $this->content = $content;
    }

    /**
     * @return int
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param bool $permanent
     */
    public function redirect($permanent = false)
    {
        if (!filter_var($this->content, FILTER_VALIDATE_URL)) {
            throw new UnableToSendResponseException("Content is not a valid url");
        }

        static::assureHeadersNotSent();

        $redirectType = $permanent ? self::PERMANENT_REDIRECT : self::TEMPORARY_REDIRECT;

        @ob_end_clean();
        header(sprintf('Location: %s', $this->content), true, $redirectType);
        @ob_end_flush();
    }

    /**
     * @retunr void
     */
    public function send()
    {
        static::assureHeadersNotSent();

        @ob_end_clean();
        header('Content-Type: text/html; charset=UTF-8');
        header('Cache-Control: private; max-age=0');
        http_response_code($this->httpCode);
        @ob_end_flush();
        echo $this->content;
        @ob_end_flush();
    }

    /**
     * @throws UnableToSendResponseException
     */
    protected static function assureHeadersNotSent()
    {
        if (headers_sent()) {
            throw new UnableToSendResponseException("Headers already sent");
        }
    }
}
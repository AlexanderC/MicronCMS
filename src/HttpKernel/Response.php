<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/15/15
 * Time: 23:39
 */

namespace MicronCMS\HttpKernel;

use MicronCMS\AbstractCompilable;
use MicronCMS\Exception\ApplicationException;


/**
 * Class Response
 * @package MicronCMS\HttpKernel
 */
class Response extends AbstractCompilable
{
    const NO_CONTENT = 204;
    const SUCCESS = 200;
    const NOT_FOUND = 404;
    const ERROR = 500;

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
     * @retunr void
     */
    public function send()
    {
        if(headers_sent()) {
            throw new ApplicationException("Headers already sent");
        }

        @ob_end_clean();
        header('Content-Type: text/html; charset=UTF-8');
        header('Cache-Control: private; max-age=0');
        http_response_code($this->httpCode);
        @ob_end_flush();
        echo $this->content;
        @ob_end_flush();
    }
}
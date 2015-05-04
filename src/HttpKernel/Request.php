<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/15/15
 * Time: 23:31
 */

namespace MicronCMS\HttpKernel;

use MicronCMS\CompilableInterface;
use MicronCMS\Helper\CompilableDefaults;


/**
 * Class Request
 * @package MicronCMS\HttpKernel
 */
class Request implements CompilableInterface
{
    use CompilableDefaults;

    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';
    const PATCH = 'PATCH';
    const OPTIONS = 'OPTIONS';
    const HEAD = 'HEAD';

    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var File[]
     */
    protected $files;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $scheme;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @param string $url
     * @param string $method
     * @param array $data
     * @param array $files
     */
    public function __construct($url, $method, array $data, array $files)
    {
        $urlParts = static::parseUrl($url);

        $this->method = strtoupper($method);
        $this->baseUrl = $urlParts['base_url'];
        $this->host = $urlParts['host'];
        $this->scheme = $urlParts['scheme'];
        $this->port = (int)$urlParts['port'];
        $this->path = sprintf('/%s', trim($urlParts['path'], '/'));
        $this->data = $data;
        $this->files = $files;
    }

    /**
     * @return Request
     */
    public static function createFromGlobals()
    {
        return new static(
            static::getFullUrlFromGlobals(),
            $_SERVER['REQUEST_METHOD'],
            array_merge($_GET, $_POST),
            File::createFromGlobals()
        );
    }

    /**
     * @param string $url
     * @return array
     */
    protected static function parseUrl($url)
    {
        $parts = parse_url($url);

        $hasExplicitPort = isset($parts['port']);

        $scheme = isset($parts['scheme']) ? $parts['scheme'] : 'http';
        $host = $parts['host'];
        $port = isset($parts['port']) ? $parts['port'] : ('http' === $scheme ? 80 : 443);
        $path = isset($parts['path']) ? $parts['path'] : '';

        return [
            'base_url' => $hasExplicitPort
                ? sprintf('%s://%s:%d', $scheme, $host, $port)
                : sprintf('%s://%s', $scheme, $host),
            'scheme' => $scheme,
            'host' => $host,
            'port' => $port,
            'path' => $path
        ];
    }

    /**
     * @param bool $useForwardedHost
     * @return string
     */
    protected static function getFullUrlFromGlobals($useForwardedHost = false)
    {
        $ssl = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true : false;
        $sp = strtolower($_SERVER['SERVER_PROTOCOL']);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        $port = $_SERVER['SERVER_PORT'];
        $port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
        $host = ($useForwardedHost && isset($_SERVER['HTTP_X_FORWARDED_HOST']))
            ? $_SERVER['HTTP_X_FORWARDED_HOST']
            : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null);
        $host = isset($host) ? $host : $_SERVER['SERVER_NAME'] . $port;

        return $protocol . '://' . $host . $_SERVER['REQUEST_URI'];
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @return File[]
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function set($name, $value)
    {
        $this->data[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return $this->has($name) ? $this->data[$name] : $default;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * @param string $pathExpression
     * @return bool
     */
    public function matchPath($pathExpression)
    {
        return (bool)preg_match(sprintf('~^%s$~ui', $pathExpression), $this->getPath());
    }
}
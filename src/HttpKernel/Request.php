<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/15/15
 * Time: 23:31
 */

namespace MicronCMS\HttpKernel;

use MicronCMS\AbstractCompilable;


/**
 * Class Request
 * @package MicronCMS\HttpKernel
 */
class Request extends AbstractCompilable
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param string $path
     * @param array $data
     */
    public function __construct($path, array $data)
    {
        $this->path = sprintf('/%s', trim($path, '/'));
        $this->data = $data;
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
     * @return bool
     */
    public function has($name)
    {
        return isset($this->data[$name]);
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
     * @return Request
     */
    public static function createFromGlobals()
    {
        return new static(
            $_SERVER['REQUEST_URI'],
            array_merge($_GET, $_POST)
        );
    }
}
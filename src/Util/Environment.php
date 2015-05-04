<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 5/4/15
 * Time: 14:06
 */

namespace MicronCMS\Util;

use MicronCMS\CompilableInterface;
use MicronCMS\Helper\CompilableDefaults;


/**
 * Class Environment
 * @package MicronCMS\Util
 */
class Environment implements CompilableInterface
{
    use CompilableDefaults;

    /**
     * @param string $name
     * @return string
     */
    public static function get($name)
    {
        $value = getenv($name);

        if (false === $value && function_exists('apache_getenv')) {
            $value = apache_getenv($name);
        }

        return $value;
    }
}
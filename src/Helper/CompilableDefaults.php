<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 5/4/15
 * Time: 14:53
 */

namespace MicronCMS\Helper;


/**
 * Class CompilableDefaults
 * @package MicronCMS\Helper
 */
trait CompilableDefaults 
{
    /**
     * @param \ReflectionClass $static
     */
    public static function preCompile(\ReflectionClass $static)
    {

    }

    /**
     * @param string $generatedCode
     */
    public static function postCompile(& $generatedCode)
    {

    }

    /**
     * @return array
     */
    public static function compileDependencies()
    {
        return [];
    }
}
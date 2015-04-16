<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/15/15
 * Time: 17:54
 */

namespace MicronCMS;


/**
 * Class AbstractCompilable
 * @package MicronCMS
 */
abstract class AbstractCompilable implements CompilableInterface
{
    /**
     * @param \ReflectionClass $static
     */
    public static function preCompile(\ReflectionClass $static)
    {
        // TODO: Implement preCompile() method.
    }

    /**
     * @param string $generatedCode
     */
    public static function postCompile(& $generatedCode)
    {
        // TODO: Implement postCompile() method.
    }

    /**
     * @return array
     */
    public static function compileDependencies()
    {
        return [];
    }
}
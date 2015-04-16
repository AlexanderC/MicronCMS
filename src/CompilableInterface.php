<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/15/15
 * Time: 17:38
 */

namespace MicronCMS;


/**
 * Interface CompilableInterface
 * @package MicronCMS
 */
interface CompilableInterface
{
    /**
     * @param \ReflectionClass $static
     */
    public static function preCompile(\ReflectionClass $static);

    /**
     * @param string $generatedCode
     */
    public static function postCompile(& $generatedCode);

    /**
     * @return array
     */
    public static function compileDependencies();
}
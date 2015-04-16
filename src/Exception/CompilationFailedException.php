<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/15/15
 * Time: 18:11
 */

namespace MicronCMS\Exception;

use MicronCMS\NonCompilableInterface;


/**
 * Class CompilationFailedException
 * @package MicronCMS\Exception
 */
class CompilationFailedException extends Exception implements NonCompilableInterface
{

}
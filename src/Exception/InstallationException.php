<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/15/15
 * Time: 20:08
 */

namespace MicronCMS\Exception;

use MicronCMS\NonCompilableInterface;


/**
 * Class InstallationException
 * @package MicronCMS\Exception
 */
class InstallationException extends ApplicationException implements NonCompilableInterface
{

}
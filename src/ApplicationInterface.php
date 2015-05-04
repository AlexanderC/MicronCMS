<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/30/15
 * Time: 12:04
 */

namespace MicronCMS;


/**
 * Interface ApplicationInterface
 * @package MicronCMS
 */
interface ApplicationInterface 
{
    const BEFORE = 0x001;
    const AFTER = 0x002;
    const ERROR = 0x003;
}
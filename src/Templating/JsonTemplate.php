<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/16/15
 * Time: 00:26
 */

namespace MicronCMS\Templating;

use MicronCMS\Templating\Exception\InvalidTemplateException;
use MicronCMS\Templating\Exception\MissingTemplateException;


/**
 * Class JsonTemplate
 * @package MicronCMS\Templating
 */
class JsonTemplate extends NativeTemplate
{
    /**
     * @return string
     */
    public function compile()
    {
        $json = parent::compile();

        if (false === ($content = @json_decode(trim($json)))) {
            throw new InvalidTemplateException("Invalid json string");
        }

        return nl2br(str_replace(' ', '&nbsp;', json_encode($content, JSON_PRETTY_PRINT)));
    }
}
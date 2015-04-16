<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/16/15
 * Time: 00:26
 */

namespace MicronCMS\Templating;

use MicronCMS\Templating\Exception\MissingTemplateException;


/**
 * Class HtmlTemplate
 * @package MicronCMS\Templating
 */
class TxtTemplate extends NativeTemplate
{
    /**
     * @return string
     */
    public function compile()
    {
        $text = parent::compile();

        return nl2br(str_replace(' ', '&nbsp;', $text));
    }
}
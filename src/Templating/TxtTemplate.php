<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/16/15
 * Time: 00:26
 */

namespace MicronCMS\Templating;


/**
 * Class HtmlTemplate
 * @package MicronCMS\Templating
 */
class TxtTemplate extends PreProcessedTemplate
{
    /**
     * @return string
     */
    public function compile()
    {
        $text = parent::compile();

        return nl2br(str_replace([
            ' ',
            "\t"
        ], [
            '&nbsp;',
            '&nbsp;&nbsp;&nbsp;&nbsp;'
        ], $text));
    }
}
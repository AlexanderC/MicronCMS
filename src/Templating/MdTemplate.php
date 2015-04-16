<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/16/15
 * Time: 00:26
 */

namespace MicronCMS\Templating;

use Michelf\MarkdownExtra as Markdown;
use MicronCMS\Templating\Exception\MissingTemplateException;


/**
 * Class MdTemplate
 * @package MicronCMS\Templating
 */
class MdTemplate extends NativeTemplate
{
    /**
     * @return string
     */
    public function compile()
    {
        $markdown = parent::compile();

        $html = Markdown::defaultTransform($markdown);

        return $html;
    }

    /**
     * @return array
     */
    public static function compileDependencies()
    {
        return [
            Markdown::class
        ];
    }
}
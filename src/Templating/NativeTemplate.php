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
 * Class NativeTemplate
 * @package MicronCMS\Templating
 */
class NativeTemplate extends AbstractTemplate
{
    /**
     * @return string
     */
    public function compile()
    {
        $content = file_get_contents($this->filePath);

        if (false === $content) {
            throw new MissingTemplateException("Unable to read template file");
        }

        $preProcessor = new PreProcessor();
        $preProcessor->process($content, $this->filePath);

        return $content;
    }
}
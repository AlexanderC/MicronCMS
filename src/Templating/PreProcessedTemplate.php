<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/21/15
 * Time: 12:02
 */

namespace MicronCMS\Templating;


/**
 * Class PreProcessedTemplate
 * @package MicronCMS\Templating
 */
class PreProcessedTemplate extends NativeTemplate
{
    /**
     * @return string
     */
    public function compile()
    {
        $content = parent::compile();

        $preProcessor = new PreProcessor();
        $preProcessor->process($content, $this->filePath);

        return $content;
    }
}
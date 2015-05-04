<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/21/15
 * Time: 13:44
 */

namespace MicronCMS\Templating\Cache;

use MicronCMS\CompilableInterface;
use MicronCMS\Helper\CompilableDefaults;
use MicronCMS\Templating\AbstractTemplate;


/**
 * Class TemplateCache
 * @package MicronCMS\Templating\Cache
 */
class TemplateCache implements CompilableInterface
{
    use CompilableDefaults;

    /**
     * @param AbstractTemplate $template
     * @param string $content
     * @return bool
     */
    public function cache(AbstractTemplate $template, & $content)
    {
        $templateFilePath = $template->getFilePath();
        $cacheFilePath = $this->getCacheFilePath($templateFilePath);

        return false !== file_put_contents(
            $cacheFilePath,
            $content = $template->compile(),
            LOCK_EX | LOCK_NB
        );
    }

    /**
     * @param string $templateFilePath
     * @return string
     */
    protected function getCacheFilePath($templateFilePath)
    {
        return preg_replace(
            '/\.[^\.]+$/ui',
            sprintf('.%s', AbstractTemplate::NATIVE_EXTENSION),
            $templateFilePath
        );
    }
}
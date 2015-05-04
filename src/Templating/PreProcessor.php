<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/16/15
 * Time: 10:16
 */

namespace MicronCMS\Templating;

use MicronCMS\CompilableInterface;
use MicronCMS\Helper\CompilableDefaults;
use MicronCMS\Templating\Exception\PreProcessingFailedException;


/**
 * Class PreProcessor
 * @package MicronCMS\Templating
 */
class PreProcessor implements CompilableInterface
{
    use CompilableDefaults;

    const T_INCLUDE = 0x001;
    const T_INCLUDE_REGEXP = '/(\${\s*include\s+(?P<statement>[^\s]+)\s*})/ui';

    /**
     * @var array
     */
    protected $parseMap = [
        self::T_INCLUDE => self::T_INCLUDE_REGEXP
    ];

    /**
     * @param string $content
     * @param string $sourceFile
     */
    public function process(& $content, $sourceFile)
    {
        foreach ($this->parseMap as $token => $regexp) {
            $content = preg_replace_callback($regexp, function ($matches) use ($token, $sourceFile) {
                $statement = $matches['statement'];

                return $this->processStatement($token, $statement, $sourceFile);
            }, $content);
        }
    }

    /**
     * @param string $token
     * @param string $statement
     * @param string $sourceFile
     * @return string
     */
    protected function processStatement($token, $statement, $sourceFile)
    {
        switch ($token) {
            case self::T_INCLUDE:
                $basePath = dirname($sourceFile);
                $templateRelativePath = ltrim($statement, '/');
                $templatePath = sprintf('%s/%s', $basePath, $templateRelativePath);

                if (!is_file($templatePath)) {
                    throw new PreProcessingFailedException("Unable to find included template");
                }

                $template = AbstractTemplate::create(new \SplFileInfo($templatePath));

                return $template->compile();

                break;
        }

        return null;
    }
}
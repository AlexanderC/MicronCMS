<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/16/15
 * Time: 00:21
 */

namespace MicronCMS\Templating;

use MicronCMS\CompilableInterface;
use MicronCMS\Helper\CompilableDefaults;
use MicronCMS\Templating\Cache\TemplateCache;
use MicronCMS\Templating\Exception\CachingFailedException;
use MicronCMS\Templating\Exception\MissingTemplateException;


/**
 * Class AbstractTemplate
 * @package MicronCMS\Templating
 */
abstract class AbstractTemplate implements CompilableInterface
{
    use CompilableDefaults;

    const NATIVE_EXTENSION = 'micron';

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @return string
     */
    public function cache()
    {
        $cache = new TemplateCache();

        if (static::class !== NativeTemplate::class) {
            if(!$cache->cache($this, $content)) {
                throw new CachingFailedException("Unable to create cache file");
            }

            return $content;
        }

        return $this->compile();
    }

    /**
     * @return string
     */
    abstract public function compile();

    /**
     * @param \SplFileInfo $file
     * @return NativeTemplate
     */
    public static function create(\SplFileInfo $file)
    {
        $extension = $file->getExtension();

        if ($extension === self::NATIVE_EXTENSION) {
            return new NativeTemplate((string)$file);
        }

        $templateClass = sprintf('%s\\%sTemplate', __NAMESPACE__, ucfirst($extension));

        if (!class_exists($templateClass)) {
            throw new MissingTemplateException(sprintf(
                "Missing template class for '%s'",
                $extension
            ));
        }

        return new $templateClass((string)$file);
    }
}
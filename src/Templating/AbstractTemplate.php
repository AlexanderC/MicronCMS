<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/16/15
 * Time: 00:21
 */

namespace MicronCMS\Templating;

use MicronCMS\AbstractCompilable;
use MicronCMS\Templating\Exception\CachingFailedException;
use MicronCMS\Templating\Exception\MissingTemplateException;


/**
 * Class AbstractTemplate
 * @package MicronCMS\Templating
 */
abstract class AbstractTemplate extends AbstractCompilable
{
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
     * @return void
     */
    public function cache()
    {
        if (static::class !== NativeTemplate::class) {
            $cacheFile = preg_replace(
                '/\.[^\.]+$/ui',
                sprintf('.%s', static::NATIVE_EXTENSION),
                $this->filePath
            );

            if (!file_put_contents($cacheFile, $this->compile(), LOCK_EX)) {
                throw new CachingFailedException("Unable to create cache file");
            }
        }
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
            return new NativeTemplate((string) $file);
        }

        $templateClass = sprintf('%s\\%sTemplate', __NAMESPACE__, ucfirst($extension));

        if(!class_exists($templateClass)) {
            throw new MissingTemplateException(sprintf(
                "Missing template class for '%s'",
                $extension
            ));
        }

        return new $templateClass((string) $file);
    }
}
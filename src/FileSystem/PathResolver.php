<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/16/15
 * Time: 00:07
 */

namespace MicronCMS\FileSystem;

use MicronCMS\CompilableInterface;
use MicronCMS\FileSystem\Exception\MissingPathException;
use MicronCMS\Helper\CompilableDefaults;
use MicronCMS\Templating\AbstractTemplate;


/**
 * Class PathResolver
 * @package MicronCMS\FileSystem
 */
class PathResolver implements CompilableInterface
{
    use CompilableDefaults;

    const INDEX = '_index';

    /**
     * @var string
     */
    protected $baseDirectory;

    /**
     * @var array
     */
    protected $allowedExtensions = ['md', 'html', 'txt', 'json'];

    /**
     * @param string $baseDirectory
     */
    public function __construct($baseDirectory)
    {
        $this->baseDirectory = realpath($baseDirectory);

        if (empty($this->baseDirectory)) {
            throw new MissingPathException("Missing base directory");
        }
    }

    /**
     * @return string
     */
    public function getBaseDirectory()
    {
        return $this->baseDirectory;
    }

    /**
     * @return array
     */
    public function getAllowedExtensions()
    {
        return $this->allowedExtensions;
    }

    /**
     * @param string $path
     * @return null|\SplFileInfo
     */
    public function resolve($path)
    {
        $path = ltrim($path, '/');
        $path = empty($path) ? self::INDEX : $path;

        $baseFilePath = sprintf('%s/%s', $this->baseDirectory, $path);

        if (preg_match('/\.[^\/\.]+$/ui', $baseFilePath) && is_file($baseFilePath)) {
            return new \SplFileInfo($baseFilePath);
        }

        $nativeFile = sprintf('%s.%s', $baseFilePath, AbstractTemplate::NATIVE_EXTENSION);

        // always highest priority
        if (is_file($nativeFile)) {
            return new \SplFileInfo($nativeFile);
        }

        $regexp = sprintf(
            '/^%s\.(%s)$/ui',
            preg_quote($baseFilePath, '/'),
            implode('|', $this->allowedExtensions)
        );

        $walker = new Walker(dirname($baseFilePath), $regexp);

        return $walker->getIterator()->current() ?: null;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/15/15
 * Time: 22:34
 */

namespace MicronCMS;

use MicronCMS\Exception\InstallationException;
use MicronCMS\FileSystem\RecursiveWalker;


/**
 * Class Installer
 * @package MicronCMS
 */
class Installer 
{
    /**
     * @var string
     */
    protected $buildDirectory;

    /**
     * @var string
     */
    protected $contentPath;

    /**
     * @var string
     */
    protected $htaccessTemplate;

    /**
     * @var string
     */
    protected $defaultContentDirectory;

    /**
     * @var string
     */
    protected $bootstrapFile;

    /**
     * @param string $buildDirectory
     * @param string $contentPath
     * @param string $htaccessTemplate
     * @param string $defaultContentDirectory
     * @param string $bootstrapFile
     */
    public function __construct($buildDirectory, $contentPath, $htaccessTemplate, $defaultContentDirectory, $bootstrapFile)
    {
        $this->buildDirectory = rtrim($buildDirectory, '/');
        $this->contentPath = ltrim($contentPath, '/');
        $this->htaccessTemplate = $htaccessTemplate;
        $this->defaultContentDirectory = realpath($defaultContentDirectory);
        $this->bootstrapFile = ltrim($bootstrapFile, '/');
    }

    /**
     * @param Compiler $compiler
     * @param bool $cleanupBuildDirectory
     */
    public function install(Compiler $compiler, $cleanupBuildDirectory = false)
    {
        if($cleanupBuildDirectory) {
            $walker = new RecursiveWalker($this->buildDirectory);

            foreach($walker->getIterator() as $file) {
                @unlink((string) $file);
            }
        }

        $contentDirectory = sprintf('%s/%s', $this->buildDirectory, $this->contentPath);
        $bootstrapFilePath = sprintf('%s/%s', $this->buildDirectory, $this->bootstrapFile);

        if (!is_dir($contentDirectory) && !mkdir($contentDirectory)) {
            throw new InstallationException("Unable to create content directory");
        }

        $htaccessContent = file_get_contents($this->htaccessTemplate);

        if(false === $htaccessContent) {
            throw new InstallationException("Unable to read htaccess template");
        }

        $htaccessContent = preg_replace(
            '/\${\s*BOOTSTRAP_SCRIPT\s*}/ui',
            $this->bootstrapFile,
            $htaccessContent
        );

        $htaccessContent = preg_replace(
            '/\${\s*CONTENT_PATH\s*}/ui',
            $this->contentPath,
            $htaccessContent
        );

        if(!file_put_contents(sprintf('%s/.htaccess', $this->buildDirectory), $htaccessContent, LOCK_EX)) {
            throw new InstallationException("Unable to persist htaccess file");
        }

        if (!empty($this->defaultContentDirectory)) {
            if (empty($this->defaultContentDirectory)) {
                throw new InstallationException("Missing link directory");
            }

            $filesIterator = (new RecursiveWalker($this->defaultContentDirectory))->getIterator();

            /** @var \SplFileObject $fileObject */
            foreach ($filesIterator as $fileObject) {
                $relativePath = $this->getRelativePath((string) $fileObject, $this->defaultContentDirectory);

                if (!copy((string) $fileObject, sprintf('%s/%s', $contentDirectory, $relativePath))) {
                    throw new InstallationException(sprintf(
                        "Unable to copy '%s' into content directory",
                        $relativePath
                    ));
                }
            }
        }

        if (!file_put_contents($bootstrapFilePath, $compiler->compile(), LOCK_EX)) {
            throw new InstallationException("Unable to persist bootstrap file");
        }
    }

    /**
     * @param string $absolutePath
     * @param string $pathPrefix
     * @return string
     */
    protected function getRelativePath($absolutePath, $pathPrefix)
    {
        return preg_replace(sprintf('/^%s\/?([^\/]+)$/ui', preg_quote($pathPrefix, '/')), '$1', $absolutePath);
    }
}
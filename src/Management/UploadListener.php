<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/30/15
 * Time: 12:30
 */

namespace MicronCMS\Management;

use Doctrine\Common\Inflector\Inflector;
use MicronCMS\AbstractCompilable;
use MicronCMS\Application;
use MicronCMS\Helper\Hook;
use MicronCMS\HttpKernel\Exception\UploadFailedException;
use MicronCMS\HttpKernel\Request;
use MicronCMS\HttpKernel\Response;
use MicronCMS\Management\Exception\Exception;


/**
 * Class UploadListener
 * @package MicronCMS\Management
 */
class UploadListener extends AbstractCompilable
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var bool
     */
    protected $isListening = false;

    /**
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * @return boolean
     */
    public function isListening()
    {
        return $this->isListening;
    }

    /**
     * @param boolean $isListening
     * @return $this
     */
    public function setIsListening($isListening)
    {
        $this->isListening = $isListening;
        return $this;
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param Application $application
     * @return $this
     */
    public function setApplication($application)
    {
        $this->application = $application;
        return $this;
    }

    /**
     * @return void
     */
    public function listen()
    {
        if ($this->isListening) {
            throw new Exception("Upload listener is already listening");
        }

        $this->application->addHook(Application::BEFORE, [$this, 'manage']);
    }

    /**
     * @param Hook $hook
     * @param Application $application
     * @param Response|null $response
     * @param Request $request
     */
    public function manage(Hook $hook, Application $application, & $response, Request $request)
    {
        $files = $request->getFiles();

        if (!empty($files)) {
            $contentPath = $application->getContentDirectory();

            $filePath = $request->get('path');
            $filePath = is_array($filePath) ? $filePath : array_fill(0, count($files), $filePath);

            foreach ($files as $i => $file) {
                if (!isset($filePath[$i])) {
                    $filePath[$i] = sprintf('%s/%s', $contentPath, $file->getName());
                }

                $fileToPersist = static::cleanupFilePath($filePath[$i]);

                if (!$file->move($fileToPersist, true)) {
                    throw new UploadFailedException("Unable to upload file due to some unknown reasons");
                }
            }
        }
    }

    /**
     * @param string $filePath
     * @return string
     */
    protected static function cleanupFilePath($filePath)
    {
        $directory = dirname($filePath);
        $directory = preg_replace('/\.+/ui', '', $directory);
        $directory = preg_replace('/\/+/ui', '/', $directory);

        $fileName = basename($filePath);
        $extension = '.txt';

        if (preg_match('/^(?<name>.+)(?P<extension>\.[a-z0-9]+)$/ui', $fileName, $matches)) {
            $fileName = $matches['name'];
            $extension = $matches['extension'];
        }

        $fileName = str_replace('_', '-', Inflector::tableize($fileName));

        return sprintf('%s/%s%s', $directory, $fileName, $extension);
    }

    /**
     * @return array
     */
    public static function compileDependencies()
    {
        return [
            Inflector::class
        ];
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/30/15
 * Time: 12:30
 */

namespace MicronCMS\Management;

use Doctrine\Common\Inflector\Inflector;
use MicronCMS\Application;
use MicronCMS\ApplicationInterface;
use MicronCMS\CompilableInterface;
use MicronCMS\Helper\CompilableDefaults;
use MicronCMS\Helper\Hook;
use MicronCMS\HttpKernel\Exception\UploadFailedException;
use MicronCMS\HttpKernel\Request;
use MicronCMS\HttpKernel\Response;
use MicronCMS\Management\Exception\Exception;
use MicronCMS\Security\Firewall;
use MicronCMS\Security\Policy\PolicyInterface;
use MicronCMS\Security\Rule;


/**
 * Class UploadListener
 * @package MicronCMS\Management
 */
class UploadListener implements CompilableInterface
{
    use CompilableDefaults;

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var bool
     */
    protected $isListening = false;

    /**
     * @var string
     */
    protected $pathToListen;

    /**
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * @param PolicyInterface $policy
     * @return $this
     */
    public function secure(PolicyInterface $policy)
    {
        $firewall = new Firewall();
        $firewall->addRule(new Rule(empty($this->pathToListen) ? '.*' : $this->pathToListen));
        $firewall->addPolicy($policy);

        $this->application->addHook(
            ApplicationInterface::BEFORE,
            function(Hook $hook, Application $application, & $response, Request $request) use ($firewall) {
                if ($this->isUploadRequest($request) && Firewall::DENIED === $firewall->decide($request)) {
                    $response = $application->createNotAuthorizedResponse('Wrong or expired OTP token provided');
                    $hook->setStopped(true);
                }
            },
            PHP_INT_MAX
        );

        return $this;
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
     * @param string $pathToListen
     * @return $this
     */
    public function listen($pathToListen = null)
    {
        if ($this->isListening) {
            throw new Exception("Upload listener is already listening");
        }

        $this->pathToListen = $pathToListen;

        $this->application->addHook(Application::BEFORE, [$this, 'manage']);
        return $this;
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function isUploadRequest(Request $request)
    {
        return empty($this->pathToListen)
            ? !empty($request->getFiles())
            : $request->matchPath($this->pathToListen) && !empty($request->getFiles());
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

        if ($this->isUploadRequest($request)) {
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
     * @param Request $request
     * @param string $contentPath
     * @param string $filePath
     * @return string
     */
    protected static function generateFileUrl(Request $request, $contentPath, $filePath)
    {
        $path = mb_substr($filePath, mb_strlen($contentPath));

        if (preg_match('/^(?<path>.+)(\.[a-z0-9]+)$/ui', $path, $matches)) {
            $path = $matches['path'];
        }

        return sprintf('%s/%s', $request->getBaseUrl(), $path);
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
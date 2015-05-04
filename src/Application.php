<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/15/15
 * Time: 18:37
 */

namespace MicronCMS;

use MicronCMS\Exception\ApplicationException;
use MicronCMS\Exception\Exception;
use MicronCMS\Exception\MissingTemplateException;
use MicronCMS\FileSystem\PathResolver;
use MicronCMS\Helper\CompilableDefaults;
use MicronCMS\Helper\HookableTrait;
use MicronCMS\HttpKernel\Request;
use MicronCMS\HttpKernel\Response;
use MicronCMS\Templating\AbstractTemplate;


/**
 * Class Application
 * @package MicronCMS
 */
class Application implements ApplicationInterface, CompilableInterface
{
    use CompilableDefaults;
    use HookableTrait;

    /**
     * @var string
     */
    protected $contentDirectory;

    /**
     * @var PathResolver
     */
    protected $resolver;

    /**
     * @var bool
     */
    protected $cache = false;

    /**
     * @var array
     */
    protected $templateGlobals = [];

    /**
     * @param string $contentDirectory
     */
    public function __construct($contentDirectory)
    {
        $this->contentDirectory = realpath($contentDirectory);

        if (empty($this->contentDirectory)) {
            throw new ApplicationException("Missing content directory");
        }

        $this->resolver = new PathResolver($this->contentDirectory);

        $this->initializeHooks();


        $this->addDefaultTemplateGlobals();
    }

    /**
     * @return $this
     */
    protected function addDefaultTemplateGlobals()
    {
        $this
            ->setTemplateGlobal('_application', $this)
            ->setTemplateGlobal('_request', null);

        return $this;
    }

    /**
     * @return PathResolver
     */
    public function getResolver()
    {
        return $this->resolver;
    }

    /**
     * @return boolean
     */
    public function isCache()
    {
        return $this->cache;
    }

    /**
     * @param boolean $cache
     * @return $this
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setTemplateGlobal($name, $value)
    {
        $this->templateGlobals[$name] = $value;
        return $this;
    }

    /**
     * @param string $templateRelativePath
     * @param array $variables
     * @return string
     */
    public function render($templateRelativePath, array $variables = [])
    {
        $templateFile = $this->resolver->resolve($templateRelativePath);

        if ($templateFile) {
            $template = AbstractTemplate::create($templateFile);
            $template->setVariables(array_merge($this->templateGlobals, $variables));

            return $template->warmUp(
                $this->cache ? $template->cache() : $template->compile()
            );
        }

        throw new MissingTemplateException(sprintf("Template %s not found", $templateRelativePath));
    }

    /**
     * @param string $message
     * @return Response
     */
    public function createNotAuthorizedResponse($message = null)
    {
        if (!empty($message)) {
            return new Response($message, Response::ACCESS_DENIED);
        }

        try {
            return new Response($this->render('_403'), Response::ACCESS_DENIED);
        } catch (Exception $e) {    }

        return new Response('Not authorized', Response::ACCESS_DENIED);
    }

    /**
     * @return Response
     */
    public function createErrorResponse()
    {
        try {
            return new Response($this->render('_500'), Response::ERROR);
        } catch (Exception $e) {    }

        return new Response('Internal server error', Response::ERROR);
    }

    /**
     * @return Response
     */
    public function createNotFoundResponse()
    {
        try {
            return new Response($this->render('_404'), Response::NOT_FOUND);
        } catch (Exception $e) {    }

        return new Response('Page not found', Response::NOT_FOUND);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function dispatch(Request $request = null)
    {
        $request = $request ?: Request::createFromGlobals();
        $path = $request->getPath();

        $this->setTemplateGlobal('_request', $request);

        $earlyResponse = null;

        $this->triggerHooks(self::BEFORE, [$this, &$earlyResponse, $request]);

        if (is_object($earlyResponse) && $earlyResponse instanceof Response) {
            $this->triggerHooks(self::AFTER, [$this, &$earlyResponse, $request]);

            return $earlyResponse;
        }

        try {
            $response = new Response($this->render($path), Response::SUCCESS);
        } catch(MissingTemplateException $e) {
            $response = $this->createNotFoundResponse();
        } catch (Exception $e) {
            $response = $this->createErrorResponse();

            $this->triggerHooks(self::ERROR, [$this, &$response, $request, $e]);
        }

        $this->triggerHooks(self::AFTER, [$this, &$response, $request]);

        return $response;
    }

    /**
     * @return string
     */
    public function getContentDirectory()
    {
        return $this->contentDirectory;
    }
}
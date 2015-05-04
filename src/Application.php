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
use MicronCMS\FileSystem\PathResolver;
use MicronCMS\Helper\HookableTrait;
use MicronCMS\HttpKernel\Request;
use MicronCMS\HttpKernel\Response;
use MicronCMS\Templating\AbstractTemplate;


/**
 * Class Application
 * @package MicronCMS
 */
class Application extends AbstractCompilable implements ApplicationInterface
{
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
     * @return Response
     */
    public function createErrorResponse()
    {
        $templateFile = $this->resolver->resolve('_500');

        if ($templateFile) {
            $template = AbstractTemplate::create($templateFile);

            $compiledContent = $this->cache ? $template->cache() : $template->compile();

            return new Response($compiledContent, Response::ERROR);
        }

        return new Response('Internal server error', Response::ERROR);
    }

    /**
     * @return Response
     */
    public function createNotFoundResponse()
    {
        $templateFile = $this->resolver->resolve('_404');

        if ($templateFile) {
            $template = AbstractTemplate::create($templateFile);

            $compiledContent = $this->cache ? $template->cache() : $template->compile();

            return new Response($compiledContent, Response::NOT_FOUND);
        }

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

        $earlyResponse = null;

        $this->triggerHooks(self::BEFORE, [$this, &$earlyResponse, $request]);

        if (is_object($earlyResponse) && $earlyResponse instanceof Response) {
            $this->triggerHooks(self::AFTER, [$this, &$earlyResponse, $request]);

            return $earlyResponse;
        }

        try {
            $templateFile = $this->resolver->resolve($path);

            if (null === $templateFile) {
                $response = $this->createNotFoundResponse();
            } else {
                $template = AbstractTemplate::create($templateFile);

                $compiledContent = $this->cache ? $template->cache() : $template->compile();

                $response = new Response($compiledContent, Response::SUCCESS);

                $this->triggerHooks(self::AFTER, [$this, &$response, $request]);
            }
        } catch (Exception $e) {
            $response = $this->createErrorResponse();

            $this->triggerHooks(self::ERROR, [$this, &$response, $request, $e]);
        }

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
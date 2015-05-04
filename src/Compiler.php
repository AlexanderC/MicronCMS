<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/15/15
 * Time: 17:37
 */

namespace MicronCMS;

use MicronCMS\Exception\CompilationFailedException;
use MicronCMS\FileSystem\RecursiveWalker;


/**
 * Class Compiler
 * @package MicronCMS
 */
class Compiler
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var bool
     */
    protected $minify = true;

    /**
     * @var array
     */
    protected $extensions = [];

    /**
     * @var string
     */
    protected $prependFile;

    /**
     * @var string
     */
    protected $appendFile;

    /**
     * @param string $path
     * @param string $extensions
     */
    public function __construct($path, $extensions = 'php|inc|phtml|php5')
    {
        $this->path = realpath($path);

        if (empty($this->path)) {
            throw new CompilationFailedException("No such compile path exists");
        }

        $this->extensions = array_map(function ($extension) {
            return 0 === strpos($extension, '.') ? $extension : sprintf('.%s', $extension);
        }, explode('|', preg_replace('/\s+/ui', '', $extensions)));

        if (empty($this->extensions)) {
            throw new CompilationFailedException("No extensions to be compiled");
        }
    }

    /**
     * @return string
     */
    public function getPrependFile()
    {
        return $this->prependFile;
    }

    /**
     * @param string $prependFile
     * @return $this
     */
    public function setPrependFile($prependFile)
    {
        $this->prependFile = $prependFile;
        return $this;
    }

    /**
     * @return string
     */
    public function getAppendFile()
    {
        return $this->appendFile;
    }

    /**
     * @param string $appendFile
     * @return $this
     */
    public function setAppendFile($appendFile)
    {
        $this->appendFile = $appendFile;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * @return boolean
     */
    public function isMinify()
    {
        return $this->minify;
    }

    /**
     * @param boolean $minify
     * @return $this
     */
    public function setMinify($minify)
    {
        $this->minify = $minify;
        return $this;
    }

    /**
     * @return string
     */
    public function compile()
    {
        $compiledParts = [];
        $compiledPartsGrouped = [];
        $compilationStack = [];

        /** @var \SplFileInfo $file */
        foreach ($this->files() as $file) {
            require_once((string)$file);
        }

        /** @var CompilableInterface $declaredClass */
        foreach (get_declared_classes() as $declaredClass) {
            if (is_subclass_of($declaredClass, CompilableInterface::class)
                || is_subclass_of($declaredClass, \Exception::class)
            ) {
                $reflectionClass = new \ReflectionClass($declaredClass);

                $this->compileRecursive($reflectionClass, $compiledPartsGrouped, $compilationStack);
            }
        }

        foreach ($compiledPartsGrouped as list($namespace, $compiledClass)) {
            $compiledParts[] = sprintf(
                "namespace %s\n{\n    %s\n}",
                $namespace,
                preg_replace("/\n/ui", "\n    ", $compiledClass)
            );
        }

        $code = implode("\n", $compiledParts);

        if (isset($this->prependFile)) {
            $prependContent = file_get_contents($this->prependFile);

            if (false === $prependContent) {
                throw new CompilationFailedException("Unable to read prepend file");
            }

            $code = sprintf(
                "namespace\n{\n%s\n}\n%s",
                $this->addSpacingLevel($this->removePhpTags($prependContent), 1),
                $code
            );
        }

        if (isset($this->appendFile)) {
            $appendContent = file_get_contents($this->appendFile);

            if (false === $appendContent) {
                throw new CompilationFailedException("Unable to read append file");
            }

            $code = sprintf(
                "%s\nnamespace\n{\n%s\n}\n",
                $code,
                $this->addSpacingLevel($this->removePhpTags($appendContent), 1)
            );
        }

        $code = sprintf("<?php\n%s", $code);

        return $this->minify ? $this->minifySource($code) : $code;
    }

    /**
     * @param string $code
     * @param int $level
     * @return mixed
     */
    protected function addSpacingLevel($code, $level)
    {
        $spaces = str_repeat("    ", (int)$level);

        $code = $spaces . ltrim($code);

        return str_replace("\n", sprintf("\n%s", $spaces), $code);
    }

    /**
     * @param string $code
     * @return string
     */
    protected function removePhpTags($code)
    {
        return preg_replace('/^\s*(<\?(php)?|\?>)\s*/mui', '', $code);
    }

    /**
     * @param string $code
     * @return string
     */
    protected function minifySource($code)
    {
        $tmpFile = tempnam(sys_get_temp_dir(), md5(static::class));

        if (!file_put_contents($tmpFile, $code, LOCK_EX)) {
            throw new CompilationFailedException("Unable to persist temporary file for minification purposes");
        }

        $minifiedCode = php_strip_whitespace($tmpFile);

        @unlink($tmpFile);

        return $minifiedCode;
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @param array $compiledPartsGrouped
     * @param array $compilationStack
     */
    protected function compileRecursive(
        \ReflectionClass $reflectionClass,
        array & $compiledPartsGrouped,
        array & $compilationStack
    ) {
        if ($reflectionClass->isInternal() || $reflectionClass->implementsInterface(NonCompilableInterface::class)) {
            return;
        }

        /** @var CompilableInterface $className */
        $className = $reflectionClass->getName();

        if (!in_array($className, $compilationStack)) {
            $compilationStack[] = $className;

            $isCompilable = $reflectionClass->isInstantiable()
                && is_subclass_of($className, CompilableInterface::class);

            $namespace = '';

            if ($isCompilable) {
                $className::preCompile($reflectionClass);
            }

            $compiledClass = $this->compileClass($reflectionClass, $namespace);

            if ($isCompilable) {
                $className::postCompile($compiledClass);
            }

            $workingReflectionClass = $reflectionClass;

            while ($parentReflectionClass = $workingReflectionClass->getParentClass()) {
                $this->compileRecursive($parentReflectionClass, $compiledPartsGrouped, $compilationStack);

                $workingReflectionClass = $parentReflectionClass;
            }

            foreach ($reflectionClass->getInterfaces() as $reflectionInterface) {
                $this->compileRecursive($reflectionInterface, $compiledPartsGrouped, $compilationStack);
            }

            foreach ($reflectionClass->getTraits() as $reflectionTrait) {
                $this->compileRecursive($reflectionTrait, $compiledPartsGrouped, $compilationStack);
            }

            if (is_subclass_of($className, CompilableInterface::class)
                && !$reflectionClass->isInterface()
            ) {
                foreach ($className::compileDependencies() as $dependencyClass) {
                    $this->compileRecursive(
                        new \ReflectionClass($dependencyClass),
                        $compiledPartsGrouped,
                        $compilationStack
                    );
                }
            }

            $compiledPartsGrouped[] = [$namespace, $compiledClass];
        }
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @param string $namespace
     * @return string
     */
    protected function compileClass(\ReflectionClass $reflectionClass, & $namespace)
    {
        $classCode = '';
        $classNamespace = $reflectionClass->getNamespaceName();
        $namespace = $classNamespace ?: $namespace;

        $endLine = $reflectionClass->getEndLine();
        $startLine = $endLine;

        $file = fopen($reflectionClass->getFileName(), 'r');

        if (!$file) {
            throw new CompilationFailedException(sprintf(
                "Unable to open '%s' file for compilation",
                $reflectionClass->getFileName()
            ));
        }

        $usesRegexp = '/^\s*use\s+\\\?(?P<class>[^\s]+)(\s+as\s+(?P<alias>[^\s]+))?\s*;\s*$/ui';

        $startLineRegexp = sprintf(
            '/^\s*(abstract\s*)?(trait|class|interface)\s*%s\s*(extends\s*[^\s]+\s*)?(implements\s*[^\s]+\s*(?:,\s*[^\s]+)*\s*)?({.*)?$/ui',
            preg_quote($reflectionClass->getShortName(), '/')
        );

        $line = 0;
        $uses = [];

        while (false !== ($buffer = fgets($file, 4096))) {
            if ($startLine === $endLine && preg_match($startLineRegexp, $buffer)) {
                $startLine = $line;
            }

            if ($line >= $startLine && $line <= $endLine) {
                $classCode .= $buffer;
            } elseif (preg_match($usesRegexp, $buffer, $matches)) {
                $realClass = $matches['class'];
                $alias = isset($matches['alias']) ? $matches['alias'] : basename(str_replace('\\', '/', $realClass));

                $uses[$alias] = $realClass;
            }

            $line++;
        }

        fclose($file);

        foreach ($uses as $alias => $realClass) {
            $classCode = preg_replace(
                sprintf('/([^[:alnum:]]+use\s+)%s(\s*(?:{[^}]*}\s*)?;)/mui', preg_quote($alias)),
                sprintf('$1\%s$2', $realClass),
                $classCode
            );

            $classCode = preg_replace(
                sprintf('/(\s+|\()(%s)(\s*(?:\(|::|\s+|\)))/mui', preg_quote($alias)),
                sprintf('$1\%s$3', $realClass),
                $classCode
            );

            $classCode = preg_replace(
                sprintf('/(\s+extends\s+)(%s)(\s*implements\s+[^\s]+)?(\s*{)/mui', preg_quote($alias)),
                sprintf('$1\%s$3$4', $realClass),
                $classCode
            );
        }

        return $classCode;
    }

    /**
     * @return \Traversable
     */
    protected function files()
    {
        return (new RecursiveWalker($this->path, $this->buildExtensionsRegexp()))
            ->getIterator();
    }

    /**
     * @return string
     */
    protected function buildExtensionsRegexp()
    {
        $escapedExtensions = array_map(function ($extension) {
            return preg_quote($extension, '/');
        }, $this->extensions);

        return sprintf('/^.+(%s)$/ui', implode('|', $escapedExtensions));
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/15/15
 * Time: 20:13
 */

namespace MicronCMS\FileSystem;

use MicronCMS\CompilableInterface;
use MicronCMS\FileSystem\Exception\Exception;
use MicronCMS\Helper\CompilableDefaults;
use Traversable;


/**
 * Class Walker
 * @package MicronCMS\FileSystem
 */
class Walker implements \IteratorAggregate, CompilableInterface
{
    use CompilableDefaults;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $filterRegexp;

    /**
     * @param string $path
     * @param string $filterRegexp
     */
    public function __construct($path, $filterRegexp = null)
    {
        $this->path = realpath($path);

        if (empty($this->path)) {
            throw new Exception("Missing path to walk through");
        }

        $this->filterRegexp = $filterRegexp;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        $iterator = $this->getInternalIterator();
        $iterator->rewind();

        while ($iterator->valid()) {
            if (empty($this->filterRegexp) || preg_match($this->filterRegexp, (string)$iterator->current())) {
                yield $iterator->current();
            }

            $iterator->next();
        }
    }

    /**
     * @return \FilesystemIterator
     */
    protected function getInternalIterator()
    {
        return new \FilesystemIterator($this->path, \FilesystemIterator::SKIP_DOTS);
    }
}
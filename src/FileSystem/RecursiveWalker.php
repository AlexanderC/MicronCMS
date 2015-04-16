<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/15/15
 * Time: 20:13
 */

namespace MicronCMS\FileSystem;

use MicronCMS\AbstractCompilable;
use MicronCMS\FileSystem\Exception\Exception;
use Traversable;


/**
 * Class RecursiveWalker
 * @package MicronCMS\FileSystem
 */
class RecursiveWalker extends AbstractCompilable implements \IteratorAggregate
{
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

        if(empty($this->path)) {
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
        $rdi = new \RecursiveDirectoryIterator($this->path, \RecursiveDirectoryIterator::SKIP_DOTS);
        $rii = new \RecursiveIteratorIterator($rdi);

        $rii->rewind();

        while ($rii->valid()) {
            if (empty($this->filterRegexp) || preg_match($this->filterRegexp, (string) $rii->current())) {
                yield $rii->current();
            }

            $rii->next();
        }
    }
}
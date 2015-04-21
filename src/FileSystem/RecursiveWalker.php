<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/15/15
 * Time: 20:13
 */

namespace MicronCMS\FileSystem;


/**
 * Class RecursiveWalker
 * @package MicronCMS\FileSystem
 */
class RecursiveWalker extends Walker
{
    /**
     * @return \FilesystemIterator
     */
    protected function getInternalIterator()
    {
        $rdi = new \RecursiveDirectoryIterator($this->path, \RecursiveDirectoryIterator::SKIP_DOTS);

        return new \RecursiveIteratorIterator($rdi);
    }
}
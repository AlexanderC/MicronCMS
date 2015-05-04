<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 4/30/15
 * Time: 11:23
 */

namespace MicronCMS\HttpKernel;

use MicronCMS\CompilableInterface;
use MicronCMS\Helper\CompilableDefaults;
use MicronCMS\HttpKernel\Exception\UploadFailedException;


/**
 * Class File
 * @package MicronCMS\HttpKernel
 */
class File implements CompilableInterface
{
    use CompilableDefaults;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $mimeType;

    /**
     * @var string
     */
    protected $temporaryPath;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var int
     */
    protected $size;

    /**
     * @param string $name
     * @param string $mimeType
     * @param string $temporaryPath
     * @param int $status
     * @param int $size
     */
    public function __construct($name, $mimeType, $temporaryPath, $status, $size)
    {
        $this->name = $name;
        $this->mimeType = $mimeType;
        $this->temporaryPath = $temporaryPath;
        $this->status = $status;
        $this->size = $size;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @return string
     */
    public function getTemporaryPath()
    {
        return $this->temporaryPath;
    }

    /**
     * @return bool
     */
    public function isOk()
    {
        return $this->status === UPLOAD_ERR_OK;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return !$this->isOk();
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->size <= 0;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return bool
     */
    public function isUploaded()
    {
        return is_uploaded_file($this->getTemporaryPath());
    }

    /**
     * @param string $path
     * @param bool $createDirectoryIfMissing
     * @return bool
     */
    public function move($path, $createDirectoryIfMissing = false)
    {
        if ($this->isError()) {
            throw new UploadFailedException(sprintf("Upload error %d on file %s", $this->status, $this->getName()));
        } elseif (!$this->isUploaded()) {
            throw new UploadFailedException(sprintf("File %s is not an uploaded one", $this->temporaryPath));
        }

        $directory = dirname($path);

        if (!is_dir($directory)) {
            if (!$createDirectoryIfMissing) {
                throw new UploadFailedException("Missing destination directory");
            }

            if (!mkdir($directory, 0777, true)) {
                throw new UploadFailedException("Unable to create destination directory");
            }
        }

        return move_uploaded_file($this->temporaryPath, $path);
    }

    /**
     * @return File[]
     */
    public static function createFromGlobals()
    {
        $collection = [];

        foreach ($_FILES as $file) {
            $collection = new static(
                empty($file['name']) ? 'Unknown' : $file['name'],
                empty($file['type']) ? 'plain/text' : $file['type'],
                $file['tmp_name'],
                (int) $file['error'],
                (int) $file['size']
            );
        }

        return $collection;
    }
}
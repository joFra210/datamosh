<?php


namespace App\Entity;


use Symfony\Component\HttpFoundation\File\UploadedFile;

class Upload
{
    protected $file;

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file): void
    {
        $this->file = $file;
    }

}

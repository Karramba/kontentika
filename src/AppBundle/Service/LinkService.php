<?php

namespace AppBundle\Service;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 *
 */
class LinkService
{
    private $kernelRootDir;

    public function __construct($kernelRootDir)
    {
        $this->kernelRootDir = $kernelRootDir;
    }

    /**
     * Downloads image and saves to storage
     *
     */
    public function downloadAndSaveThumbnail($thumbnailUrl, $uniqueId)
    {
        $imagesDir = $this->kernelRootDir . '/../web/uploads';

        if (filter_var($thumbnailUrl, FILTER_VALIDATE_URL) && $image = file_get_contents($thumbnailUrl)) {
            $path = sys_get_temp_dir() . "/" . $uniqueId;
            if (file_put_contents($path, $image)) {
                try {
                    $file = new File($path);
                    $extension = $file->guessExtension();
                    $newName = $uniqueId . "." . $extension;
                    $file->move($imagesDir, $newName);
                    return $newName;
                } catch (Exception $e) {
                    throw new HttpException(500, $e->getMessage());
                }
            }
        }
        return null;
    }

    public function isImageOnly($url)
    {
        $headers = @get_headers($url, 1);

        if (isset($headers['Content-Type']) && !is_array($headers['Content-Type'])) {
            if (strpos($headers['Content-Type'], 'image/') !== false) {
                return true;
            }
        }
        return false;
    }

}

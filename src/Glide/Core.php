<?php

namespace Glide;

abstract class Core
{
    private $sourceDirectoryPath;
    private $sourceImageFileName;
    protected $cropWidth;
    protected $cropHeight;
    protected $cropXPos;
    protected $cropYPos;
    protected $width;
    protected $height;
    protected $resizeMethod = 'max';
    protected $quality = 90;

    public function parseParamatersFromSlug($slug)
    {
        $segments = explode(',', $slug);

        foreach ($segments as $segment) {

            if (strpos($segment, 'filename_') === 0) {
                $this->setsourceImageFileName(substr($segment, 9));
            }

            if (strpos($segment, 'crop_ratio_') === 0) {
                $sizes = explode('_', substr($segment, 11));
                $this->setCropByRatio($sizes[0]/$sizes[1]);
            } else if (strpos($segment, 'crop_') === 0) {
                $sizes = explode('_', substr($segment, 5));
                $this->setCropByCoordinates($sizes[0], $sizes[1], $sizes[2], $sizes[3]);
            }

            if (strpos($segment, 'width_') === 0) {
                $this->setWidth(substr($segment, 6));
            }

            if (strpos($segment, 'height_') === 0) {
                $this->setHeight(substr($segment, 7));
            }

            if (strpos($segment, 'resize_') === 0) {
                $this->setResizeMethod(substr($segment, 7));
            }
        }

        return $this;
    }

    public function setSourceDirectoryPath($sourceDirectoryPath)
    {
        if (!is_dir($sourceDirectoryPath)) {
            throw new \LogicException('The source directory is not a valid.');
        }

        $this->sourceDirectoryPath = $sourceDirectoryPath;

        return $this;
    }

    public function setSourceImageFileName($sourceImageFileName)
    {
        $this->sourceImageFileName = $sourceImageFileName;

        return $this;
    }

    public function setQuality($quality)
    {
        $this->quality = $quality;

        return $this;
    }

    public function setCropByRatio($ratio)
    {
        // Get original image size
        $size = getimagesize($this->getSourceImagePath());

        $sourceWidth = $size[0];
        $sourceHeight = $size[1];

        // Set dimensions
        $cropWidth = $sourceWidth;
        $cropHeight = floor($sourceWidth / $ratio);

        // Update dimensions if out of document bounds
        if ($cropHeight > $sourceHeight) {
            $cropWidth = $sourceHeight * $ratio;
            $cropHeight = $sourceHeight;
        }

        $cropXPos = floor(($sourceWidth - $cropWidth) / 2);
        $cropYPos = floor(($sourceHeight - $cropHeight) / 2);

        // Build crop command
        $this->cropWidth = $cropWidth;
        $this->cropHeight = $cropHeight;
        $this->cropXPos = $cropXPos;
        $this->cropYPos = $cropYPos;

        return $this;
    }

    public function setCropByCoordinates($width, $height, $cropXPos, $cropYPos)
    {
        $this->cropWidth = $width;
        $this->cropHeight = $height;
        $this->cropXPos = $cropXPos;
        $this->cropYPos = $cropYPos;

        return $this;
    }

    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    public function setResizeMethod($resizeMethod)
    {
        if (!in_array($resizeMethod, array('max', 'min', 'force'))) {
            throw new \LogicException('Not a valid resize method');
        }

        $this->resizeMethod = $resizeMethod;

        return $this;
    }

    public function reset()
    {
        $this->crop = null;
        $this->width = null;
        $this->height = null;

        return $this;
    }

    public function outputImage()
    {
        $this->createImage();

        header('Content-Type: image/jpeg');
        header('Content-Length: ' . filesize($this->getOutputPath()));
        header('Expires: ' . gmdate('D, d M Y H:i:s', strtotime('+1 years')) . ' GMT', true);
        header('Cache-Control: public, max-age=31536000', true);

        readfile($this->getOutputPath());
        exit;
    }

    protected function getSourceDirectoryPath()
    {
        if (is_null($this->sourceDirectoryPath)) {
            throw new \LogicException('The source directory has not been set.');
        }

        if (!is_dir($this->sourceDirectoryPath)) {
            throw new \LogicException('The source directory is not a valid.');
        }

        return $this->sourceDirectoryPath;
    }

    protected function getSourceImagePath()
    {
        if (is_null($this->sourceImageFileName)) {
            throw new \LogicException('The source filename has not been set.');
        }

        $sourcePath = $this->getSourceDirectoryPath() . '/' . $this->sourceImageFileName;

        if (!is_file($sourcePath)) {
            throw new \LogicException('The source path is not valid.');
        }

        return $this->sourceDirectoryPath . '/' . $this->sourceImageFileName;
    }

    protected function getOutputPath()
    {
        $path = $this->getCacheDirectoryPath();
        $path .= '/';
        $path .= $this->sourceImageFileName;

        if ($this->cropWidth) {
            $path .= '_crop_' . $this->cropWidth;
            $path .= '_' . $this->cropHeight;
            $path .= '_' . $this->cropXPos;
            $path .= '_' . $this->cropYPos;
        }

        if ($this->width) {
            $path .= '_width_' . $this->width;
        }

        if ($this->height) {
            $path .= '_height_' . $this->height;
        }

        if ($this->width or $this->height) {
            $path .= '_resize_method_' . $this->resizeMethod;
        }

        $path .= '_quality_' . $this->quality;
        $path .= '.jpg';

        return $path;
    }

    protected function getCacheDirectoryPath()
    {
        return $this->getSourceDirectoryPath() . '/.cache';
    }

    protected function makeCacheDirectory()
    {
        if (!is_dir($this->getCacheDirectoryPath())) {
            mkdir($this->getCacheDirectoryPath());
        }
    }
}

<?php

namespace Glide;

class ImageMagick extends Core implements ProviderInterface
{
    private $convertPath;
    private static $resizeMethods = array(
        'max' => '',
        'min' => '^',
        'force' => '!'
    );

    public function __construct($convertPath = 'convert')
    {
        $this->setConvertPath($convertPath);
    }

    public function setConvertPath($convertPath)
    {
        $this->convertPath = $convertPath;

        return $this;
    }

    public function createImage()
    {
        $this->makeCacheDirectory();

        if (!file_exists($this->getOutputPath())) {
            exec($this->getConvertCommand());
        }

        return $this;
    }

    private function getConvertCommand()
    {
        // Convert command
        $convertCommand = $this->convertPath;

        // Auto-rotate and flatten
        $convertCommand .= ' -background white -flatten -auto-orient';

        // Source path
        $convertCommand .= ' ' . $this->getSourceImagePath();

        // Crop
        if ($this->cropWidth) {
            $convertCommand .= ' -extent ' . $this->cropWidth . 'x' . $this->cropHeight . '+' . $this->cropXPos . '+' . $this->cropYPos;
        }

        // Resize
        if (!is_null($this->width) and !is_null($this->height)) {
            $convertCommand .= ' -resize ' . $this->width . 'x' . $this->height . self::$resizeMethods[$this->resizeMethod];
        } elseif (!is_null($this->width) and is_null($this->height)) {
            $convertCommand .= ' -resize ' . $this->width . self::$resizeMethods[$this->resizeMethod];
        } elseif (is_null($this->width) and !is_null($this->height)) {
            $convertCommand .= ' -resize x' . $this->height . self::$resizeMethods[$this->resizeMethod];
        }

        // Image quality
        $convertCommand .= ' -quality ' . $this->quality;

        // Destination path
        $convertCommand .= ' ' . $this->getOutputPath();

        // Return command
        return $convertCommand;
    }
}

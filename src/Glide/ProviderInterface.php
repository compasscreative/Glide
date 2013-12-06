<?php

namespace Glide;

interface ProviderInterface
{
    public function parseParamatersFromSlug($slug);
    public function setSourceDirectoryPath($sourcePath);
    public function setSourceImageFileName($sourceImageFileName);
    public function setQuality($quality);
    public function setCropByRatio($ratio);
    public function setCropByCoordinates($width, $height, $cropXPos, $cropYPos);
    public function setWidth($width);
    public function setHeight($height);
    public function setResizeMethod($method);
    public function reset();
    public function createImage();
    public function outputImage();
}

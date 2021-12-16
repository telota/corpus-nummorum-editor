<?php

namespace App\Http\Controllers\storage;
use App\Http\Controllers\Controller;

//use Storage;
//use DB;
use Imagick;
use ImagickPixel;

class ImageHandler extends Controller {
    public function test () {
        return $this->createThumbnails(41) ? 'ok' : 'fail';
    }

    public function createPath ($path) {
        return '/opt/projects/corpus-nummorum/silo10/coins/'.$path.'/';
    }

    public function createThumbnails ($src, $transformation = null) {
        $path = $this->createPath($src);
        $full = $path.'full.tif';

        // Set Transformation Parameters
        $scale = intval(empty($transformation['scale']) ? 100 : $transformation['scale']);
        if ($scale === 0) $scale = 100;
        $rotate = intval(empty($transformation['rotate']) ? 0 : $transformation['rotate']);
        if ($rotate > 180 || $rotate < -180) $rotate = 0;
        $offsetX = intval(empty($transformation['offsetX']) ? 0 : $transformation['offsetX']);
        if ($offsetX > 100 || $offsetX < -100) $offsetX = 0;
        $offsetY = intval(empty($transformation['offsetY']) ? 0 : $transformation['offsetY']);
        if ($offsetY > 100 || $offsetY < -100) $offsetY = 0;

        // Get Background color
        $image = new Imagick($full);
        $colors = $this->getColors($image);
        $color = $colors['imagick'];
        $bg = $colors['rgb'];

        // Make Image square
        $image = $this->makeSquare($image, $color);

        // Rotate Image
        $image = $this->rotate($image, $color, $rotate);

        // Shift Image
        $image = $this->offset($image, $color, $offsetX, $offsetY);

        // Scale Image
        $image = $this->scale($image, $color, $scale);

        // write JPG Thumbnails
        $image = $this->writeJPEGs($path, $image, $color);

        // Write Transformation JSON
        $this->writeTransformation($path, $scale, $rotate, $offsetX, $offsetY, $bg);

        return ['success' => true];
    }

    public function getColors ($image) {
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();

        // Extract Cornerpixels
        $colors = [
            'tl' => $image->getImagePixelColor(1, 1)->getColor(),
            'tr' => $image->getImagePixelColor($width, 1)->getColor(),
            'br' => $image->getImagePixelColor($width, $height)->getColor(),
            'bl' => $image->getImagePixelColor(1, $width)->getColor()
        ];

        // Get Color Chanel values
        $rgb = [];
        foreach (['r', 'g', 'b'] as $key) {
            $rgb[$key] = array_column($colors, $key);
            $max = max($rgb[$key]);
            $min = min($rgb[$key]);
            $dif = abs($max - $min);

            if ($dif > 15) {
                $rgb = ['r' => 128, 'g' => 128, 'b' => 128];
                break;
            }
            else $rgb[$key] = round(array_sum($rgb[$key]) / 4);
        }

        $rgb = implode(',', $rgb);

        return [
            'imagick' => new ImagickPixel('rgba('.$rgb.',1)'),
            'rgb' => $rgb
        ];
    }


    public function makeSquare ($original, $color) {
        $width = $original->getImageWidth();
        $height = $original->getImageHeight();

        if ($width === $height) return $original;

        $size = $height > $width  ? $height : $width;
        $x = floor(($size - $width) / 2);
        $y = floor(($size - $height) / 2);

        $image = new Imagick();
        $image->newImage($size, $size, $color);

        $image->compositeImage($original, Imagick::COMPOSITE_COPY, $x, $y);

        return $image;
    }

    public function rotate ($original, $color, $rotate) {
        if ($rotate === 0) return $original;

        $originalSize = $original->getImageWidth();

        $original->rotateimage($color, $rotate);

        // Handle odd angles
        if ($rotate % 90 !== 0) {
            $image = new Imagick();
            $image->newImage($originalSize, $originalSize, $color);
            $offset = floor(($originalSize - $original->getImageWidth()) / 2);

            $image->compositeImage($original, Imagick::COMPOSITE_COPY, $offset, $offset);

            return $image;
        }
        else return $original;
    }

    public function offset ($original, $color, $x, $y) {
        if ($x === 0 && $y === 0) return $original;

        $size = $original->getImageWidth();

        $x = floor($size * $x / 100);
        $y = floor($size * $y / 100);

        $image = new Imagick();
        $image->newImage($size, $size, $color);

        $image->compositeImage($original, Imagick::COMPOSITE_COPY, $x, $y);

        return $image;
    }

    public function scale ($original, $color, $scale) {
        if ($scale === 100) return $original;

        $originalSize = $size = $original->getImageWidth();
        $original->scaleImage(round($size * $scale / 100), round($size * $scale / 100));
        $size = $original->getImageWidth();

        $offset = floor(($originalSize - $size) / 2);

        $image = new Imagick();
        $image->newImage($originalSize, $originalSize, $color);

        $image->compositeImage($original, Imagick::COMPOSITE_COPY, $offset, $offset);

        return $image;
    }

    public function writeJPEGs ($path, $original, $color) {
        foreach(config('dbi.settings.thumbnails') as $name => $size) {
            $image = new Imagick();
            $originalSize = $original->getImageWidth();
            $image->newImage($originalSize, $originalSize, $color);

            // Add data from original image
            $image->compositeImage($original, Imagick::COMPOSITE_COPY, 0, 0);
            $image->scaleImage($size, $size);

            // format and save as file
            $image->setImageFormat('jpeg');
            $image->writeImage($path.$name.'.jpeg');
        }
    }

    public function writeTransformation ($path, $scale, $rotate, $offsetX, $offsetY, $bg) {
        $transformation = [
            'scale' => $scale,
            'rotate' => $rotate,
            'offsetX' => $offsetX,
            'offsetY' => $offsetY,
            'background' => $bg
        ];
        $transformation = json_encode($transformation, JSON_UNESCAPED_UNICODE);
        file_put_contents($path.'transformation.json', $transformation);
    }
}

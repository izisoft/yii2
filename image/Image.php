<?php 

namespace izi\image;
use Yii; 
use Gregwar\Image\Image as GImage;

class Image extends \yii\base\Component
{
    
    public function showImage($path){
        
        $path = __DIR__ . '/sold.png';
        
        
        
        $imagick = new \Imagick(realpath($path));
        $draw = new \ImagickDraw();
        //$draw->setStrokeColor($strokeColor);
        //$draw->setFillColor($fillColor);
        
        $draw->setStrokeWidth(1);
        $draw->setFontSize(36);
        
        $text = "Imagick is a native php \nextension to create and \nmodify images using the\nImageMagick API.";
        
        //$draw->setFont("../fonts/Arial.ttf");
        $imagick->annotateimage($draw, 40, 40, 0, $text);
        
        header("Content-Type: image/jpg");
        echo $imagick->getImageBlob();
        
        exit;
//         
//         echo GImage::open($path)
        
//         ->sepia()
//         ->png();
    }
}
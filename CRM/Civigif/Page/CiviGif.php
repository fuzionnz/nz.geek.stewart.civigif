<?php
use CRM_Civigif_ExtensionUtil as E;

class CRM_Civigif_Page_CiviGif extends CRM_Core_Page {

  public function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(E::ts('CiviGif - Testing'));
    $filename = 'test.gif';
    $image_path = Civi::paths()->getPath('[cms.root]/sites/default/files/') . $filename;
    // Example: Assign a variable for use in a template
    $this->assign('image_path', '/sites/default/files/' . $filename);

    $handle = fopen($image_path,'w+');
    if (!($handle)) {
       kpr("Failed to open image file");
       kpr($handle);
    }
    else {
        $width = 1200;
        $height = 600;
        
        $draw = new ImagickDraw();
        $draw->setFillColor('black');
        $draw->setFont('Bookman-DemiItalic');
        $draw->setFontSize( 30 );

        $background = new ImagickPixel('transparent');
        
        $canvas = new Imagick();

        for ($i=0; $i< 10; $i++) {
            $frames[$i] = new Imagick();
            $frames[$i]->newImage($width,$height, $background);
            $frames[$i]->annotateImage($draw, 10, 45 + $i*40, 0, "Image $i");
            $frames[$i]->setImageFormat('gif');
            $frames[$i]->setImageDispose(0);
            $canvas->addImage($frames[$i]);
            $canvas->setImageDelay(50);
        }


        $canvas->setImageFormat('gif');
        $final_image = $canvas->coalesceImages();
        $final_image->setImageFormat('gif');
        $final_image->setImageIterations(0); //loop forever
        $final_image->mergeImageLayers(\Imagick::LAYERMETHOD_OPTIMIZEPLUS);
        $final_image->writeImages($image_path, TRUE);
    
        //imagedestroy($image);
    }
    
    parent::run();
  }

}

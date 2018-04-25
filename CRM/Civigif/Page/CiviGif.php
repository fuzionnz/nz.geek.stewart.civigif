<?php
use CRM_Civigif_ExtensionUtil as E;

class CRM_Civigif_Page_CiviGif extends CRM_Core_Page {


    /**
     * Generate an animated gif adding lines from $lines one by one.
     * 
     */
    private function generate_image( $lines){

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

            $background = new ImagickPixel('white');
        
            $canvas = new Imagick();

            foreach ($lines as $i => $line) {                
                $frames[$i] = new Imagick();
                $frames[$i]->newImage($width,$height, $background);
                $added = FALSE;
                for ($j = $i; $j>=0 && $i - $j < 5 ; $j--) {
                    $frames[$i]->annotateImage($draw, 10, 45 + ($i-$j)*40, 0, $lines[$j]);
                    $added = TRUE;
                }
                if ($added) {
                    $frames[$i]->setImageFormat('gif');                    
                    $canvas->addImage($frames[$i]);
                    $canvas->setImageDelay(50);
                }
            }

            $canvas->setImageFormat('gif');
            $final_image = $canvas->coalesceImages();
            $final_image->setImageFormat('gif');
            $final_image->setImageIterations(0); //loop forever
            $final_image->mergeImageLayers(\Imagick::LAYERMETHOD_OPTIMIZEPLUS);
            $final_image->writeImages($image_path, TRUE);
    

        }
    }

    public function run() {
        // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
        CRM_Utils_System::setTitle(E::ts('CiviGif - Testing'));


        $lines = ["zero","one", "two", "three", "four", "five","six", "seven","eight","nine", "ten", "eleven", "twelve", "thirteen", "fourteen"];
        $this::generate_image( $lines);
        parent::run();
    }

}

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
            $font_path = CRM_Core_Resources::singleton()->getPath('nz.geek.stewart.civigif') . "/fonts/RobotoCondensed-Light.ttf";
            $draw->setFont($font_path);
            $draw->setFontSize( 30 );

            $background = new ImagickPixel('white');
        
            $canvas = new Imagick();

            foreach ($lines as $i => $line) {                
                $frames[$i] = new Imagick();
                $frames[$i]->newImage($width,$height, $background);
                $added = FALSE;
                for ($j = $i; $j>=0 && $i - $j < 5 ; $j--) {
                    $frames[$i]->annotateImage($draw, 10, 45 + ($i-$j)*40, 0, "{$lines[$j]['name']} \${$lines[$j]['amount']} {$lines[$j]['time']} ago");
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

        //$this::generate_image();
        $result = civicrm_api3('Contribution', 'get', array(
            'sequential' => 1,
            'return' => array("total_amount", "receive_date"),
            'options' => array('limit' => 10),
            'api.Contact.getsingle' => array('return' => array("first_name")),
        ));
        //kpr(var_export($result));
        
        if ($result['is_error'] !=0) {
            die("Api Error");            
        }
        $lines = [];
        $now = new DateTime();
        foreach ($result['values'] as $contribution) {

            $time_since = $now->diff(new DateTime($contribution['receive_date']));
            
            $diff_str = [];
            
            $format_order = [
                ["y", "year"],
                ["m", "minute"],
                ["d", "day"],
                ["h", "hour"],
                ["i", "minute"],
                ["s", "second"],
            ];

            foreach ($format_order as $key) {
                $component = $time_since->{$key[0]};
                if ($component > 0) {                    
                    if ($component > 1){
                        $diff_str[]=  $time_since->{$key[0]} . " " . $key[1] ."s";
                    }                    
                    else {
                        $diff_str[]=  $time_since->{$key[0]} . " " . $key[1];
                    }                        
                }
                if (sizeof($diff_str) >= 2) {
                    break;
                }
            }

            $lines[] = [
                'name'=> $contribution['api.Contact.getsingle']['first_name'],
                'time' => implode(" ", $diff_str),
                'amount' => $contribution['total_amount'],
            ];
        }
        $this::generate_image( $lines);
        
        parent::run();
    }

}

<?php

class NoName_ImageTransparencyFix_Core_Model_File_Validator_Image extends Mage_Core_Model_File_Validator_Image
{

    /**
     * Validation callback for checking is file is image
     *
     * @param  string $filePath Path to temporary uploaded file
     * @return null
     * @throws Mage_Core_Exception
     */
    public function validate($filePath)
    {
        list($imageWidth, $imageHeight, $fileType) = getimagesize($filePath);
        if ($fileType) {
            if ($this->isImageType($fileType)) {
                //replace tmp image with re-sampled copy to exclude images with malicious data
                $image = imagecreatefromstring(file_get_contents($filePath));
                if ($image !== false) {
                    $img = imagecreatetruecolor($imageWidth, $imageHeight);

                    switch ($fileType) {
                        case IMAGETYPE_GIF:
                            imagecopyresampled($img, $image, 0, 0, 0, 0, $imageWidth, $imageHeight, $imageWidth, $imageHeight);
                            imagegif($img, $filePath);
                            break;
                        case IMAGETYPE_JPEG:
                            imagecopyresampled($img, $image, 0, 0, 0, 0, $imageWidth, $imageHeight, $imageWidth, $imageHeight);
                            imagejpeg($img, $filePath, 100);
                            break;
                        case IMAGETYPE_PNG:
                            imagecolortransparent($img, imagecolorallocatealpha($img, 0, 0, 0, 127));
                            imagealphablending($img, false);
                            imagesavealpha($img, true);
                            imagecopyresampled($img, $image, 0, 0, 0, 0, $imageWidth, $imageHeight, $imageWidth, $imageHeight);
                            imagepng($img, $filePath);
                            break;
                        default:
                            return;
                    }
                    
                    imagedestroy($img);
                    imagedestroy($image);
                    
                    return null;
                } else {
                    throw Mage::exception('Mage_Core', Mage::helper('core')->__('Invalid image.'));
                }
            }
        }
        
        throw Mage::exception('Mage_Core', Mage::helper('core')->__('Invalid MIME type.'));
    }

}

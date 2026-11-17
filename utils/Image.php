<?php

    class Image{
       
        public static function generate_picture_name(string $name, bool $file = false) : string {
            // $types = [
            //     'image/gif'  => 'gif',
            //     'image/jpeg' => 'jpg',
            //     'image/png'  => 'png',
            //     'image/webp' => 'webp'
            // ];

            // if (!isset($types[$file['type']])) {
            //     throw new Exception("Unsupported image type");
            // }
            if($file){
                $baseName = pathinfo($name, PATHINFO_FILENAME); // nom sans extension
                return $baseName . '_' . time();
            }

            return $name . '_' . time();
        }
        public static function validate_picture(array $file): array {
            $errors = [];
            $maxSize = 5 * 1024 * 1024;
            
            if (isset($file['name']) && $file['name'] != '') {                
                if ($file['error'] == 0) {
                    $valid_types = ["image/gif", "image/jpeg", "image/png", "image/webp"];
                    if (!in_array($file['type'], $valid_types)) {
                        $errors[] = "Unsupported image format : gif, jpg/jpeg, png or WEBP.";
                    }
                    if($file["size"] > $maxSize){
                        $errors[] = "Image is too large. Maximum allowed size is 5 MB.";
                    }
                } else {
                    
                    $errors[] = "Error while uploading file.";
                }
            }
            return $errors;
        }

    }


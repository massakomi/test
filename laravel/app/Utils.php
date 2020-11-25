<?php

namespace App;

class Utils {

    public static function log($txt)
    {
        fwrite($a = fopen('log.txt', 'a+'), "\n".date('Y-m-d H:i:s').' '.$txt); fclose($a);
    }

    /*public function initUploader()
    {
        

    }*/

    /**
     * Расширение файла
     */
    public static function extension($filename) {
        $name_explode = explode('.', $filename);
        $extension = mb_strtolower($name_explode[count($name_explode) - 1]);
        return $extension;
    }

    /**
     * Транслитирует слово
     */
    public static function translit($var, $lower = true, $punkt = true) {
        if ( is_array($var) ) return "";
        // $var = mb_strtolower($var);
       $langtranslit = array(
        'а' => 'a', 'б' => 'b', 'в' => 'v',
        'г' => 'g', 'д' => 'd', 'е' => 'e',
        'ё' => 'e', 'ж' => 'zh', 'з' => 'z',
        'и' => 'i', 'й' => 'y', 'к' => 'k',
        'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r',
        'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'c',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
        'ь' => '', 'ы' => 'y', 'ъ' => '',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        "ї" => "yi", "є" => "ye",

        'А' => 'A', 'Б' => 'B', 'В' => 'V',
        'Г' => 'G', 'Д' => 'D', 'Е' => 'E',
        'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',
        'И' => 'I', 'Й' => 'Y', 'К' => 'K',
        'Л' => 'L', 'М' => 'M', 'Н' => 'N',
        'О' => 'O', 'П' => 'P', 'Р' => 'R',
        'С' => 'S', 'Т' => 'T', 'У' => 'U',
        'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
        'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
        'Ь' => '', 'Ы' => 'Y', 'Ъ' => '',
        'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
        "Ї" => "yi", "Є" => "ye",
        );

        $var = trim( strip_tags( $var ) );
        if ( $lower ) $var = mb_strtolower( $var );
        $var = preg_replace( "/\s+/ms", "-", $var );

        $var = strtr($var, $langtranslit);

        if ( $punkt ) $var = preg_replace( "/[^a-z0-9\_\-.]+/mi", "", $var );
        else $var = preg_replace( "/[^a-z0-9\_\-]+/mi", "", $var );

        $var = preg_replace( '#[\-]+#i', '-', $var );


        $var = str_ireplace( ".php", "", $var );
        $var = str_ireplace( ".php", ".ppp", $var );

        return $var;
    }


    /**
    * Обрезка картинки точно в прямоуголник $w * $h без искажения размеров
    * в отличие от resize обрезает изображение, не искажая его
    *
    * @param  integer Ширина
    * @param  integer Высота
    * @param  string  Тип обрезки: center - центр (default), left - верх/влево, right - вниз/вправо
    */
    public static function imagecrop($filename, $pathTo, $w, $h, $cropType = null)
    {

        list($srcW, $srcH, $image_type) = getimagesize($filename);
        if ($image_type == 2) {
            $srcImage = imagecreatefromjpeg($filename);
        } elseif ($image_type == 1) {
            $srcImage = imagecreatefromgif($filename);
        } elseif ($image_type == 3) {
            $srcImage = imagecreatefrompng($filename);
        } else {
            return false;
        }

        if (!$srcW) {
            return ;
        }
        $ks   = $srcW / $srcH;// растянутость по ширине источника
        $kd   = $w / $h;// растянутость по ширине новой картинки
        $ofX  = $ofXr  = 0;
        $ofY  = $ofYr  = 0;
        if ($kd > $ks) {
            $a    = $srcW / $kd;
            $ofY  = round(($srcH - $a) / 2);
            $ofYr = round($srcH - $a);
            $srcH = $a;
        } else {
            $a    = $srcH * $kd;
            $ofX  = round(($srcW - $a) / 2);
            $ofXr = round($srcW - $a);
            $srcW = $a;
        }

        $srcNew  = imagecreatetruecolor ($w, $h);
        if ($cropType == 'right') {
            imagecopyresampled($srcNew, $srcImage, 0, 0, $ofXr, $ofYr, $w, $h, $srcW, $srcH);
        } elseif ($cropType == 'left') {
            imagecopyresampled($srcNew, $srcImage, 0, 0, 0,     0,     $w, $h, $srcW, $srcH);
        } else {
            imagecopyresampled($srcNew, $srcImage, 0, 0, $ofX,  $ofY,  $w, $h, $srcW, $srcH);
        }
        if ($image_type == 2) {
            $ext = 'jpg';
        } elseif ($image_type == 1) {
            $ext = 'gif';
        } elseif ($image_type == 3) {
            $ext = 'png';
        }

        if ($image_type == 2) {
            $res = ImageJPEG($srcNew, $pathTo, $quality=90);
        } elseif ($image_type == 1) {
            $res = ImageGIF($srcNew, $pathTo);
        } elseif ($image_type == 3) {
            $res = ImagePNG($srcNew, $pathTo);
        }
        return $res;
    }

    public static function removeDir($dir)
    {
        if (file_exists($dir)) {
            $files = scandir($dir);
            foreach ($files as $k => $v) {
                if ($v == '.' || $v == '..') {
                    continue;
                }
                if (is_dir($dir .'/'. $v)) {
                	removeDir($dir .'/'. $v);
                    continue;
                }
                unlink($dir .'/'. $v);
            }
            rmdir($dir);
        }
    }
}

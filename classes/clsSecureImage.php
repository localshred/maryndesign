<?php

//***************************************************************
//** title  : An damaged image generator for validating text
//**
//** story  : Sometimes you need to verify that the client posting
//**          or registering in your site is actually a human. By
//**          asking him/her to type in the word s/he sees, you ensure
//**          that the client is human, and not a bot/spider which
//**          is probably trying to harm your system.
//**          Play around with the values when constructing this
//**          object, but also feel free to experiment with the
//**          maths inside the image manipulating loops.
//**          Note that this class is writing a png file on disk,
//**          so you might need to have a png with the same name
//**          already present in that location with write
//**          permissions set.
//**
//** author : Ioannis Cherouvim
//** e-mail : morales@hack.gr
//** date   : 2005-06-25
//*******************************************************

    class PWImage {

        //instantiate class
        //$filename : the path+filename of the written png file
        //$string   : the text that you want to have in the image
        //$fact     : a magnification factor. play with values>2
        //$bcol     : the background colour, R, G, B (0-255)
        //$fcol     : the foreground colour, R, G, B (0-255)
        function PWImage($filename, $string, $fact, $bcol, $fcol) {
            $font = 5;
            $cosrate = rand(10,19);
            $sinrate = rand(10,18);
           
            $charwidth = imagefontwidth($font);
            $charheight = imagefontheight($font);
            $width=(strlen($string)+2)*$charwidth;
            $height=2*$charheight;
   
            $im = @imagecreatetruecolor($width, $height) or die("Cannot Initialize new GD image stream");
            $im2 = imagecreatetruecolor($width*$fact, $height*$fact);
            $bcol = imagecolorallocate($im, $bcol[0], $bcol[1], $bcol[2]);
            $fcol = imagecolorallocate($im, $fcol[0], $fcol[1], $fcol[2]);

            imagefill($im, 0, 0, $bcol);
            imagefill($im2, 0, 0, $bcol);

            $dotcol = imagecolorallocate($im, (abs($this->getR($fcol)-$this->getR($bcol)))/2.5,
                                              (abs($this->getG($fcol)-$this->getG($bcol)))/2.5,
                                              (abs($this->getB($fcol)-$this->getB($bcol)))/2.5);
   
            $dotcol2 = imagecolorallocate($im, (abs($this->getR($fcol)-$this->getR($bcol)))/1.5,
                                               (abs($this->getG($fcol)-$this->getG($bcol)))/1.5,
                                               (abs($this->getB($fcol)-$this->getB($bcol)))/3.5);
   
            $linecol = imagecolorallocate($im, (abs($this->getR($fcol)-$this->getR($bcol)))/2.4,
                                               (abs($this->getG($fcol)-$this->getG($bcol)))/2.1,
                                               (abs($this->getB($fcol)-$this->getB($bcol)))/2.5);
   
            for($i=0; $i<$width; $i=$i+rand(1,5)) {
                for($j=0; $j<$height; $j=$j+rand(1,5)) {
                    imagesetpixel($im, $i, $j, $dotcol);
                }
            }

            imagestring($im, $font, $charwidth, $charheight/2, $string, $fcol);

            for($j=0; $j<$height*$fact; $j=$j+rand(3,6)) {
                imageline($im2, 0, $j, $width*$fact, $j, $linecol);
            }

            for($i=0; $i<$width*$fact; $i=$i+rand(4,9)) {
                imageline($im2, $i, 0, $i, $height*$fact, $linecol);
            }

            for($i=0; $i<$width*$fact; $i++) {
                for($j=0; $j<$height*$fact; $j++) {
                $x = abs(((cos($i/$cosrate)*5+sin($j/$sinrate*2)*2+$i)/$fact))%$width;
                    $y = abs(((sin($j/$sinrate)*5+cos($i/$cosrate*2)*2+$j)/$fact))%$height;
                    $col = imagecolorat($im, $x, $y);
                    if ($col!=$bcol) imagesetpixel($im2, $i, $j, $col);
                }
            }
       
            for($j=0; $j<$height*$fact; $j=$j+rand(1,5)) {
                for($i=0; $i<$width*$fact; $i=$i+rand(1,5)) {
                    imagesetpixel($im2, $i, $j, $dotcol2);
                }
            }

            ob_start();
            imagepng($im2);
            $buffer = ob_get_contents();
            ob_end_clean();
            $handle = fopen($filename, "w");
            fwrite($handle, $buffer);
            fclose($handle);

            imagedestroy($im);
            imagedestroy($im2);
        }
       
        //functions to extract RGB values from combined 24bit color value
        function getR($col) {return (($col >> 8) >> 8) % 256;}
        function getG($col) {return ($col >> 8) % 256;}
        function getB($col) {return $col % 256;}

    }   

    //**********************************************
    //*************** example use ******************
    //**********************************************
//    $pw = new PWImage("../images/random.png", substr(md5(microtime()*mktime()),0,5), 2, array(0, 0, 0), array(255, 128, 200));
//    $pw = new PWImage("temp2.png", "WEBERDEV", 4, array(255, 255, 255), array(0, 0, 0));
//   $pw = new PWImage("temp3.png", "9df2ceA", 3, array(0, 0, 0), array(255, 228, 250));
//    $pw = new PWImage("temp4.png", "abcdef_g", 1.8, array(0, 0, 0), array(0, 255, 255));

//    echo "<img src='/images/random.png'/><br/>";
//    echo "<img src='temp2.png'/><br/>";
//    echo "<img src='temp3.png'/><br/>";
//   echo "<img src='temp4.png'/><br/>";

?> 

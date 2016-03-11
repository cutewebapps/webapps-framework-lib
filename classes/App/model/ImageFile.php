<?php

/**
 * This file is a part of CWA framework.
 * Copyright 2012, CuteWebApps.com
 * https://github.com/cutewebapps/webapps-framework-lib
 *
 * Licensed under GPL, Free for usage and redistribution.
 */

/**
 * Handling image files
 */
class App_ImageFile {
    /** @var int */
    protected $_nWidth  = 0;
    /** @var int */
    protected $_nHeight = 0;
    /** @var string */
    protected $_strPath = '';
    /** @var resource */
    protected $_ptrImage = null;

    /** @return resource */
    protected function _getGdPtr()
    {
        return $this->_ptrImage;
    }

    /** @return resource */
    public function getGdPtr()
    {
        if ( !$this->_ptrImage ) {
            $this->_createFrom();
        }

        return $this->_getGdPtr();
    }

    public function  __destruct() {
        if ( $this->_getGdPtr() ) {
            imagedestroy( $this->_getGdPtr () );
        }
    }

    /**
    * @return resource
    */
    protected function _createFrom()
    {
        $this->_ptrImage = null;
        if ( !$this->getFilePath() )
            return null;

        $f = fopen( $this->getFilePath(), 'rb' );
	if ( !$f )
            return null;
	$hdr = fread( $f, 500 ); fclose( $f );


    if ( strstr( $hdr, 'GIF89' ) ) {
            $this->_ptrImage = @imagecreatefromgif( $this->getFilePath() );
    } else if ( strstr( $hdr, 'JFIF' ) || strstr( $hdr, 'Exif' )  ) {
            $this->_ptrImage = imagecreatefromjpeg( $this->getFilePath() );
	} else if ( strstr( $hdr, 'PNG' ) ) {
            $this->_ptrImage = @imagecreatefrompng( $this->getFilePath() );
	} else if ( substr( $hdr, 0, 2 ) == 'BM' ) {
            $this->_ptrImage = $this->_createFromBmp();
	} else {
            $this->_ptrImage = imagecreatefromjpeg( $this->getFilePath() );
        }
	return $this->_ptrImage;
    }

    /**
    * @return resource
    */
    protected function _createFromBmp()
    {
        $filename = $this->getFilePath();
        if (! $f1 = fopen($filename,"rb")) { echo "No file "; return FALSE; }

       $FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1,14));
       if ($FILE['file_type'] != 19778) return FALSE;

       $BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'.
                     '/Vcompression/Vsize_bitmap/Vhoriz_resolution'.
                     '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));

       $BMP['colors'] = pow(2,$BMP['bits_per_pixel']);
       if ($BMP['size_bitmap'] == 0) $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
       $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8;
       $BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
       $BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4);
       $BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4);
       $BMP['decal'] = 4-(4*$BMP['decal']);
       if ($BMP['decal'] == 4) $BMP['decal'] = 0;

       $PALETTE = array();
       if ($BMP['colors'] < 16777216) { $PALETTE = unpack('V'.$BMP['colors'], fread($f1,$BMP['colors']*4)); }

       $IMG = fread($f1,$BMP['size_bitmap']);
       $VIDE = chr(0);
       $res = imagecreatetruecolor($BMP['width'],$BMP['height']);
       $P = 0;
       $Y = $BMP['height']-1;
       while ($Y >= 0) {
            $X=0;
            while ($X < $BMP['width']) {
                    if ($BMP['bits_per_pixel'] == 24)
                            $COLOR = unpack("V",substr($IMG,$P,3).$VIDE);
                    elseif ($BMP['bits_per_pixel'] == 16) {
                            $COLOR = unpack("n",substr($IMG,$P,2));
                            $COLOR[1] = $PALETTE[$COLOR[1]+1];
                    } elseif ($BMP['bits_per_pixel'] == 8) {
                            $COLOR = unpack("n",$VIDE.substr($IMG,$P,1));
                            $COLOR[1] = $PALETTE[$COLOR[1]+1];
                    } elseif ($BMP['bits_per_pixel'] == 4) {
                            $COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
                            if (($P*2)%2 == 0) $COLOR[1] = ($COLOR[1] >> 4) ; else $COLOR[1] = ($COLOR[1] & 0x0F);
                            $COLOR[1] = $PALETTE[$COLOR[1]+1];
                    } elseif ($BMP['bits_per_pixel'] == 1) {
                            $COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
                            if     (($P*8)%8 == 0) $COLOR[1] =  $COLOR[1]        >>7;
                            elseif (($P*8)%8 == 1) $COLOR[1] = ($COLOR[1] & 0x40)>>6;
                            elseif (($P*8)%8 == 2) $COLOR[1] = ($COLOR[1] & 0x20)>>5;
                            elseif (($P*8)%8 == 3) $COLOR[1] = ($COLOR[1] & 0x10)>>4;
                            elseif (($P*8)%8 == 4) $COLOR[1] = ($COLOR[1] & 0x8)>>3;
                            elseif (($P*8)%8 == 5) $COLOR[1] = ($COLOR[1] & 0x4)>>2;
                            elseif (($P*8)%8 == 6) $COLOR[1] = ($COLOR[1] & 0x2)>>1;
                            elseif (($P*8)%8 == 7) $COLOR[1] = ($COLOR[1] & 0x1);
                            $COLOR[1] = $PALETTE[$COLOR[1]+1];
                    } else  return FALSE;
                    imagesetpixel($res,$X,$Y,$COLOR[1]);
                    $X++;
                    $P += $BMP['bytes_per_pixel'];
            }
            $Y--; $P+=$BMP['decal'];
       }
       fclose($f1);
       return $res;
    }


    /**
     * IMPORTANT: if $strImagePath starts from slash, it will mean relative to CWA_APPLICATION_DIR path
     * otherwise, use "file://" prefix for your $strImagePath
     *
     * @param string $strImagePath
     * @param int $nWidth (optional, autodetect by default)
     * @param int $nHeight (optional, autodetect by default)
     */
    public function __construct( $strImagePath, $nWidth = 0, $nHeight = 0 )
    {
        $this->_strPath = $strImagePath;
        if ( $nWidth <= 0 || $nHeight <= 0 ) {
            list( $nSizeX, $nSizeY ) = getimagesize( $this->getFilePath() );
            $this->_nWidth = $nSizeX;
            $this->_nHeight = $nSizeY;
        } else {
            $this->_nWidth = $nWidth;
            $this->_nHeight = $nHeight;
        }
    }
    /**
     * @return string
     */
    public function getHttpPath()
    {
        return $this->_strPath;
    }
    /**
     * for APIs and data sharing you will need images to
     * be given in absolute paths
     * @return string
     */
    public function getAbsHttpPath()
    {
        if ( substr( $this->getHttpPath(), 0, 1 ) == '/' ) {
            return ( Sys_Mode::isSsl() ? 'https://' : 'http://' )
                . '/' . $_SERVER['HTTP_HOST'] . $this->getHttpPath();
        }
        return $this->getHttpPath();
    }
    /**
     * @return string
     */
    public function getFilePath()
    {
        if ( substr( $this->getHttpPath(), 0, 1 ) == '/' ) {
            return CWA_APPLICATION_DIR . $this->getHttpPath();
        }

        // there should be no file path for images on another domain!
        //if ( preg_match( '/^https?:\/\//',  $this->getHttpPath() ) ) {
        //    return '';
        //}

        return $this->getHttpPath();
    }
    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->_nWidth;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->_nHeight;
    }

    /**
     * @return string
     */
    public function getImageTag( $arrAttributes = array(), $bIncludeTimeStamp = false )
    {
        $arrAttr = array();

        if ( !isset($arrAttributes[ 'width' ]) && $this->getWidth() > 0 ) {
            $arrAttributes[ 'width' ] = $this->getWidth();
        }
        if ( !isset($arrAttributes[ 'height' ]) &&$this->getHeight() > 0 ) {
            $arrAttributes[ 'height' ] = $this->getHeight();
        }
        if ( !isset( $arrAttributes['alt'] ) ) {
            $arrAttributes[ 'alt' ] = '';
        }

        $arrAttributes[ 'src' ] = $this->getHttpPath();
        if ( $bIncludeTimeStamp ) {
            $arrAttributes[ 'src' ] .= '?tm=' . filemtime( $this->getFilePath() );
        }

        foreach( $arrAttributes as $strAttrKey => $strAttrValue )
        {
            $arrAttr[ $strAttrKey ] = $strAttrKey.'="'.$strAttrValue.'" ';
        }
        return '<img '.implode( ' ',$arrAttr ).' />';
    }


    /**
     * @return string
     */
    public function getImageLazyTag( $arrAttributes = array(), $bIncludeTimeStamp = false )
    {
        $arrAttr = array();

        if ( !isset($arrAttributes[ 'width' ]) && $this->getWidth() > 0 ) {
            $arrAttributes[ 'width' ] = $this->getWidth();
        }
        if ( !isset($arrAttributes[ 'height' ]) &&$this->getHeight() > 0 ) {
            $arrAttributes[ 'height' ] = $this->getHeight();
        }
        if ( !isset( $arrAttributes['alt'] ) ) {
            $arrAttributes[ 'alt' ] = '';
        }

        $arrAttributes[ 'data-original' ] = $this->getHttpPath();
        if ( $bIncludeTimeStamp ) {
            $arrAttributes[ 'data-original' ] .= '?tm=' . filemtime( $this->getFilePath() );
        }

        if ( !isset( $arrAttributes['class'] )) {
            $arrAttributes['class'] = '';
        }
        $arrAttributes['class'] = trim( $arrAttributes['class'].' lazy' );

        foreach( $arrAttributes as $strAttrKey => $strAttrValue )
        {
            $arrAttr[ $strAttrKey ] = $strAttrKey.'="'.$strAttrValue.'" ';
        }
        return '<img '.implode( ' ',$arrAttr ).' />';
    }


    /**
     * @return int
     */
    public function getRatio()
    {
        return ( $this->getHeight() == 0 ) ? 0 :
               sprintf( '%0.2f', $this->getWidth() / $this->getHeight() );
    }


    public function saveAsJpg()
    {
        if ( !$this->_createFrom() ) {
            throw new App_ImageFile_Exception( "Image cannot be opened" );
        }
        $im = $this->_getGdPtr();

        $file = new Sys_File( $this->getFilePath() );
        $file->write( '', true );

        $a = imagejpeg($im, $file->getName(), 95);
        imagedestroy($im);
        $this->_ptrImage = null;
    }

    /**
     * Rotate image automatically
     * @param integer $nWidthLimit
     * @return App_ImageFile
     */
    public function fixOrientation( $strRoot, $strPath, $nWidthLimit, $nOrientation = '' ) {

        if (!$nOrientation) {

            $exif = exif_read_data( $strRoot . $strPath );

            // no orientation tag found, exit
            if (!isset($exif['COMPUTED']['Orientation'])) return false;
            //$exif['COMPUTED']['Orientation'] = 6;

    //        Sys_Debug::dump($exif);
            $nOrientation = $exif['COMPUTED']['Orientation'];
    //        Sys_Debug::dumpDie($nOrientation);

        }

        // Get new dimensions
        $nHeight = (int) (($nWidthLimit / $this->getWidth()) * $this->getHeight());

        // create new image file
        $imgResult = new App_ImageFile( $strPath, $nWidthLimit, $nHeight );
	    $this->_createFrom();
        $image = $this->_getGdPtr();
	    if ( !$image )
            throw new App_ImageFile_Exception( 'GD Pointer was not created' );

        // Resample
        $im = imagecreatetruecolor($nWidthLimit, $nHeight);

        imagecopyresampled(
                $im, $image,
                0, 0, 0, 0,
                $nWidthLimit, $nHeight,
                $this->getWidth(), $this->getHeight()
        );

        // Fix Orientation
        switch($nOrientation) {
            case 3:
                $im = imagerotate($im, 180, 0);
                break;
            case 6:
                $im = imagerotate($im, -90, 0);
                break;
            case 8:
                $im = imagerotate($im, 90, 0);
                break;
        }

//        Sys_Debug::dumpDie($imgResult->getFilePath());
        $file = new Sys_File( $imgResult->getFilePath() );
        $file->write( '', true );
	    $a = imagejpeg($im, $file->getName(), 95);

	    imagedestroy($im);
        return  $imgResult;

    }


    /**
     * Generates resampled image with filling in into box
     * @return App_ImageFile
     */
    public function generateResampled( $strDestinationPath, $nWidthLimit, $nHeightLimit )
    {
        if ( $this->getHeight() == 0 || $this->getWidth() == 0 )
            throw new App_ImageFile_Exception ( 'Width or height of the resampled image cannot be zero' );

        $fltRatio = $this->getWidth() / $this->getHeight();
        if ( $this->getWidth() > $this->getHeight() ) {
            $nNewWidth = $nWidthLimit;
            $nNewHeight = intval( $nWidthLimit / $fltRatio );
        } else  {
            $nNewHeight = $nHeightLimit;
            $nNewWidth = intval( $nHeightLimit * $fltRatio );
        }

        $imgResult = new App_ImageFile( $strDestinationPath, $nNewWidth, $nNewHeight );
	    $this->_createFrom();
        $img = $this->_getGdPtr();
	    if ( !$img )
            throw new App_ImageFile_Exception( 'GD Pointer was not created' );

        $im = imagecreatetruecolor( $nNewWidth, $nNewHeight );
	    imagecopyresampled($im, $img, 0, 0, 0, 0,
            $nNewWidth, $nNewHeight,
            $this->getWidth(), $this->getHeight() );

        $file = new Sys_File( $imgResult->getFilePath() );
        $file->write( '', true );
	    $a = imagejpeg($im, $file->getName(), 95);

	    imagedestroy($im);
        return  $imgResult;
    }

    /**
     * @return  App_ImageFile
     */
    public function generateThumbnail( $strDestinationPath, $nNewWidth, $nNewHeight, $nColor = 0xFFFFFF )
    {
        $imgResult = new App_ImageFile( $strDestinationPath, $nNewWidth, $nNewHeight );

        $src_file = $this->getFilePath();
	if ( !file_exists( $src_file )) {
            throw new App_ImageFile_Exception('Source file cannot be found');
            return false;
        }
	// if (!$size = getimagesize($src_file)) { return false; }
	// if ( strstr( strtolower( file_get_contents( $src_file)), '<html>' ) ) return false;

        $sizes = $nNewWidth;
        $size = array( $this->getWidth(), $this->getHeight() );

	$this->_createFrom();
        $img = $this->_getGdPtr();
	if ( !$img ) {
            throw new App_ImageFile_Exception('Invalid GD pointer');
            return false;
        }

	$offset_x = $offset_y = 0;
	if ($size[0] > $size[1] ) {
		$offset_y = ($sizes - ($size[1]*$sizes)/$size[0])/2;
	} else {
		$offset_x = ($sizes - ($size[0]*$sizes)/$size[1])/2;
	}

	if ($size[0] > $size[1]) {
		$im1 = imagecreatetruecolor($size[0], $size[0]);
		$w = $size[0];
	} else {
		$im1 = imagecreatetruecolor($size[1], $size[1]);
		$w = $size[1];
	}

	imagefill($im1, 0, 0, $nColor);
	imagecopy($im1, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
	$im = imagecreatetruecolor($sizes, $sizes);
	imagefill($im, 0, 0, $nColor);
	imagecopyresampled($im, $im1, $offset_x, $offset_y, 0, 0,
			$sizes, $sizes, imagesx($im1), imagesy($im1));

	$im1 = imagecreatetruecolor( imagesx($im), imagesy($im) );
	imagefill($im1, 0, 0, $nColor);
	imagecopy($im1, $im, 0, 0, 0, 0, imagesx($im), imagesy($im));
	$white = imagecolorallocate( $im1, 224, 224, 255);

        $file = new Sys_File( $imgResult->getFilePath() );
        $file->save( '', true );
	$a = imagejpeg($im1, $file->getName(), 95);

	imagecolordeallocate( $im1, $white );
	imagedestroy($im1);
	imagedestroy($im);
        return $imgResult;
    }

    /**
     * @return  App_ImageFile (crop square )
     */
    public function generateThumbnailSquare( $dir, $w, $q = 80 )
    {

        $imgResult = new App_ImageFile( $dir, $w, $w );
        $imgResult->_strPathFull = $this->getDirPath( $imgResult );; // full system path to thumb

        $src_file = $this->getFilePath();
        if ( !file_exists( $src_file )) {
            throw new App_ImageFile_Exception('Source file cannot be found');
            return false;
        }

        // auto detect type
        $arrImgSize = getimagesize($src_file);
        list($strImg,$strType) = explode('/', $arrImgSize['mime']);

        // создаём исходное изображение на основе
        // исходного файла и опеределяем его размеры
        $strCreateFuncName = 'imagecreatefrom'.strtolower($strType);
        $src = $strCreateFuncName($src_file);

        $w_src = imagesx($src);
        $h_src = imagesy($src);

        // создаём пустую квадратную картинку
        $dest = imagecreatetruecolor($w,$w);

        // вырезаем квадратную серединку по x, если фото горизонтальное
        if ($w_src>$h_src) {
            imagecopyresampled($dest, $src, 0, 0,
            round((max($w_src,$h_src)-min($w_src,$h_src))/2),
            0, $w, $w, min($w_src,$h_src), min($w_src,$h_src));
        }

        // вырезаем квадратную серединку по y, если фото вертикальное
        elseif ($w_src<$h_src) {
            imagecopyresampled($dest, $src, 0, 0, 0,
            round((max($w_src,$h_src)-min($w_src,$h_src))/2),
            $w, $w, min($w_src,$h_src), min($w_src,$h_src));
        }

        // квадратная картинка масштабируется без вырезок
        else {
            imagecopyresampled($dest, $src, 0, 0, 0, 0, $w, $w, $w_src, $w_src);
        }

        // вывод картинки и очистка памяти
        $res=imagejpeg($dest,$imgResult->_strPathFull,$q);

        imagedestroy($dest);
        imagedestroy($src);

        return ($res)? $imgResult : null;
    }


    /**
     * @return  App_ImageFile (crop square )
     */
    public function generateThumbnailRectangle( $dir, $w, $h, $q = 80 )
    {

        $imgResult = new App_ImageFile( $dir, $w, $w );
        $imgResult->_strPathFull = $this->getDirPath( $imgResult ); // full system path to thumb

        $src_file = $this->getFilePath();
        if ( !file_exists( $src_file )) {
            throw new App_ImageFile_Exception('Source file cannot be found');
            return false;
        }

        // auto detect type
        $arrImgSize = getimagesize($src_file);
        list($strImg,$strType) = explode('/', $arrImgSize['mime']);

        // создаём исходное изображение на основе
        // исходного файла и опеределяем его размеры
        $strCreateFuncName = 'imagecreatefrom'.strtolower($strType);
        $src = $strCreateFuncName($src_file);

        $w_src = imagesx($src);
        $h_src = imagesy($src);

        // создаём пустую квадратную картинку
        $dest = imagecreatetruecolor($w,$h);

        // вырезаем квадратную серединку по x, если фото горизонтальное
        if ($w_src>$h_src) {
            imagecopyresampled($dest, $src, 0, 0,
            round((max($w_src,$h_src)-min($w_src,$h_src))/2),
            0, $w, $h, min($w_src,$h_src), min($w_src,$h_src));
        }

        // вырезаем квадратную серединку по y, если фото вертикальное
        elseif ($w_src<$h_src) {
            imagecopyresampled($dest, $src, 0, 0, 0,
            round((max($w_src,$h_src)-min($w_src,$h_src))/2),
            $w, $h, min($w_src,$h_src), min($w_src,$h_src));
        }

        // квадратная картинка масштабируется без вырезок
        else {
            imagecopyresampled($dest, $src, 0, 0, 0, 0, $w, $h, $w_src, $h_src);
        }

        // вывод картинки и очистка памяти
        $res=imagejpeg($dest,$imgResult->_strPathFull,$q);

        imagedestroy($dest);
        imagedestroy($src);

        return ($res)? $imgResult : null;
    }

//    function generateThumbnailCrop( $file_output, $crop = 'square', $percent = false, $boolUseAppDir = true) {
//	list($w_i, $h_i, $type) = getimagesize( $this->getFilePath() );
//	if (!$w_i || !$h_i) {
//            throw App_Exception( 'Unable to get the length and width of the image' );
//            return;
//        }
//        $types = array('','gif','jpeg','png');
//        $ext = $types[$type];
//        if ($ext) {
//            $func = 'imagecreatefrom'.$ext;
//            $img = $func($this->getFilePath());
//        } else {
//            echo 'Invalid file format';
//            return;
//        }
//	if ($crop == 'square') {
//            $min = $w_i;
//            if ($w_i > $h_i) $min = $h_i;
//            $w_o = $h_o = $min;
//	} else {
//            list($x_o, $y_o, $w_o, $h_o) = $crop;
//            if ($percent) {
//                    $w_o *= $w_i / 100;
//                    $h_o *= $h_i / 100;
//                    $x_o *= $w_i / 100;
//                    $y_o *= $h_i / 100;
//            }
//    	if ($w_o < 0) $w_o += $w_i;
//	    $w_o -= $x_o;
//            if ($h_o < 0) $h_o += $h_i;
//            $h_o -= $y_o;
//	}
//	$img_o = imagecreatetruecolor($w_o, $h_o);
//	imagecopy($img_o, $img, 0, 0, $x_o, $y_o, $w_o, $h_o);
//        if ( file_exists( (( $boolUseAppDir ) ? CWA_APPLICATION_DIR : '') . $file_output) )
//            unlink ( (( $boolUseAppDir ) ? CWA_APPLICATION_DIR : '') . $file_output);
//	if ($type == 2) {
//            return imagejpeg($img_o, (( $boolUseAppDir ) ? CWA_APPLICATION_DIR : '') . $file_output,100);
//	} else {
//            $func = 'image'.$ext;
//            return $func($img_o, (( $boolUseAppDir ) ? CWA_APPLICATION_DIR : '') . $file_output);
//	}
//        imagedestroy($img_o);
//    }

    function generateThumbnailCrop( $strDirImage, $thumb_width, $thumb_height, $strExt )
    {
        $imgResult = new App_ImageFile( $strDirImage, $thumb_width, $thumb_height );


        $src_file = $this->getFilePath();
        $strFuntion = 'imagecreatefrom' . ( ($strExt == 'jpg') ? 'jpeg' : $strExt );
        $image = $strFuntion($src_file);

        $width = imagesx($image);
        $height = imagesy($image);

        $original_aspect = $width / $height;
        $thumb_aspect = $thumb_width / $thumb_height;

        if ( $original_aspect >= $thumb_aspect )
        {
           // If image is wider than thumbnail (in aspect ratio sense)
           $new_height = $thumb_height;
           $new_width = $width / ($height / $thumb_height);
        }
        else
        {
           // If the thumbnail is wider than the image
           $new_width = $thumb_width;
           $new_height = $height / ($width / $thumb_width);
        }

        $thumb = imagecreatetruecolor( $thumb_width, $thumb_height );

        // Resize and crop
        imagecopyresampled($thumb,
                           $image,
                           0 - ($new_width - $thumb_width) / 2, // Center the image horizontally
                           0 - ($new_height - $thumb_height) / 2, // Center the image vertically
                           0, 0,
                           $new_width, $new_height,
                           $width, $height);
        imagejpeg($thumb, $strDirImage, 80);

        return $imgResult;
    }

    public function getDirPath( $imgResult )
    {
        if( ! strstr($imgResult->_strPath, 'file://') && ! strstr($imgResult->_strPath, '/home') ) {
            $strPath = CWA_APPLICATION_DIR . $imgResult->_strPath;
        } else {
            $strPath = $imgResult->_strPath;
        }

        return $strPath;
    }
}

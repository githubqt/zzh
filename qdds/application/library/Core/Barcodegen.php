<?php
namespace Core;

require_once(APPLICATION_PATH.'/application/library/barcodegen/class/BCGFontFile.php');
require_once(APPLICATION_PATH.'/application/library/barcodegen/class/BCGColor.php');
require_once(APPLICATION_PATH.'/application/library/barcodegen/class/BCGDrawing.php');
require_once(APPLICATION_PATH.'/application/library/barcodegen/class/BCGcode128.barcode.php');

use lib\barcode\{BCGFontFile,BCGColor,BCGDrawing,BCGcode128};

 /** 
 * 用于设置条形码打印
 */
class Barcodegen
{
	/**
	 * 用于生成条形码
	 * @param string 	$text 		条形码编号
	 * @param interger	$fontSize	字体大小
	 * @param interger  $scale		分辨率
	 * @param interger  $thickness  高度
	 * @return outpng
	 */
    public function createCode($text = 'HELLO',$fontSize = 10,$scale = 1,$thickness = 40)
    {
		// Loading Font
		$font = new BCGFontFile(APPLICATION_PATH.'/application/library/barcodegen/font/Arial.ttf', $fontSize);
		
		// Don't forget to sanitize user inputs
		
		// The arguments are R, G, B for color.
		$color_black = new BCGColor(0, 0, 0);
		$color_white = new BCGColor(255, 255, 255);


		$drawException = null;
		try {
			$code = new BCGcode128();
			$code->setScale($scale); // Resolution
			$code->setThickness($thickness); // Thickness 高度
			$code->setForegroundColor($color_black); // Color of bars
			$code->setBackgroundColor($color_white); // Color of spaces
			$code->setFont($font); // Font (or 0)
			$code->parse($text); // Text
		} catch(Exception $exception) {
			$drawException = $exception;
		}
		
		/* Here is the list of the arguments
		1 - Filename (empty : display on screen)
		2 - Background color */
		$drawing = new BCGDrawing('', $color_white);
		if($drawException) {
			$drawing->drawException($drawException);
		} else {
			$drawing->setBarcode($code);
			$drawing->draw();
		}
		
		// Header that says it is an image (remove it if you save the barcode to a file)
		header('Content-Type: image/png');
		header('Content-Disposition: inline; filename="barcode.png"');
		
		// Draw (or save) the image into PNG format.
		$drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
    }    
}
		
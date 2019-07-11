<?php
namespace Core;
use Custom\YDLib;
//生成二维码
//引入核心库文件
include APPLICATION_PATH."/application/library/phpqrcode/phpqrcode.php";
use lib\QRcode;
class Qzcode{

    public function __construct(){}
    //封装生成二维码图片的函数（方法）
    /** *利用php类库生成二维码图片
     * $content：二维码内容参数
     * $size：生成二维码的尺寸，宽度和高度的值
     * $lev：可选参数，纠错等级
     * $margin：生成的二维码离边框的距离
     */
    public function create_code($content, $size = '200', $lev = 'L', $margin= '5')
    {
        QRcode::png($content, false, $lev, $size, $margin);
//        $QR = 'qrcode.png';//已经生成的原始二维码图
//        Header("Content-type: image/png");
//        $QR = imagecreatefromstring(file_get_contents($QR));
//        ImagePng($QR);
    }
	

	/**
     * 二维码生成器
     */
    public function  qrcode($content,$detail)
    {
   		 //生成的商品分享图片路径	
		$filePath = "./data/product/".($detail['id'] % 256)."/";	
		
		
		if (!file_exists($filePath)) {
			mkdir($filePath,0777,TRUE);
		}
		
		if (!file_exists($fileName)) {
			//提前准备好的二维码
			// $codeName = RESOURCE_STATIC."qdds/img/share/qrcode.png";
			$codeName = RESOURCE_STATIC."qdds/img/share/coupan/qrcode.png";
	
			//$content存入二维码图片 
			$image = QRcode::png($content,$codeName, 'L', 6, 2); 		
	
	         //创建画布  
	        $backgroudImg = APPLICATION_PATH.'/application/resource/promote.jpg';
			
	       
			$im = imagecreatefromstring(file_get_contents($backgroudImg));

			$width = imagesx ( $im );
	        //填充画布背景色  
	        $color = imagecolorallocate($im, 255, 255, 255);  
	        imagefill($im, 0, 0, $color);  
	      
	        //字体文件  
	        $font_file = APPLICATION_PATH.'/application/resource/msyhbd.ttc';  
	        $font_file_bold = APPLICATION_PATH.'/application/resource/2016.ttf';  
	
	        //设定字体的颜色  
	        $font_color_1 = ImageColorAllocate ($im, 140, 140, 140);  
	        $font_color_2 = ImageColorAllocate ($im, 28, 28, 28);  
	        $font_color_3 = ImageColorAllocate ($im, 129, 129, 129);  
	        $font_color_4 = ImageColorAllocate ($im, 236, 180, 182);  
	      
	        $fang_bg_color = ImageColorAllocate ($im, 254, 216, 217);  
	      
	        //宣传图片  
	        $goodImg = '';
	        if (!empty($detail['logo_url'])) {
				$goodImg = HOST_FILE.$detail['logo_url'];
			} else {
				$goodImg = HOST_STATIC."common/images/common.png";
			}
			
	        
			 
	        list($g_w,$g_h) = getimagesize($goodImg);  
	        $goodImg = $this->createImageFromFile($goodImg);  
	        imagecopyresized($im, $goodImg, 203, 175, 0, 0, 730, 730, $g_w, $g_h);  
	      
	        //二维码  
	        list($code_w,$code_h) = getimagesize($codeName);  
	        $codeImg = $this->createImageFromFile($codeName);  
			$topY = 100;
			$topFontY = 100;
			$fontX = 1050;
	        imagecopyresized($im, $codeImg, 1060, 660, 0, 0, 250, 250, $code_w, $code_h);  	      
	        
	        $dataname = $this->hanzi_insert('商品名称：'.$detail['name'],17,"\n");
	        //优惠券描述 
	        $topY += $topFontY;
	        imagettftext($im, 36,0, $fontX, $topY, $font_color_2 ,$font_file, $dataname);  
	        $topY += 160;
	        imagettftext($im, 36,0, $fontX, $topY, $font_color_2 ,$font_file, '市场价格：' . $detail['market_price']);
			 
			$length = mb_strlen('市场价格：' . $detail['market_price']);
	        $red1 = imagecolorallocate($im,255,0,0);//创建一个颜色，以供使用
	        for ($i=0; $i < 5; $i++) { 
				 imageline($im,$fontX,340+$i,$length*40+$fontX,340+$i,$red1);//画一条直线
			}	
					
	        $topY += 120;
	        imagettftext($im, 36,0, $fontX, $topY, $font_color_2 ,$font_file, '本店销售：' . $detail['sale_price']);
	        $topY += 120;
	        imagettftext($im, 36,0, $fontX, $topY, $font_color_2 ,$font_file, '商品数量：' . $detail['stock']);
	        $topY += 180;
	        imagettftext($im, 32,0, 1330, $topY, $font_color_2 ,$font_file, "微信扫描二维码即可购买\n          或店内详情" );
			


			$fontSize = 28;
			$fontBox = imagettfbbox($fontSize, 0, $font_file, $detail['company']);//文字水平居中实质
			imagettftext($im, $fontSize,0, ceil(($width - $fontBox[2]) / 2), 1250, $font_color_4 ,$font_file, $detail['company']);
			
	        Header("Content-type: image/png");
			ob_start();
			ImagePng($im); 
			$image_data = ob_get_contents();
			ob_end_clean();
			
			echo $image_data;
			file_put_contents($fileName, $image_data);
	        //释放空间  
	        imagedestroy($im);  
	        imagedestroy($goodImg);  
	        imagedestroy($codeImg);  
		} else {
			Header("Content-type: image/png");
			echo readfile($fileName);
		}
    }	

    
    function str_insert($str, $i, $substr)
    {
    	for($j=0; $j<$i; $j++){
    		$startstr .= $str[$j];
    	}
    	for ($j=$i; $j<strlen($str); $j++){
    		$laststr .= $str[$j];
    	}
    	$str = ($startstr . $substr . $laststr);
    	return $str;
    }
	//汉字截取
    function hanzi_insert($str, $i, $substr)
    {   	
		$startstr = mb_substr($str,0,$i,'UTF8');
		$laststr = mb_substr($str,$i,$i,'UTF8');
    	$str = ($startstr . $substr . $laststr);
    	return $str;
    }

    /**
     * 商品分享图片生成
     * @param $content 分享内容
     * @param $detail 商品数据，array
     */
    function shareProduct($content,$detail)
    {
        //生成的商品分享图片路径
        $filePath = "./data/product/".($detail['id'] % 256)."/";

        if (!file_exists($filePath)) {
            mkdir($filePath,0777,TRUE);
        }

        if (!file_exists($fileName)) {
            //提前准备好的二维码

            // $codeName = RESOURCE_STATIC."qdds/img/share/qrcode.png";
            $codeName = RESOURCE_STATIC."qdds/img/share/coupan/qrcode.png";

            //$content存入二维码图片
            $image = QRcode::png($content,$codeName, 'L', 6, 2);

            //创建画布
            $backgroudImg = APPLICATION_PATH.'/application/resource/promote.png';

            //$im = imagecreatefrompng($backgroudImg);
            $im = imagecreatefromstring(file_get_contents($backgroudImg));

            $width = imagesx ( $im );
            //填充画布背景色
            $color = imagecolorallocate($im, 255, 255, 255);
            imagefill($im, 0, 0, $color);

            //字体文件
            $font_file = APPLICATION_PATH.'/application/resource/msyhbd.ttc';
            $font_file_bold = APPLICATION_PATH.'/application/resource/2016.ttf';

            //设定字体的颜色
            $font_color_1 = ImageColorAllocate ($im, 140, 140, 140);
            $font_color_2 = ImageColorAllocate ($im, 28, 28, 28);
            $font_color_3 = ImageColorAllocate ($im, 129, 129, 129);
            $font_color_4 = ImageColorAllocate ($im, 236, 180, 182);
            $font_color_5 = ImageColorAllocate ($im, 255, 255, 255);
            $font_color_6 = ImageColorAllocate ($im, 204, 178, 118);

            $fang_bg_color = ImageColorAllocate ($im, 254, 216, 217);

            //宣传图片
            $goodImg = '';
            if (!empty($detail['logo_url'])) {
                $goodImg = HOST_FILE.$detail['logo_url'];
            } else {
                $goodImg = HOST_STATIC."common/images/common.png";
            }

            list($g_w,$g_h) = getimagesize($goodImg);
            $goodImg = $this->createImageFromFile($goodImg);
            imagecopyresized($im, $goodImg, 150, 170, 0, 0, 420, 420, $g_w, $g_h);

            //背景图
            list($g_w,$g_h) = getimagesize($backgroudImg);
            $backgroudImg = $this->createImageFromFile($backgroudImg);
            imagecopyresized($im, $backgroudImg, 0, 0, 0, 0, $g_w, $g_h, $g_w, $g_h);

            //二维码
            list($code_w,$code_h) = getimagesize($codeName);
            $codeImg = $this->createImageFromFile($codeName);
            imagecopyresized($im, $codeImg, 274, 860, 0, 0, 170, 170, $code_w, $code_h);

            $product_name = mb_substr($detail['name'],0,20,'UTF8');
            $fontSize = 22;
            $fontBox = imagettfbbox($fontSize, 0, $font_file, $product_name);//文字水平居中实质
            imagettftext($im, $fontSize,0, ceil(($width - $fontBox[2]) / 2), 730, $font_color_5 ,$font_file, $product_name);


            imagettftext($im, 20,0, 100, 790, $font_color_6 ,$font_file, '本店售价');

            $seckill_price = '￥' . $detail['sale_price'];
            $fontSize = 32;
            $fontBox = imagettfbbox($fontSize, 0, $font_file, $seckill_price);//文字水平居中实质
            imagettftext($im, $fontSize,0, ceil(($width - $fontBox[2]) / 2), 790, $font_color_6 ,$font_file, $seckill_price);

            imagettftext($im, 16,0, 500, 790, $font_color_5 ,$font_file, '公价 ￥' . $detail['market_price']);

            $length = mb_strlen($detail['market_price']);
            $red1 = imagecolorallocate($im,255,0,0);//创建一个颜色，以供使用
            for ($i=0; $i < 3; $i++) {
                imageline($im,550,780+$i,$length*15+550,780+$i,$red1);//画一条直线
            }

            $fontSize = 28;
            $fontBox = imagettfbbox($fontSize, 0, $font_file, $detail['shop_name']);//文字水平居中实质
            imagettftext($im, $fontSize,0, ceil(($width - $fontBox[2]) / 2), 100, $font_color_6 ,$font_file, $detail['shop_name']);

            Header("Content-type: image/png");
            ob_start();
            ImagePng($im);
            $image_data = ob_get_contents();
            ob_end_clean();

            echo $image_data;
            file_put_contents($fileName, $image_data);
            //释放空间
            imagedestroy($im);
            imagedestroy($goodImg);
            imagedestroy($codeImg);
        } else {
            Header("Content-type: image/png");
            echo readfile($fileName);
        }
    }

    /**
     * 优惠券分享图片生成 
     * @param $content 分享内容  
     * @param $detail 优惠券数据，array 
     */  
    function shareSeckill($content,$detail)
    {
    	
    	
    	//生成的优惠券分享图片路径	
		$filePath = "./data/seckill/".($detail['id'] % 256)."/";	

		if (!file_exists($filePath)) {
			mkdir($filePath,0777,TRUE);
		}
		
		if (!file_exists($fileName)) {
			//提前准备好的二维码
			
			// $codeName = RESOURCE_STATIC."qdds/img/share/qrcode.png";
			$codeName = RESOURCE_STATIC."qdds/img/share/coupan/qrcode.png";
	
			//$content存入二维码图片 
			$image = QRcode::png($content,$codeName, 'L', 6, 2); 		
	
	         //创建画布  
	        $backgroudImg = APPLICATION_PATH.'/application/resource/seckill.png';
			
			$im = imagecreatefrompng($backgroudImg);

			$width = imagesx ( $im );
	        //填充画布背景色  
	        $color = imagecolorallocate($im, 255, 255, 255);
	        imagefill($im, 0, 0, $color);
	      
	        //字体文件  
	        $font_file = APPLICATION_PATH.'/application/resource/msyhbd.ttc';  
	        $font_file_bold = APPLICATION_PATH.'/application/resource/2016.ttf';  
	
	        //设定字体的颜色  
	        $font_color_1 = ImageColorAllocate ($im, 140, 140, 140);  
	        $font_color_2 = ImageColorAllocate ($im, 28, 28, 28);  
	        $font_color_3 = ImageColorAllocate ($im, 129, 129, 129);  
	        $font_color_4 = ImageColorAllocate ($im, 236, 180, 182);
            $font_color_5 = ImageColorAllocate ($im, 255, 255, 255);
            $font_color_6 = ImageColorAllocate ($im, 204, 178, 118);

            $fang_bg_color = ImageColorAllocate ($im, 254, 216, 217);
	      
	        //宣传图片  
	        $goodImg = '';
	        if (!empty($detail['logo_url'])) {
				$goodImg = HOST_FILE.$detail['logo_url'];
			} else {
				$goodImg = HOST_STATIC."common/images/common.png";
			}	        
			 
	        list($g_w,$g_h) = getimagesize($goodImg);
	        $goodImg = $this->createImageFromFile($goodImg);
	        imagecopyresized($im, $goodImg, 150, 170, 0, 0, 420, 420, $g_w, $g_h);

	        //背景图
            list($g_w,$g_h) = getimagesize($backgroudImg);
            $backgroudImg = $this->createImageFromFile($backgroudImg);
            imagecopyresized($im, $backgroudImg, 0, 0, 0, 0, $g_w, $g_h, $g_w, $g_h);
	      
	        //二维码  
	        list($code_w,$code_h) = getimagesize($codeName);  
	        $codeImg = $this->createImageFromFile($codeName);
	        imagecopyresized($im, $codeImg, 274, 860, 0, 0, 170, 170, $code_w, $code_h);

            $product_name = mb_substr($detail['product_name'],0,20,'UTF8');
            $fontSize = 22;
            $fontBox = imagettfbbox($fontSize, 0, $font_file, $product_name);//文字水平居中实质
            imagettftext($im, $fontSize,0, ceil(($width - $fontBox[2]) / 2), 730, $font_color_5 ,$font_file, $product_name);


            imagettftext($im, 20,0, 100, 790, $font_color_6 ,$font_file, '限时秒杀价');

            $seckill_price = '￥' . $detail['seckill_price'];
            $fontSize = 32;
            $fontBox = imagettfbbox($fontSize, 0, $font_file, $seckill_price);//文字水平居中实质
            imagettftext($im, $fontSize,0, ceil(($width - $fontBox[2]) / 2), 790, $font_color_6 ,$font_file, $seckill_price);

            imagettftext($im, 16,0, 500, 790, $font_color_5 ,$font_file, '公价 ￥' . $detail['market_price']);

            $length = mb_strlen($detail['market_price']);
            $red1 = imagecolorallocate($im,255,0,0);//创建一个颜色，以供使用
            for ($i=0; $i < 3; $i++) {
                imageline($im,550,780+$i,$length*15+550,780+$i,$red1);//画一条直线
            }

            $time = $detail['starttime'] .' 至 '. $detail['endtime'];
            $fontSize = 16;
            $fontBox = imagettfbbox($fontSize, 0, $font_file, $time);//文字水平居中实质
            imagettftext($im, $fontSize,0, ceil(($width - $fontBox[2]) / 2), 830, $font_color_6 ,$font_file, $time);

            $fontSize = 28;
			$fontBox = imagettfbbox($fontSize, 0, $font_file, $detail['shop_name']);//文字水平居中实质
			imagettftext($im, $fontSize,0, ceil(($width - $fontBox[2]) / 2), 100, $font_color_6 ,$font_file, $detail['shop_name']);

	        Header("Content-type: image/png");
			ob_start();
			ImagePng($im); 
			$image_data = ob_get_contents();
			ob_end_clean();
			
			echo $image_data;
			file_put_contents($fileName, $image_data);
	        //释放空间  
	        imagedestroy($im);  
	        imagedestroy($goodImg);  
	        imagedestroy($codeImg);  
		} else {
			Header("Content-type: image/png");
			echo readfile($fileName);
		}
    }  
    
    /*
     * 
     *分享摇一摇
     * @param $content 分享内容  
     * @param $detail 摇一摇数据，array 
     */
    function shareShake($content,$detail)
    {
    	    	 
    	//生成的摇一摇分享图片路径
    	$filePath = "./data/seckill/".($detail['id'] % 256)."/";
    	if (!file_exists($filePath)) {
    		mkdir($filePath,0777,TRUE);
    	}
    
    	if (!file_exists($fileName)) {
			//提前准备好的二维码
			
			$codeName = RESOURCE_STATIC."qdds/img/share/coupan/qrcode.png";
	
			//$content存入二维码图片 
			$image = QRcode::png($content,$codeName, 'L', 6, 0); 		
	
	         //创建画布  
	        $backgroudImg = APPLICATION_PATH.'/application/resource/shake.jpg';
			
			$im = imagecreatefromstring(file_get_contents($backgroudImg));

			$width = imagesx ( $im );
	        //填充画布背景色  
	        $color = imagecolorallocate($im, 255, 255, 255);  
	        imagefill($im, 0, 0, $color);  
	      
	        //字体文件  
	        $font_file = APPLICATION_PATH.'/application/resource/msyhbd.ttc';  
	        $font_file_bold = APPLICATION_PATH.'/application/resource/2016.ttf';  
	
	        //设定字体的颜色  
	        $font_color_1 = ImageColorAllocate ($im, 140, 140, 140);  
	        $font_color_2 = ImageColorAllocate ($im, 28, 28, 28);  
	        $font_color_3 = ImageColorAllocate ($im, 129, 129, 129);  
	        $font_color_4 = ImageColorAllocate ($im, 236, 180, 182);  
	      
	        $fang_bg_color = ImageColorAllocate ($im, 254, 216, 217);  
	      
	        //二维码  
	        list($code_w,$code_h) = getimagesize($codeName);  
	        $codeImg = $this->createImageFromFile($codeName);  
	        imagecopyresized($im, $codeImg, 430, 790, 0, 0, 180, 180, $code_w, $code_h);  
			
			$fontSize = 28;
			$fontBox = imagettfbbox($fontSize, 0, $font_file, $detail['company']);//文字水平居中实质
			imagettftext($im, $fontSize,0, ceil(($width - $fontBox[2]) / 2), 1425, $font_color_2 ,$font_file, $detail['company']);
			
	        Header("Content-type: image/png");
			ob_start();
			ImagePng($im); 
			$image_data = ob_get_contents();
			ob_end_clean();
			
			echo $image_data;
			file_put_contents($fileName, $image_data);
	        //释放空间  
	        imagedestroy($im);  
	        imagedestroy($goodImg);  
	        imagedestroy($codeImg);  
    	} else {
    		Header("Content-type: image/png");
    		echo readfile($fileName);
    	}
    }
    
    
    
    
    
    
    
    //使用方法-------------------------------------------------  
    //数据格式，如没有优惠券coupon_price值为0。  
    // $gData = [  
        // 'pic' => 'code_png/nv_img.jpg',  
        // 'title' =>'chic韩版工装羽绒棉服女冬中长款2017新款棉袄大毛领收腰棉衣外套',  
        // 'price' => 19.8,  
        // 'original_price' => 119.8,  
        // 'coupon_price' => 100  
    // ];  
    // //直接输出  
    // createSharePng($gData,'code_png/php_code.jpg');  
    // //输出到图片  
    // createSharePng($gData,'code_png/php_code.jpg','share.png');     
    
    /** 
     * 分享图片生成 
     * @param $gData  商品数据，array 
     * @param $codeName 二维码图片 
     * @param $fileName string 保存文件名,默认空则直接输入图片 
     */  
     //$gData,$codeName,$fileName = ''
    public function createSharePng($content,$detail)
    {
    	//生成的优惠券分享图片路径	
		$filePath = "./data/coupon/".($detail['id'] % 256)."/";	
		
		if (!file_exists($filePath)) {
			mkdir($filePath,0777,TRUE);
		}
		
		$fileName = $filePath.$detail['id'].".png";
		if (!file_exists($fileName)) {
			//提前准备好的二维码
			// $codeName = RESOURCE_STATIC."qdds/img/share/qrcode.png";
			$codeName = RESOURCE_STATIC."qdds/img/share/coupan/qrcode.png";
	
			//$content存入二维码图片 
			$image = QRcode::png($content,$codeName, 'L', 6, 2); 		
	
	         //创建画布  
	        $backgroudImg = APPLICATION_PATH.'/application/resource/coupon.jpg';
			
			$im = imagecreatefromstring(file_get_contents($backgroudImg));

			$width = imagesx ( $im );
	        //填充画布背景色  
	        $color = imagecolorallocate($im, 255, 255, 255);  
	        imagefill($im, 0, 0, $color);  
	      
	        //字体文件  
	        $font_file = APPLICATION_PATH.'/application/resource/msyhbd.ttc';  
	        $font_file_bold = APPLICATION_PATH.'/application/resource/2016.ttf';  
	
	        //设定字体的颜色  
	        $font_color_1 = ImageColorAllocate ($im, 242, 113, 154);  
	        $font_color_2 = ImageColorAllocate ($im, 190, 0, 0);  
 
	  
	      	$countInfo = $detail['use_type'] == 1?"店铺券":"商品券";
			
			if ($detail['sill_type'] == 2) {
				$countInfo = $countInfo."\n"."满".intval($detail['sill_price'])."元";
			}
			if ($detail['pre_type'] == 1) {
				$countInfo = $countInfo.""."减".intval($detail['pre_value'])."元";
			} else {
				$z = $detail['pre_value'] / 10;
				$countInfo = $countInfo.""."打".$z."折";
			}
			$fontSize = 23;
			$fontBox = imagettfbbox($fontSize, 0, $font_file, $detail['company']);//文字水平居中实质
			imagettftext($im, $fontSize,0, ceil(($width - $fontBox[2]) / 2), 40, $font_color_2 ,$font_file, $detail['company']);
			
	        //折扣优惠  
	        imagettftext($im, 30,0, 320, 150, $font_color_1 ,$font_file_bold, $countInfo);  
	      
	        //二维码  
	        list($code_w,$code_h) = getimagesize($codeName);  
	        $codeImg = $this->createImageFromFile($codeName);  
		
	        imagecopyresized($im, $codeImg, 146, 113, 0, 0, 156, 156, $code_w, $code_h); 
			if ($detail['time_type'] == 2) {
				imagettftext($im, 11,0, 288, 300, $color ,$font_file,'使用时间：         不限');   
			} else {
				$time = date("Y年m月d日H:i",strtotime($detail['start_time']))." 至 ".date("Y年m月d日H:i",strtotime($detail['end_time']));
				imagettftext($im, 9,0, 288, 300, $color ,$font_file,'使用时间：'.$time);  
			}
	      	
	        Header("Content-type: image/png");
			ob_start();
			ImagePng($im); 
			$image_data = ob_get_contents();
			ob_end_clean();
			echo $image_data;
			file_put_contents($fileName, $image_data);
	        //释放空间  
	        imagedestroy($im);  
	        imagedestroy($goodImg);  
	        imagedestroy($codeImg);  
		} else {
			Header("Content-type: image/png");
			echo readfile($fileName);
		}
    }  
      
    /** 
     *  从图片文件创建Image资源
     * @param $file 图片文件，支持url 
     * @return bool|resource    成功返回图片image资源，失败返回false 
     */  
    function createImageFromFile($file){  
        if(preg_match('/http(s)?:\/\//',$file)){  
            $fileSuffix = $this->getNetworkImgType($file);  
        }else{  
            $fileSuffix = pathinfo($file, PATHINFO_EXTENSION);  
        }  
      
        if(!$fileSuffix) return false;  
      
        switch ($fileSuffix){  
            case 'jpeg':  
                $theImage = @imagecreatefromjpeg($file);  
                break;  
            case 'jpg':  
                $theImage = @imagecreatefromjpeg($file);  
                break;  
            case 'png':  
                $theImage = @imagecreatefrompng($file);  
                break;  
            case 'gif':  
                $theImage = @imagecreatefromgif($file);  
                break;  
            default:  
                $theImage = @imagecreatefromstring(file_get_contents($file));  
                break;  
        }  
      
        return $theImage;  
    }  
      
    /** 
     * 获取网络图片类型 
     * @param $url  网络图片url,支持不带后缀名url 
     * @return bool 
     */  
    function getNetworkImgType($url)
    {
        $ch = curl_init(); //初始化curl  
        curl_setopt($ch, CURLOPT_URL, $url); //设置需要获取的URL  
        curl_setopt($ch, CURLOPT_NOBODY, 1);  
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);//设置超时  
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);  
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //支持https  
        curl_exec($ch);//执行curl会话  
        $http_code = curl_getinfo($ch);//获取curl连接资源句柄信息  
        curl_close($ch);//关闭资源连接  
       	//QRcode::png($url, 'L', 6, 2); 
        if ($http_code['http_code'] == 200) {  
            $theImgType = explode('/',$http_code['content_type']);  
      
            if($theImgType[0] == 'image'){  
                return $theImgType[1];  
            }else{  
                return false;  
            }  
        }else{  
            return false;  
        }  
    }  
      
    /** 
     * 分行连续截取字符串 
     * @param $str  需要截取的字符串,UTF-8 
     * @param int $row  截取的行数 
     * @param int $number   每行截取的字数，中文长度 
     * @param bool $suffix  最后行是否添加‘...’后缀 
     * @return array    返回数组共$row个元素，下标1到$row 
     */  
    function cn_row_substr($str,$row = 1,$number = 10,$suffix = true){  
        $result = array();  
        for ($r=1;$r<=$row;$r++){  
            $result[$r] = '';  
        }  
      
        $str = trim($str);  
        if(!$str) return $result;  
      
        $theStrlen = strlen($str);  
      
        //每行实际字节长度  
        $oneRowNum = $number * 3;  
        for($r=1;$r<=$row;$r++){  
            if($r == $row and $theStrlen > $r * $oneRowNum and $suffix){  
                $result[$r] = $this->mg_cn_substr($str,$oneRowNum-6,($r-1)* $oneRowNum).'...';  
            }else{  
                $result[$r] = $this->mg_cn_substr($str,$oneRowNum,($r-1)* $oneRowNum);  
            }  
            if($theStrlen < $r * $oneRowNum) break;  
        }  
      
        return $result;  
    }  
      
    /** 
     * 按字节截取utf-8字符串 
     * 识别汉字全角符号，全角中文3个字节，半角英文1个字节 
     * @param $str  需要切取的字符串 
     * @param $len  截取长度[字节] 
     * @param int $start    截取开始位置，默认0 
     * @return string 
     */  
    function mg_cn_substr($str,$len,$start = 0){  
        $q_str = '';  
        $q_strlen = ($start + $len)>strlen($str) ? strlen($str) : ($start + $len);  
      
        //如果start不为起始位置，若起始位置为乱码就按照UTF-8编码获取新start  
        if($start and json_encode(substr($str,$start,1)) === false){  
            for($a=0;$a<3;$a++){  
                $new_start = $start + $a;  
                $m_str = substr($str,$new_start,3);  
                if(json_encode($m_str) !== false) {  
                    $start = $new_start;  
                    break;  
                }  
            }  
        }  
      
        //切取内容  
        for($i=$start;$i<$q_strlen;$i++){  
            //ord()函数取得substr()的第一个字符的ASCII码，如果大于0xa0的话则是中文字符  
            if(ord(substr($str,$i,1))>0xa0){  
                $q_str .= substr($str,$i,3);  
                $i+=2;  
            }else{  
                $q_str .= substr($str,$i,1);  
            }  
        }  
        return $q_str;  
    }   
	
	
	/**
	 * 获得小程序商家二维码
	 */
	public function getwxaqrcode($domain)
    {
        $access_token = $this->AccessToken();
        $url = 'https://api.weixin.qq.com/wxa/getwxacode?access_token='.$access_token;
        $path = "pages/index?domain=".$domain;
        $width = 430;
        $data = '{"path":"'.$path.'","width":'.$width.'}';
        $return = YDLib::curlPostRs($url,$data);
		
		$im = imagecreatefromstring($return);
		
		$logor = RESOURCE_STATIC."common/images/minir.png";
        list($r_w,$r_h) = getimagesize($logor);  
        $logor = $this->createImageFromFile($logor);  
        imagecopyresized($im, $logor, 140, 140, 0, 0, 150, 150, $r_w, $r_h);  		
		
		$logo = RESOURCE_STATIC."common/images/mini.png";
		
        list($g_w,$g_h) = getimagesize($logo);  
        $logo = $this->createImageFromFile($logo);  
        imagecopyresized($im, $logo, 115, 115, 0, 0, 200, 200, $g_w, $g_h);  
		
		Header("Content-type: image/png");		
		ob_start();
		ImagePng($im); 
		$image_data = ob_get_contents();
		ob_end_clean();
		
		echo $image_data;		
    }

	/**
	 * 获得小程序授权信息
	 */	
	private function AccessToken()
    {
    	$mem = YDLib::getMem('memcache');
        $key = __CLASS__."::".__FUNCTION__;
        $AccessToken = 0;$mem->get($key);
		if (empty($AccessToken)) {
			$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . MINI_WEIXIN_OPENID . '&secret=' . MINI_WEIXIN_APPSECRET;
	        $AccessToken = YDLib::curlPostRs($url);
	        $AccessToken = json_decode($AccessToken , true);
	        $AccessToken = $AccessToken['access_token'];
			$mem->set($key,$AccessToken);
		}
        return $AccessToken;
    } 
    

    /*
     *会员推广
     *
     */
    function GradePromote($content,$detail)
    {
    	//生成的会员推广分享图片路径
    	$filePath = "./data/user/".($detail['id'] % 256)."/";
    	if (!file_exists($filePath)) {
    		mkdir($filePath,0777,TRUE);
    	}
    	$fileName = $filePath.$detail['id'].".png";
    	if (!file_exists($fileName)) {
    		//提前准备好的二维码
    		 
    		$codeName = RESOURCE_STATIC."qdds/img/share/qrcode.png";
    
    		//$content存入二维码图片
    		$image = QRcode::png($content,$codeName, 'L', 6, 0);
    
    		 
    		//二维码
    		list($code_w,$code_h) = getimagesize($codeName);
    		$codeImg = $this->createImageFromFile($codeName);
    
    		Header("Content-type: image/png");
    		ob_start();
    		ImagePng($codeImg);
    		$image_data = ob_get_contents();
    		ob_end_clean();
    		 
    		echo $image_data;
    		file_put_contents($fileName, $image_data);
    		//释放空间
    		imagedestroy($codeImg);
    	} else {
    		Header("Content-type: image/png");
    		echo readfile($fileName);
    	}
    }
    
    
    /*
     *
     * 分享竞价拍
     * @param $content 分享内容
     * @param $detail 竞价拍数据，array
     */
    function qrcodeBigding($content,$detail)
    {
    	 
    	//生成的竞价拍分享图片路径
    	$filePath = "./data/bigding/".($detail['id'] % 256)."/";
    	if (!file_exists($filePath)) {
    		mkdir($filePath,0777,TRUE);
    	}
    
    	if (!file_exists($fileName)) {
    		//提前准备好的二维码
    		$codeName = RESOURCE_STATIC."qdds/img/share/qrcode.png";
    
    		//$content存入二维码图片
    		$image = QRcode::png($content,$codeName, 'L', 6, 0);
    
    		//创建画布
    		$backgroudImg = APPLICATION_PATH.'/application/resource/bigdding.jpg';
    			
    		$im = imagecreatefromstring(file_get_contents($backgroudImg));
    
    		$width = imagesx ( $im );
    		 
    		//字体文件
    		$font_file = APPLICATION_PATH.'/application/resource/msyhbd.ttc';
    		$font_file_bold = APPLICATION_PATH.'/application/resource/2016.ttf';
    
    		
    		$font_color_4 = ImageColorAllocate ($im, 255, 0, 0);
    		 
    		 
    		//二维码
    		list($code_w,$code_h) = getimagesize($codeName);
    		$codeImg = $this->createImageFromFile($codeName);
    		imagecopyresized($im, $codeImg, 230, 157, 0, 0, 140, 140, $code_w, $code_h);
    			
    		$topY = 635;
    		$topFontY = 30;
    		$fontX = 40;
    		
    		$product_name = $this->gbsubstr2($detail['product_name'],0,17);
    		
    		$topY += $topFontY;
    		imagettftext($im, 18,0, $fontX, $topY, $font_color_2 ,$font_file,$product_name );
    		$topY += 40;
    		imagettftext($im, 16,0, $fontX, $topY, $font_color_2 ,$font_file, '市场价格：' . $detail['bigding_price']);
    		$topY += 30;
    		imagettftext($im, 16,0, $fontX, $topY, $font_color_2 ,$font_file, '加价幅度：' . $detail['bid_lncrement']);
    		$topY += 30;
    		imagettftext($im, 16,0, $fontX, $topY, $font_color_2 ,$font_file, '限购数量：' . '1');
    		
    		imagettftext($im, 18,0, 420, 675, $font_color_4 ,$font_file,  $detail['start_price'].' 元');
    		
    			
    		Header("Content-type: image/png");
    		ob_start();
    		ImagePng($im);
    		$image_data = ob_get_contents();
    		ob_end_clean();
    			
    		echo $image_data;
    		file_put_contents($fileName, $image_data);
    		//释放空间
    		imagedestroy($im);
    		imagedestroy($goodImg);
    		imagedestroy($codeImg);
    	} else {
    		Header("Content-type: image/png");
    		echo readfile($fileName);
    	}
    }
    
    
   
/**
   * 该函数是对于utf8编码
   * @author 2582308253@qq.com
   * @param string $str
   * @param int $start
   * @param int $length
   * @return string
   */
  function gbsubstr2($str, $start, $length) {
    $length = abs($length);
    $strLen = strlen($str);
    $len = $start + $length;
    $newStr = '';
    for($i = $start; $i < $len && $i < $strLen; $i++) {
      if(ord(substr($str, $i, 1)) > 0xa0) {
        //utf8编码中一个汉字是占据3个字节的，对于其他的编码的字符串，中文占据的字节各有不同，自己需要去修改这个数a
        $newStr .= substr($str, $i, 3);//此处a=3;
        $i+=2;
        $len += 2; //截取了三个字节之后，截取字符串的终止偏移量也要随着每次汉字的截取增加a-1;
      } else {
        $newStr .= substr($str, $i, 1);
      }
    }
    return $newStr;
  }
    
	/**
     * 分享拼团商品
     */
    public function  shareGroup($content,$detail)
    {
   		//生成的拼团分享图片路径	
		$filePath = "./data/group/".($detail['id'] % 256)."/";	
		
		if (!file_exists($filePath)) {
			mkdir($filePath,0777,TRUE);
		}
		
		if (!file_exists($fileName)) {
			//提前准备好的二维码
			$codeName = RESOURCE_STATIC."qdds/img/share/coupan/qrcode.png";
	
			//$content存入二维码图片 
			$image = QRcode::png($content,$codeName, 'L', 6, 2); 		
	
	         //创建画布  
	        $backgroudImg = APPLICATION_PATH.'/application/resource/pintuan.jpg';
			
	       
			$im = imagecreatefromstring(file_get_contents($backgroudImg));

			$width = imagesx ( $im );
	        //填充画布背景色  
	        $color = imagecolorallocate($im, 255, 255, 255);  
	        imagefill($im, 0, 0, $color);  
	      
	        //字体文件  
	        $font_file = APPLICATION_PATH.'/application/resource/msyhbd.ttc';  
	        $font_file_bold = APPLICATION_PATH.'/application/resource/2016.ttf';  
	
	        //设定字体的颜色  
	        $font_color_1 = ImageColorAllocate ($im, 140, 140, 140);  
	        $font_color_2 = ImageColorAllocate ($im, 28, 28, 28);  
	        $font_color_3 = ImageColorAllocate ($im, 129, 129, 129);  
	        $font_color_4 = ImageColorAllocate ($im, 236, 180, 182);  
	      
	        $fang_bg_color = ImageColorAllocate ($im, 254, 216, 217);  
	      
	        //宣传图片  
	        $goodImg = '';
	        if (!empty($detail['logo_url'])) {
				$goodImg = HOST_FILE.$detail['logo_url'];
			} else {
				$goodImg = RESOURCE_STATIC."common/images/common.png";
			}

	        list($g_w,$g_h) = getimagesize($goodImg);  
	        $goodImg = $this->createImageFromFile($goodImg);  
	        imagecopyresized($im, $goodImg, 50, 680, 0, 0, 350, 350, $g_w, $g_h);  
	      
	        //二维码  
	        list($code_w,$code_h) = getimagesize($codeName);  
	        $codeImg = $this->createImageFromFile($codeName);  
			$topY = 630;
			$topFontY = 100;
			$fontX = 410;
	        imagecopyresized($im, $codeImg, 600, 960, 0, 0, 150, 150, $code_w, $code_h);
	        $dataname = $this->hanzi_insert('商品名称：'.$detail['product_name'],10,"\n");
	        //优惠券描述 
	        $topY += $topFontY;
	        imagettftext($im, 24,0, $fontX, $topY, $font_color_2 ,$font_file, $dataname);  
	        $topY += 150;
	        imagettftext($im, 24,0, $fontX, $topY, $font_color_2 ,$font_file, '商品原价：' . $detail['sale_price']);
			 
			$length = mb_strlen('商品原价：' . $detail['sale_price']);
	        $red1 = imagecolorallocate($im,255,0,0);//创建一个颜色，以供使用
	        for ($i=0; $i < 5; $i++) { 
				 imageline($im,$fontX,865+$i,$length*26+$fontX,865+$i,$red1);//画一条直线
			}	
					
	        $topY += 60;
	        imagettftext($im, 24,0, $fontX, $topY, $font_color_2 ,$font_file, '拼团价：' . $detail['group_price']);
	        $topY += 130;
	        imagettftext($im, 16,0, 90, $topY, $font_color_2 ,$font_file, "微信扫描二维码即可拼团\n          或店内详情" );
			
			
	        imagettftext($im, 24,0, 150, 500, $font_color_2 ,$font_file, '开始时间：' . $detail['starttime']);
	        imagettftext($im, 24,0, 150, 550, $font_color_2 ,$font_file, '结束时间：' . $detail['endtime']);

			


			// $fontSize = 16;
			// $fontBox = imagettfbbox($fontSize, 0, $font_file, $detail['company']);//文字水平居中实质
			// imagettftext($im, $fontSize,0, ceil(($width - $fontBox[2]) / 2), 1100, $font_color_4 ,$font_file, $detail['company']);
			
	        Header("Content-type: image/png");
			ob_start();
			ImagePng($im); 
			$image_data = ob_get_contents();
			ob_end_clean();
			
			echo $image_data;
			file_put_contents($fileName, $image_data);
	        //释放空间  
	        imagedestroy($im);  
	        imagedestroy($goodImg);  
	        imagedestroy($codeImg);  
		} else {
			Header("Content-type: image/png");
			echo readfile($fileName);
		}
    }	    
    
}
?>
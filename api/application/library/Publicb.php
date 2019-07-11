<?php
/**
 * 公共类库
 * @author hxg
 * @time 2018-5-03
 */
use Custom\YDLib;
use User\UserModel;
use Common\Crypt3Des;
use Score\UserScoreModel;
use Order\OrderChildModel;
use Score\ScoreRuleModel;
class Publicb {
	/*
	 * 获取IP
	 */
	public static function GetIP() {
		$ip = false;
		if (! empty ( $_SERVER ["HTTP_CLIENT_IP"] )) {
			$ip = $_SERVER ["HTTP_CLIENT_IP"];
		}
		if (! empty ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) {
			$ips = explode ( ", ", $_SERVER ['HTTP_X_FORWARDED_FOR'] );
			if ($ip) {
				array_unshift ( $ips, $ip );
				$ip = FALSE;
			}
			for($i = 0; $i < count ( $ips ); $i ++) {
				if (! preg_match ( "/^(10|172.16|192.168)./", $ips [$i] )) {
					$ip = $ips [$i];
					break;
				}
			}
		}
		return ($ip ? $ip : $_SERVER ['REMOTE_ADDR']);
	}
	
	/**
	 * 通过CURL发送HTTP请求
	 * 
	 * @param string $url
	 *        	//请求URL
	 * @param array $postFields
	 *        	//请求参数
	 * @return mixed
	 */
	public static function curlPost($url, $data) {
		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_URL, $url );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
		if (! empty ( $data )) {
			curl_setopt ( $curl, CURLOPT_POST, 1 );
			curl_setopt ( $curl, CURLOPT_POSTFIELDS, $data );
		}
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
		$output = curl_exec ( $curl );
		curl_close ( $curl );
		return $output;
	}
	
	/**
	 * 生成数字+字母验证码
	 * 
	 * @param int $num
	 *        	验证码位数
	 * @param int $w
	 *        	生成图片的宽度
	 * @param int $h
	 *        	生成图片的高度
	 * @param string $session
	 *        	生成session的名称
	 * @return img
	 */
	public static function getCode($num, $w, $h, $session = NULL) {
		$str = "23456789abcdefghijkmnpqrstuvwxyz";
		$code = '';
		for($i = 0; $i < $num; $i ++) {
			$code .= $str [mt_rand ( 0, strlen ( $str ) - 1 )];
		}
		$mem = YDLib::getMem ( 'memcache' );
		$mem->set ( $session . '_' . SUPPLIER_ID . '_' . session_id (), $code );
		
		Header ( "Content-type: image/PNG" );
		$im = imagecreate ( $w, $h );
		$black = imagecolorallocate ( $im, mt_rand ( 0, 200 ), mt_rand ( 0, 120 ), mt_rand ( 0, 120 ) );
		$gray = imagecolorallocate ( $im, 118, 151, 199 );
		$bgcolor = imagecolorallocate ( $im, 235, 236, 237 );
		imagefilledrectangle ( $im, 0, 0, $w, $h, $bgcolor );
		imagerectangle ( $im, 0, 0, $w - 1, $h - 1, $gray );
		$style = array (
				$black,
				$black,
				$black,
				$black,
				$black,
				$gray,
				$gray,
				$gray,
				$gray,
				$gray 
		);
		
		imagesetstyle ( $im, $style );
		$y1 = rand ( 0, $h );
		$y2 = rand ( 0, $h );
		$y3 = rand ( 0, $h );
		$y4 = rand ( 0, $h );
		imageline ( $im, 0, $y1, $w, $y3, IMG_COLOR_STYLED );
		imageline ( $im, 0, $y2, $w, $y4, IMG_COLOR_STYLED );
		
		for($i = 0; $i < 200; $i ++) {
			imagesetpixel ( $im, rand ( 0, $w ), rand ( 0, $h ), $black );
		}
		$strx = rand ( 15, 30 );
		
		for($i = 0; $i < $num; $i ++) {
			$strpos = rand ( 22, 28 );
			// imagestring($im, 5, $strx, $strpos, substr($code, $i, 1), $black);
			imagettftext ( $im, 20, 0, $strx, $strpos, $black, APPLICATION_PATH . '/application/resource/pingfang.ttf', substr ( $code, $i, 1 ) );
			$strx += rand ( 10, 15 );
		}
		
		imagepng ( $im );
		imagedestroy ( $im );
	}
	
	/**
	 * 输出pdf格式文件
	 * 
	 * @param string $html
	 *        	文件内容
	 * @param string $name
	 *        	文件名称
	 * @return pdf
	 */
	public static function out_pdf($html, $name) {
		$_loader = new \Houhouyun\Loader ( APP_PATH, APP_PATH . '/../houhouyun' );
		$_loader->loaderClass ( "phpPdf" );
		$pdf = new phpPdf ( 'P', 'mm', 'A4', true, 'UTF-8', false );
		// 去除页眉
		$pdf->setPrintHeader ( false );
		// 设置间距
		$pdf->SetMargins ( 10, 10, 10, 10 );
		$pdf->SetFooterMargin ( 10 );
		// 设置分页
		$pdf->SetAutoPageBreak ( TRUE, 25 );
		// 设置字体
		$pdf->SetFont ( 'stsongstdlight', '', 12 );
		$pdf->AddPage ();
		
		$pdf->writeHTML ( $html );
		
		// 输出PDF
		$pdf->Output ( $name . '.pdf', 'I' ); // I查看， D下载
	}
	
	/**
	 * 输出xls格式文件
	 * 
	 * @param array $data
	 *        	文件内容
	 * @param string $name
	 *        	文件名称
	 * @return pdf
	 */
	public static function out_xls($data, $name) {
		$_loader = new \Houhouyun\Loader ( APP_PATH, APP_PATH . '/../houhouyun' );
		if (! class_exists ( 'PHPExcel' )) {
			$_loader->loaderClass ( "phpExcel" );
		}
		$objPHPExcel = new PHPExcel ();
		// 从数据库输出数据处理方式
		$rcount = count ( $data );
		for($i = 0; $i < $rcount; $i ++) {
			$ccount = count ( $data [$i] );
			$ccount = $ccount > 26 ? 26 : $ccount;
			$iterator = 0;
			foreach ( $data [$i] as $key => $value ) {
				if ($iterator < $ccount) {
					$objPHPExcel->getActiveSheet ()->setCellValueExplicit ( chr ( ord ( 'A' ) + $iterator ) . ($i + 1), $value, PHPExcel_Cell_DataType::TYPE_STRING );
				}
				$iterator ++;
			}
		}
		$objPHPExcel->getActiveSheet ()->setCellValue ( 'A1000', '' );
		$outputFileName = $name . '.xls';
		$xlsWriter = new PHPExcel_Writer_Excel5 ( $objPHPExcel );
		header ( "Content-Type: application/force-download" );
		header ( "Content-Type: application/octet-stream" );
		header ( "Content-Type: application/download" );
		header ( 'Content-Disposition:inline;filename="' . $outputFileName . '"' );
		header ( "Content-Transfer-Encoding: binary" );
		header ( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
		header ( "Last-Modified: " . gmdate ( "D, d M Y H:i:s" ) . " GMT" );
		header ( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
		header ( "Pragma: no-cache" );
		$xlsWriter->save ( 'php://output' );
	}
	
	/*
	 * 功能：php实现下载远程图片保存到本地
	 * 参数：文件url,保存文件目录,保存文件名称，使用的下载方式
	 * 当保存文件名称为空时则使用远程文件原来的名称
	 */
	public static function getImage($url, $type = 1) {
		$_CLASSIFY = array (
				1 => 'head',
				2 => 'tosu',
				3 => 'diandnag',
				4 => 'other' 
		);
		$classify = isset ( $_CLASSIFY [$type] ) ? $_CLASSIFY [$type] : "";
		$save_dir = $classify . "/" . date ( "Y/m/d" ) . "/";
		
		// 创建保存目录
		if (! file_exists ( RESOURCE_FILE . 'upload/' . $save_dir ) && ! mkdir ( RESOURCE_FILE . 'upload/' . $save_dir, 0777, true )) {
			return array (
					'file_name' => '',
					'save_path' => '',
					'error' => 5 
			);
		}
		
		// 获取远程文件所采用的方法
		$header = array (
				"Connection: Keep-Alive",
				"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
				"Pragma: no-cache",
				"Accept-Language: zh-Hans-CN,zh-Hans;q=0.8,en-US;q=0.5,en;q=0.3",
				"User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:29.0) Gecko/20100101 Firefox/29.0" 
		);
		
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $header );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt ( $ch, CURLOPT_ENCODING, 'gzip,deflate' );
		
		$img = curl_exec ( $ch );
		
		$curlinfo = curl_getinfo ( $ch );
		
		// 关闭连接
		curl_close ( $ch );
		
		if ($curlinfo ['http_code'] == 200) {
			if ($curlinfo ['content_type'] == 'image/jpeg') {
				$exf = '.jpg';
			} else if ($curlinfo ['content_type'] == 'image/png') {
				$exf = '.png';
			} else if ($curlinfo ['content_type'] == 'image/gif') {
				$exf = '.gif';
			}
		}
		$filename = date ( "YmdHis" ) . uniqid () . $exf;
		$res = file_put_contents ( RESOURCE_FILE . 'upload/' . $save_dir . $filename, $img );
		return '/upload/' . $save_dir . $filename;
	}
	public static function loginCookie($user_id)
    {
		setcookie ( UserModel::$_userLogin, Crypt3Des::encrypt ( $user_id ), time () + 180000, "/", COOKIE_DOMAIN );
        //后台修改密码或其他操作后需要再次登陆：登陆成功后删除cache标识
		$mem = YDLib::getMem ( 'memcache' );
        $key = UserModel::$log_validation. $user_id;
        $cacheUser = $mem->get ( $key );
        YDLib::testLog($key);
        YDLib::testLog($cacheUser);
        if ($cacheUser) {
            $mem->delete ( $key );
            $cacheUser = $mem->get ( $key );
            YDLib::testLog($cacheUser);
        }
	}

    /**
     * 获取登录token
     * @param $user_id
     * @return string
     */
	public static function getLoginToken($user_id)
    {
        $tokenService = new \Services\Auth\TokenAuthService();
        $tokenService->setUserId($user_id);
        $tokenService->setUserType('user');
        return (string)$tokenService->getJWTToken();
    }

    /**
     * 获取商户登录token
     * @param $user_id
     * @param $supplier_id
     * @return string
     */
    public static function getAdminLoginToken($user_id,$supplier_id)
    {
        $tokenService = new \Services\Auth\TokenAuthService();
        $tokenService->setUserId($user_id);
        $tokenService->setUserType('admin');
        $tokenService->setSupplierId($supplier_id);
        return (string)$tokenService->getJWTToken();
    }


}

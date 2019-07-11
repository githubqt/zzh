<?php

/**
 * 权限model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-05
 */
namespace Sms;

use Overtrue\Pinyin;
use Custom\YDLib;
use Common\CommonBase;
use User\UserModel;
use Sms\SmsModelModel;
use Pushmsg\PushmsgModel;
use Core\SmsSingleSender;
use Core\ChuanglanSmsApi;
use ErrnoStatus;
use Pushmsg\PushmsgMassModel;
use Supplier\SupplierModel;

class SmsModel extends \Common\CommonBase {
	protected static $_tableName = 'sms_log';
	private function __construct() {
		parent::__construct ();
	}
	
	/* 获取列表 */
	public static function getList($attribute = array(), $page = 0, $rows = 10) {
		if (! empty ( $attribute ['info'] ) && is_array ( $attribute ['info'] ) && count ( $attribute ['info'] ) > 0) {
			extract ( $attribute ['info'] );
		}
		
		$pdo = YDLib::getPDO ( 'db_r' );
        $fields = " a.* ";
		$sql = 'SELECT 
        		   [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a 
		        WHERE
        		    a.is_del = 2
        		';
		
		if (isset ( $name ) && ! empty ( $name )) {
			$sql .= " AND a.name like '%" . $name . "%' ";
		}
		
		if (isset ( $en_name ) && ! empty ( $en_name )) {
			$sql .= " AND a.en_name like '%" . $en_name . "%' ";
		}
		
		if (isset ( $alias_name ) && ! empty ( $alias_name )) {
			$sql .= " AND a.alias_name like '%" . $alias_name . "%' ";
		}
		
		if (isset ( $start_time ) && isset ( $end_time ) && ! empty ( $start_time ) && ! empty ( $end_time )) {
			$sql .= " AND a.created_at >= '" . $start_time . " 00:00:00' ";
			$sql .= " AND a.created_at <= '" . $end_time . " 23:59:59' ";
		}
		
		$result ['list'] = $pdo->YDGetAll ( str_replace ( "[*]", $fields, $sql ) );
		
		$result ['total'] = $pdo->YDGetOne ( str_replace ( "[*]", "count(*) as num", $sql ) );
		if ($result) {
			return $result;
		} else {
			return false;
		}
	}
	
	/**
	 * 添加
	 *
	 * @param array $info        	
	 * @return mixed
	 *
	 */
	public static function addData($info) {
		$db = YDLib::getPDO ( 'db_w' );
		$info ['is_del'] = '2';
		$info ['created_at'] = date ( "Y-m-d H:i:s" );
		$info ['updated_at'] = date ( "Y-m-d H:i:s" );
		$result = $db->insert ( self::$_tableName, $info, [ 
				'ignore' => true 
		] );
		
		return $result;
	}
	
	/**
	 * 获取单条数据
	 *
	 * @param interger $id        	
	 * @return mixed
	 *
	 */
	public static function getInfoByID($id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['id'] = intval ( $id );
		
		$pdo = self::_pdo ( 'db_r' );
		return $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->getRow ();
	}
	
	/**
	 * 验证码是否过期
	 * 
	 * @param string $code
	 *        	验证码
	 * @param string $mobile
	 *        	手机号
	 * @return boolean 结果
	 */
	public static function codeIsYes($code, $mobile, $type = '1') {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['mobile'] = trim ( $mobile );
		$where ['code'] = $code;
		$where ['status'] = '2';
		$where ['supplier_id'] = SUPPLIER_ID;
		$where ['sms_type'] = $type;
		$pdo = self::_pdo ( 'db_r' );
		$info = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $where )->order ( 'id DESC' )->getRow ();
		if ($info) {
			// 是否过去判断
			if ((strtotime ( $info ['created_at'] ) + $info ['valid']) < time ()) {
				return false;
			}
		} else {
			return false;
		}
		
		return true;
	}
	
	/**
	 * 查询当天发送成功短信记录数
	 * 
	 * @param string $mobile
	 *        	手机号
	 * @param int $sms_type
	 *        	短信类型
	 * @return int
	 */
	public static function getTodayNum($mobile, $sms_type = 0) {
		$pdo = self::_pdo ( 'db_r' );
		$sql = "SELECT
					count(1) AS num
				FROM
					" . CommonBase::$_tablePrefix . self::$_tableName . "
				WHERE
					mobile = {$mobile}
				AND
					sms_type = {$sms_type}
				AND
					to_days(created_at) = to_days(now())
				AND
					is_del = " . CommonBase::DELETE_SUCCESS . "
		        AND
		            status = " . CommonBase::STATUS_SUCCESS . "
		        AND
		            supplier_id = " . SUPPLIER_ID;
		return $pdo->YDGetRow ( $sql ) ['num'];
	}
	
	/**
	 * 查询最新一条发送成功短信记录
	 * 
	 * @param string $mobile
	 *        	手机号
	 * @param int $sms_type
	 *        	短信类型
	 * @return array
	 */
	public static function getLastOne($mobile, $sms_type = 0) {
		$pdo = self::_pdo ( 'db_r' );
		$sql = "SELECT
					*
				FROM
					" . CommonBase::$_tablePrefix . self::$_tableName . "
				WHERE
					mobile = {$mobile}
				AND
					sms_type = {$sms_type}
				AND
					is_del = " . CommonBase::DELETE_SUCCESS . "
		        AND
		            status = " . CommonBase::STATUS_SUCCESS . "
		        AND
		            supplier_id = " . SUPPLIER_ID . "
				ORDER BY id DESC";
		return $pdo->YDGetRow ( $sql );
	}
	
	/**
	 * 根据一条自增ID更新表记录
	 * 
	 * @param array $data
	 *        	更新字段作为key的数组
	 * @param array $id
	 *        	表自增id
	 * @return boolean 更新结果
	 */
	public static function updateByID($data, $id) {
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'id' => intval ( $id ) 
		) );
	}
	
	/**
	 * 根据表自增 ID删除记录
	 * 
	 * @param int $id
	 *        	表自增 ID
	 * @return boolean 删除是否成功
	 */
	public static function deleteByID($id) {
		$data ['is_del'] = self::DELETE_FAIL;
		$data ['updated_at'] = date ( "Y-m-d H:i:s" );
		$data ['deleted_at'] = date ( "Y-m-d H:i:s" );
		
		$pdo = self::_pdo ( 'db_w' );
		return $pdo->update ( self::$_tableName, $data, array (
				'id' => intval ( $id ) 
		) );
	}
	
	/**
	 * 群发短信
	 * 
	 * @param array $data
	 *        	短信参数
	 *        	$data['mobiles'] 手机号数组
	 *        	$data['content'] 短信内容
	 */
	public static function SendSmsMass($data) {
		
		$mobile_num = count($data ['mobiles']);
		$mobiles = implode(",",$data ['mobiles']);
		
		/**
		 * 短信长度判断规则
		 * 1.英文，符号，汉字算一个字
		 * 2.长度小于等于70，算一条短信
		 * 3.长度大于70，每条短信按照67个字来算。
		 * */
		$content_length = mb_strlen($data ['content']);
		$content_num = 1;
		if ($content_length > 70) {
			$content_num = ceil($content_length / 67);
		}
		
		$num = bcmul($mobile_num,$content_num);//短信条数
		
		// 商家短信剩余数量
		$smsCount = PushmsgModel::getContent ();
		if ($smsCount ['remain_num'] < $num) {
			YDLib::output ( ErrnoStatus::STATUS_50022 );
		}		

		$singleSender = new ChuanglanSmsApi ();
		$result = $singleSender->sendSMS ($mobiles,$data ['content']);
		$res = json_decode($result,true);
		if(is_null($res) || !isset($res['code']) || !$res['code']=='0'){
			YDLib::testlog ($result);
			YDLib::output ( ErrnoStatus::STATUS_60003 );
		}
		
		// 更新短信统计
		$upsms = [ ];
		$upsms ['remain_num'] = $smsCount ['remain_num'] - $num;
		$upsms ['use_num'] = $smsCount ['use_num'] + $num;
		$res = PushmsgModel::updateByID ( $upsms, $smsCount ['id'] );
		if ($res === FALSE) {
			YDLib::output ( ErrnoStatus::STATUS_60003 );
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
	}
	
	/**
	 * 群发短信
	 * 
	 * @param array $data 群发详情
	 */
	public static function SendSmsMassID($data) {	
		// 商家短信剩余数量
		$smsCount = PushmsgModel::getContent ();
		if ($smsCount ['remain_num'] < $data['sms_num_total']) {
			YDLib::output ( ErrnoStatus::STATUS_50022 );
		}			

		$singleSender = new ChuanglanSmsApi ();
		$result = $singleSender->sendSMS ($data ['mobiles'],$data ['content']);
		$res = json_decode($result,true);
		if(is_null($res) || !isset($res['code']) || !$res['code']=='0'){
			YDLib::testlog ($result);
			YDLib::output ( ErrnoStatus::STATUS_60003 );
		}
		
		// 更新群发状态
		$updata = [ ];
		$updata ['sms_num_ok'] = $data['sms_num_total'];
		$updata ['mobile_num_ok'] = $data['mobile_num_total'];
		$updata ['result'] = $result;
		$updata ['status'] = PushmsgMassModel::MASS_STATUS_3;
		$res = PushmsgMassModel::updateByID ( $updata, $data ['id'] );
		if ($res === FALSE) {
			YDLib::output ( ErrnoStatus::STATUS_40118 );
		}		
		
		// 更新短信统计
		$upsms = [ ];
		$upsms ['remain_num'] = $smsCount ['remain_num'] - $data['sms_num_total'];
		$upsms ['use_num'] = $smsCount ['use_num'] + $data['sms_num_total'];
		$res = PushmsgModel::updateByID ( $upsms, $smsCount ['id'] );
		if ($res === FALSE) {
			YDLib::output ( ErrnoStatus::STATUS_60003 );
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
	}

	/**
	 * 发送短信
	 * 
	 * @param array $data
	 *        	短信参数
	 *        	$data['mobile'] 手机号
	 *        	$data['model_id'] 模板ID
	 *        	$data['params'] 模板参数
	 */
	public static function SendSms($data) {
		$info = [ ];
		$info ['sms_type'] = $data ['sms_type'] ? $data ['sms_type'] : '0';
		$info ['code'] = $data ['code'] ? $data ['code'] : '';
		$info ['valid'] = $data ['valid'] ? $data ['valid'] : '0';
		$info ['mobile'] = $data ['mobile'];
		$info ['type'] = '2';
		$info ['supplier_id'] = SUPPLIER_ID;
		$info ['status'] = '1';
		$info ['model_id'] = $data ['model_id'];
		$info ['note'] = '';
		
		// 通过手机号查询用户ID
		$user_info = UserModel::getInfoByMobile ( $info ['mobile'] );
		if ($user_info) {
			$info ['user_id'] = $user_info ['id'];
		} else {
			$info ['user_id'] = '0';
		}
		
		// 通过模板ID查询模板内容
		$model_info = SmsModelModel::getInfoByID ( $info ['model_id'] );
		$content = $model_info ['content'];
		if ($data ['params']) {
    		foreach ( $data ['params'] as $key => $value ) {
    			$content = str_replace ( '{' . ($key + 1) . '}', $value, $content );
    		}
		}
        $supplier_info = SupplierModel::getInfoByID(SUPPLIER_ID);
		$info ['content'] = '【' . $supplier_info['sms_name'] . '】' . $content;
		$last_id = self::addData ( $info );
		if ($last_id == false) {
			YDLib::output ( ErrnoStatus::STATUS_60003 );
		}
		
		// 商家短信剩余数量
		$smsCount = PushmsgModel::getContent ();
		if ($smsCount ['remain_num'] <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_50022 );
		}
		
		// 发短信
		// $singleSender = new SmsSingleSender ();
		// $result = $singleSender->sendWithParam ( "86", $info ['mobile'], $model_info ['templId'], $data ['params'], SMS_NAME, "" );
		// $res = json_decode ( $result, true );
		// if ($res ['result'] != '0') {
			// YDLib::output ( ErrnoStatus::STATUS_60003 );
		// }
		
		$singleSender = new ChuanglanSmsApi ();
		$result = $singleSender->sendSMS ($info ['mobile'],$info ['content']);
		$res = json_decode($result,true);
		if(is_null($res) || !isset($res['code']) || !$res['code']=='0'){
			YDLib::testlog ($result);
			YDLib::output ( ErrnoStatus::STATUS_60003 );
		}
		
		$content_length = mb_strlen($info['content']);
		$content_num = 1;
		if ($content_length > 70) {
		    $content_num = ceil($content_length / 67);
		}
		
		$updata = [ ];
		$updata ['status'] = '2';
		$updata ['old_num'] = $smsCount ['remain_num'];
		$updata ['new_num'] = $smsCount ['remain_num'] - $content_num;
		$updata ['result'] = $result;
		$res = self::updateByID ( $updata, $last_id );
		if (! $res) {
			YDLib::output ( ErrnoStatus::STATUS_60003 );
		}
		
		// 更新短信统计
		$upsms = [ ];
		$upsms ['remain_num'] = $smsCount ['remain_num'] - $content_num;
		$upsms ['use_num'] = $smsCount ['use_num'] + $content_num;
		$res = PushmsgModel::updateByID ( $upsms, $smsCount ['id'] );
		if ($res === FALSE) {
			YDLib::output ( ErrnoStatus::STATUS_60003 );
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
	}
	
	
	
	
	
	
	/**
	 * 发送短信
	 *
	 * @param array $data
	 *        	短信参数
	 *        	$data['mobile'] 手机号
	 *        	$data['model_id'] 模板ID
	 *        	$data['params'] 模板参数
	 */
	public static function SendSmsJustFire($data) {
	    $info = [ ];
	    $info ['sms_type'] = $data ['sms_type'] ? $data ['sms_type'] : '0';
	    $info ['code'] = $data ['code'] ? $data ['code'] : '';
	    $info ['valid'] = $data ['valid'] ? $data ['valid'] : '0';
	    $info ['mobile'] = $data ['mobile'];
	    $info ['type'] = '2';
	    $info ['supplier_id'] = SUPPLIER_ID;
	    $info ['status'] = '1';
	    $info ['model_id'] = $data ['model_id'];
	    $info ['note'] = '';
	
	    // 通过手机号查询用户ID
	    $user_info = UserModel::getInfoByMobile ( $info ['mobile'] );
	    if ($user_info) {
	        $info ['user_id'] = $user_info ['id'];
	    } else {
	        $info ['user_id'] = '0';
	    }
	
	    // 通过模板ID查询模板内容
	    $model_info = SmsModelModel::getInfoByID ( $info ['model_id'] );
	    $content = $model_info ['content'];
	    if ($data ['params']) {
	        foreach ( $data ['params'] as $key => $value ) {
	            $content = str_replace ( '{' . ($key + 1) . '}', $value, $content );
	        }
	    }
	    $supplier_info = SupplierModel::getInfoByID(SUPPLIER_ID);
	    $info ['content'] = '【' . $supplier_info['sms_name'] . '】' . $content;
	    $last_id = self::addData ( $info );
	    if ($last_id == false) {
	        YDLib::testLog( ErrnoStatus::STATUS_60003 );
	        return false;
	    }
	
	    // 商家短信剩余数量
	    $smsCount = PushmsgModel::getContent ();
	    if ($smsCount ['remain_num'] <= 0) {
	        YDLib::testLog( ErrnoStatus::STATUS_50022 );
	        return false;
	    }
	
	    // 发短信
	    // $singleSender = new SmsSingleSender ();
	    // $result = $singleSender->sendWithParam ( "86", $info ['mobile'], $model_info ['templId'], $data ['params'], SMS_NAME, "" );
	    // $res = json_decode ( $result, true );
	    // if ($res ['result'] != '0') {
	    // YDLib::output ( ErrnoStatus::STATUS_60003 );
	    // }
	
	    $singleSender = new ChuanglanSmsApi ();
	    $result = $singleSender->sendSMS ($info ['mobile'],$info ['content']);
	    $res = json_decode($result,true);
	    if(is_null($res) || !isset($res['code']) || !$res['code']=='0'){
	        YDLib::testlog ($result);
	        YDLib::testLog( ErrnoStatus::STATUS_60003 );
	        return false;
	    }
	
	    $content_length = mb_strlen($info['content']);
	    $content_num = 1;
	    if ($content_length > 70) {
	        $content_num = ceil($content_length / 67);
	    }
	    
	    $updata = [ ];
	    $updata ['status'] = '2';
	    $updata ['old_num'] = $smsCount ['remain_num'];
	    $updata ['new_num'] = $smsCount ['remain_num'] - $content_num;
	    $updata ['result'] = $result;
	    $res = self::updateByID ( $updata, $last_id );
	    if (! $res) {
	        YDLib::testLog( ErrnoStatus::STATUS_60003 );
	        return false;
	    }
	
	    // 更新短信统计
	    $upsms = [ ];
	    $upsms ['remain_num'] = $smsCount ['remain_num'] - $content_num;
	    $upsms ['use_num'] = $smsCount ['use_num'] + $content_num;
	    $res = PushmsgModel::updateByID ( $upsms, $smsCount ['id'] );
	    if ($res === FALSE) {
	        YDLib::testLog( ErrnoStatus::STATUS_60003 );
	        return false;
	    }
	
	   return true;
	}
	
	
	
	
	/**
	 * 模板发送短信
	 *
	 * @param array $data
	 *        	短信参数
	 *        	$data['mobile'] 手机号
	 *        	$data['model_id'] 模板ID
	 *        	$data['params'] 模板参数
	 */
	public static function templateSendSms($data) {
		$info = [ ];
		$info ['sms_type'] = $data ['sms_type'] ? $data ['sms_type'] : '0';
		$info ['code'] = $data ['code'] ? $data ['code'] : '';
		$info ['valid'] = $data ['valid'] ? $data ['valid'] : '0';
		$info ['mobile'] = $data ['mobile'];
		$info ['type'] = '2';
		$info ['supplier_id'] = SUPPLIER_ID;
		$info ['status'] = '1';
		$info ['model_id'] = $data ['model_id'];
		$info ['note'] = '';
	
		// 通过手机号查询用户ID
		$user_info = UserModel::getInfoByMobile ( $info ['mobile'] );
		if ($user_info) {
			$info ['user_id'] = $user_info ['id'];
		} else {
			$info ['user_id'] = '0';
		}
	
		// 通过模板ID查询模板内容
		$model_info = SmsModelModel::getInfoByID ( $info ['model_id'] );
		$content = $model_info ['content'];
		if ($data ['params']) {
			foreach ( $data ['params'] as $key => $value ) {
				$content = str_replace ( '{' . ($key + 1) . '}', $value, $content );
			}
		}
		$supplier_info = SupplierModel::getInfoByID(SUPPLIER_ID);
		$info ['content'] = '【' . $supplier_info['sms_name'] . '】' . $content;
		$last_id = self::addData ( $info );
		if ($last_id == false) {
			YDLib::output ( ErrnoStatus::STATUS_60003 );
		}
	
		// 商家短信剩余数量
		$smsCount = PushmsgModel::getContent ();
		if ($smsCount ['remain_num'] <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_50022 );
		}
	
		// 发短信
		// $singleSender = new SmsSingleSender ();
		// $result = $singleSender->sendWithParam ( "86", $info ['mobile'], $model_info ['templId'], $data ['params'], SMS_NAME, "" );
		// $res = json_decode ( $result, true );
		// if ($res ['result'] != '0') {
		// YDLib::output ( ErrnoStatus::STATUS_60003 );
		// }
	
		$singleSender = new ChuanglanSmsApi ();
		$result = $singleSender->sendSMS ($info ['mobile'],$info ['content']);
		$res = json_decode($result,true);
		if(is_null($res) || !isset($res['code']) || !$res['code']=='0'){
			YDLib::testlog ($result);
			YDLib::output ( ErrnoStatus::STATUS_60003 );
		}
	
		$content_length = mb_strlen($info['content']);
		$content_num = 1;
		if ($content_length > 70) {
			$content_num = ceil($content_length / 67);
		}
	
		$updata = [ ];
		$updata ['status'] = '2';
		$updata ['old_num'] = $smsCount ['remain_num'];
		$updata ['new_num'] = $smsCount ['remain_num'] - $content_num;
		$updata ['result'] = $result;
		$res = self::updateByID ( $updata, $last_id );
		if (! $res) {
			YDLib::output ( ErrnoStatus::STATUS_60003 );
		}
	
		// 更新短信统计
		$upsms = [ ];
		$upsms ['remain_num'] = $smsCount ['remain_num'] - $content_num;
		$upsms ['use_num'] = $smsCount ['use_num'] + $content_num;
		$res = PushmsgModel::updateByID ( $upsms, $smsCount ['id'] );
		if ($res === FALSE) {
			YDLib::output ( ErrnoStatus::STATUS_60003 );
		}
	
		//YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
	}
	
	
	
	
	
}
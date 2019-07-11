<?php

/**
 * 商户等级model
 * @time 2018-05-11
 */
namespace Grade;

use Custom\YDLib;
use Common\CommonBase;
use Admin\AdminModel;
use User\UserSupplierModel;
use Grade\GradeGrowthLogModel;
use Grade\GradeRightsModel;
use Grade\GradeLogModel;
use Score\UserScoreModel;
use Coupan\CoupanModel;
use Supplier\SupplierModel;
use Services\Msg\MsgService;
use User\UserModel;

class GradeModel extends \BaseModel {
	protected static $_tableName = 'user_grade';
	
	/**
	 * 获取对应的list列表
	 * 
	 * @return array
	 */
	public static function getList() {
		$pdo = YDLib::getPDO ( 'db_r' );
        $fields = " a.*";
		$sql = 'SELECT
        		   [*]
        		FROM
				 	' . CommonBase::$_tablePrefix . self::$_tableName . ' a
 				WHERE 
 					a.is_del =  2
				AND  
					a.status = 2
				AND  
					a.supplier_id = ' . SUPPLIER_ID . '
				ORDER BY a.grade_id 	
				';
		
		$result = $pdo->YDGetAll ( str_replace ( '[*]', $fields, $sql ) );
		
		return $result;
	}
	
	/**
	 * 根据成长值查询等级
	 * 
	 * @return array
	 */
	public static function getGradeByGrowth($growth) {
		$pdo = YDLib::getPDO ( 'db_w' );
		$sql = 'SELECT
        		   *
        		FROM
				 	' . CommonBase::$_tablePrefix . self::$_tableName . '
 				WHERE 
 					is_del =  2
				AND  
					status = 2
				AND  
					supplier_id = ' . SUPPLIER_ID . '
				AND  
					growth <= ' . $growth . '
				ORDER BY grade_id DESC';
		return $pdo->YDGetRow ( $sql );
	}
	
	/**
	 * 根据表自增ID获取该条记录信息
	 *
	 * @param int $id
	 *        	表自增ID
	 */
	public static function getInfoByID($id, $supplier_id) {
		$where ['is_del'] = self::DELETE_SUCCESS;
		$where ['user_id'] = intval ( $id );
		$where ['supplier_id'] = intval ( $supplier_id );
		$pdo = self::_pdo ( 'db_r' );
		
		$users = $pdo->clear ()->select ( '*' )->from ( 'user_supplier' )->where ( $where )->getRow ();
		return $users;
	}
	
	/**
	 * 添加信息
	 *
	 * @param array $info        	
	 * @return mixed
	 */
	public static function addData($info) {
		$supplier_id = AdminModel::getAdminLoginInfo ( AdminModel::getAdminID () ) ['supplier_id'];
		
		$db = YDLib::getPDO ( 'db_w' );
		$info ['is_del'] = '2';
		$info ['supplier_id'] = $supplier_id;
		$info ['created_at'] = date ( "Y-m-d H:i:s" );
		$info ['updated_at'] = date ( "Y-m-d H:i:s" );
		$result = $db->insert ( self::$_tableName, $info );
		
		return $result;
	}
	
	/**
	 * 更新信息
	 *
	 * @param array $info        	
	 * @return mixed
	 */
	public static function updateByID($data, $id, $supplier_id) {
		$pdo = YDLib::getPDO ( 'db_w' );
		
		if ($supplier_id) {
			$inof = array ();
			$inof ['updated_at'] = date ( "Y-m-d H:i:s" );
			$update = $pdo->update ( self::$_tableName, $inof, array (
					'id' => intval ( $id ),
					'supplier_id' => $supplier_id,
					'is_del' => '2' 
			) );
			
			return $update;
		}
		return false;
	}
	
	/**
	 * 根据表自增 ID删除记录
	 *
	 * @param int $id
	 *        	表自增 ID
	 * @return boolean 删除是否成功
	 */
	public static function deleteByID($id, $supplier_id) {
		$pdo = self::_pdo ( 'db_w' );
		$supdate = $pdo->delete ( 'user_grade', array (
				'id' => intval ( $id ),
				'supplier_id' => $supplier_id,
				'is_del' => '2' 
		) );
		
		return $supdate;
	}
	public static function getDradeInfoByGradeId($id, $supplier_id) {
		$pdo = self::_pdo ( 'db_w' );
		$sql = 'SELECT
        		   *
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . '
		         WHERE
		             grade_id = ' . $id . '
		         AND
		             supplier_id = ' . $supplier_id . '
		         AND
		             status = 2
		         AND
		             is_del = 2
		           ';
		$user = $pdo->YDGetRow ( $sql );
		return $user;
	}
	
	/**
	 * 会员累积成长值
	 * 
	 * @param interger $user_id
	 *        	会员ID
	 * @param interger $money
	 *        	消费金额：正为加，负为减
	 * @param interger $source
	 *        	来源：1下单 2退款
	 * @param interger $source_id
	 *        	资源ID
	 * @return boolean
	 */
	public static function growthJudge($user_id, $money, $source, $source_id) {
		// 会员每消费1 元，交易完成后即获得 1 点成长值,取整处理
		$growth = intval ( $money );
		if ($growth == 0) {
			YDLib::testlog ( '无成长值' );
			return TRUE;
		}
		// 成长值变动日志
		$userInfo = UserSupplierModel::getInfoByUserID ( $user_id );
		$logInfo = [ ];
		$logInfo ['user_id'] = $user_id;
		$logInfo ['source'] = $source;
		$logInfo ['source_id'] = $source_id;
		$logInfo ['change_growth'] = $growth;
		$logInfo ['history_growth'] = $userInfo ['growth'];
		$logInfo ['current_gorwth'] = bcadd ( $userInfo ['growth'], $growth );
		$res = GradeGrowthLogModel::addData ( $logInfo );
		if (! $res) {
			YDLib::testlog ( "添加成长值变动日志失败" );
			return FALSE;
		}
		
		$data = [ ];
		$data ['growth'] = $growth;
		$res = UserSupplierModel::autoUpdateByUserID ( $data, $user_id );
		if (! $res) {
			YDLib::testlog ( "更新会员成长值失败" );
			return FALSE;
		}
		
		//积分变动发信息
		$user_info = UserModel::getAdminInfo($user_id);
		$suppplier_detail = SupplierModel::getInfoByID(SUPPLIER_ID);
		$weichat_url = sprintf(M_URL, $suppplier_detail['domain']).'mobile/user';
		$msgData = [
				'params' => [
						'0' => $logInfo ['change_growth'],
						'1' => $logInfo ['current_gorwth']
				],
				'weixin_params' => [
						'url' => $weichat_url,
						'pagepath' => [
								'appid' => MINI_APPID,
								'pagepath' => 'pages/index?domain=${'.$suppplier_detail['domain'].'}&share_url=${'.urlencode(SHOM_URL.$suppplier_detail['domain'].'mobile/user').'}'
						]
				]
		];
			
			
		/* 发送短信 */
		MsgService::fireMsg('12', $user_info ['mobile'], $user_info['id'],$msgData);
		
		
		
		// 检测是否升级(会员ID)
		return self::gradeJudge ( $user_id );
	}
	
	/**
	 * 会员等级升级
	 * 
	 * @param interger $user_id
	 *        	会员ID
	 * @return boolean
	 */
	public static function gradeJudge($user_id) {
		
		$userInfo = UserSupplierModel::getInfoByUserID ( $user_id );
		// 查询会员当前适应的等级
		$currentGradeInfo = self::getGradeByGrowth ( $userInfo ['growth'] );
		if (! $currentGradeInfo || ! is_array ( $currentGradeInfo ) || count ( $currentGradeInfo ) == 0) {
			YDLib::testlog ( '未设置升级规则，无需升级' );
			return TRUE;
		}
		
		if ($currentGradeInfo ['grade_id'] == $userInfo ['grade_id']) {
			YDLib::testlog ( '无需升降级' );
			return TRUE;
		}
		
		// 升级
		if ($currentGradeInfo ['grade_id'] > $userInfo ['grade_id']) {
			$up = bcsub ( $currentGradeInfo ['grade_id'], $userInfo ['grade_id'] );
			// YDLib::testlog('升级：'.$up);
			// 循环写入等级变动日志
			$gradeData = [ ];
			$gradeData ['user_id'] = $user_id;
			$gradeData ['is_gift'] = '2';
			$gradeData ['type'] = '1';
			$current_grade = $userInfo ['grade_id'];
			$history_grade = $userInfo ['grade_id'];
			for($i = 1; $i <= $up; $i ++) {
				$gradeData ['current_grade'] = $current_grade + 1;
				$gradeData ['history_grade'] = $history_grade;
				$res = GradeLogModel::addData ( $gradeData );
				if (! $res) {
					YDLib::testlog ( "等级日志更新失败" );
					return FALSE;
				}
				$current_grade += 1;
				$history_grade += 1;
				// 赠送升级礼包
				$res = self::gradeGift ( $user_id, $gradeData ['current_grade'] );
				if (! $res) {
					YDLib::testlog ( "升级礼包赠送失败" );
					return FALSE;
				}
			}
			// 更新等级
			$data = [ ];
			$data ['grade_id'] = $currentGradeInfo ['grade_id'];
			$res = UserSupplierModel::updateByID ( $data, $userInfo ['id'] );
			
			if (! $res) {
				YDLib::testlog ( "等级更新失败" );
				return FALSE;
			}
			
			
			$Info = UserSupplierModel::getInfoByUserID ( $user_id );
			//获取原等级信息
			$GradeInfo = self::getDradeInfoByGradeId ( $userInfo ['grade_id'],SUPPLIER_ID);
		
			
			//发送升级信息
			$user_info = UserModel::getAdminInfo($user_id);
			$suppplier_detail = SupplierModel::getInfoByID(SUPPLIER_ID);
			$weichat_url = sprintf(M_URL, $suppplier_detail['domain']).'mobile/user';
			$msgData = [
					'params' => [
							'0' => $suppplier_detail['shop_name'],
					],
					'weixin_params' => [
							'url' => $weichat_url,
							'pagepath' => [
									'appid' => MINI_APPID,
									'pagepath' => 'pages/index?domain=${'.$suppplier_detail['domain'].'}&share_url=${'.urlencode(SHOM_URL.$suppplier_detail['domain'].'mobile/user').'}'
							],
							'data' => [
									'first' => [
											'value' => '恭喜您在<'.$suppplier_detail['shop_name'].'>会员卡升级啦！'
									],
									'keyword1' => [
											'value' => $GradeInfo['identity']
									],
									'keyword2' => [
											'value' => $currentGradeInfo['identity']
									]
							]
					]
			];
			
			
			/* 发送短信 */
			MsgService::fireMsg('11', $user_info ['mobile'], $user_info['id'],$msgData);
			
			// 降级
		} else if ($currentGradeInfo ['grade_id'] < $userInfo ['grade_id']) {
			$dp = bcsub ( $userInfo ['grade_id'], $currentGradeInfo ['grade_id'] );
			// 循环写入等级变动日志
			// YDLib::testlog('降级：'.$dp);
			$gradeData = [ ];
			$gradeData ['user_id'] = $user_id;
			$gradeData ['is_gift'] = '1';
			$gradeData ['type'] = '2';
			$current_grade = $userInfo ['grade_id'];
			$history_grade = $userInfo ['grade_id'];
			for($i = 1; $i <= $dp; $i ++) {
				$gradeData ['current_grade'] = $current_grade - 1;
				$gradeData ['history_grade'] = $history_grade;
				$res = GradeLogModel::addData ( $gradeData );
				if (! $res) {
					YDLib::testlog ( "等级日志更新失败" );
					return FALSE;
				}
				$current_grade -= 1;
				$history_grade -= 1;
			}
			
			// 更新等级
			$data = [ ];
			$data ['grade_id'] = $currentGradeInfo ['grade_id'];
			$res = UserSupplierModel::updateByID ( $data, $userInfo ['id'] );
			
			if (! $res) {
				YDLib::testlog ( "等级更新失败" );
				return FALSE;
			}
		}
		
		return TRUE;
	}
	
	/**
	 * 会员升级礼包发放
	 * 
	 * @param interger $user_id
	 *        	会员ID
	 * @param interger $grade_id
	 *        	等级ID
	 * @return boolean 是否成功
	 */
	public static function gradeGift($user_id, $grade_id) {
		// 会员升级礼包
		$user_rights = GradeRightsModel::getInfoByGradeID ( $grade_id );
		// YDLib::testlog($user_rights);
		if (! $user_rights) {
			YDLib::testlog ( '没有礼包哦' );
			return TRUE;
		}
		
		// 更新用户积分
		if ($user_rights ['is_integral'] == '1' && $user_rights ['integral'] > 0) {
			$res = UserScoreModel::giveScore ( $user_id, 3, $grade_id, $user_rights ['integral'] );
			if (! $res ['status']) {
				YDLib::testlog ( $res );
				return FALSE;
			}
		}
		
		// 卡券发放
		if ($user_rights ['is_coupons'] == '1' && ! empty ( $user_rights ['coupons'] )) {
			$coupons = json_decode ( $user_rights ['coupons'], TRUE );
			foreach ( $coupons as $key => $value ) {
				$detail = CoupanModel::getInfoByID ( $value ['id'] );
				// 最多能发放几张
				$num = $value ['num'];
				if ($detail ['remain_num'] < $num) {
					$num = $detail ['remain_num'];
				}
				if ($num > 0) { // 没有券可发了
					$coupandata = [ ];
					$coupandata ['supplier_id'] = SUPPLIER_ID;
					$coupandata ['user_id'] = $user_id;
					$coupandata ['coupan_id'] = $value ['id'];
					$coupandata ['status'] = 1;
					$coupandata ['give_at'] = date ( "Y-m-d H:i:s" );
					
					// 插入多条优惠券
					$couponSql = "insert into " . self::$_tablePrefix . "user_coupan 
					(supplier_id,user_id,coupan_id,status,give_at,is_del,created_at,updated_at) values ";
					for($i = 0; $i < $num; $i ++) {
						$couponSql .= "(" . SUPPLIER_ID . "," . $user_id . "," . $value ['id'] . ",1,NOW(),2,NOW(),NOW()),";
					}
					$couponSql = rtrim ( $couponSql, "," );
					// YDLib::testlog($couponSql);
					$pdo = self::_pdo ( 'db_w' );
					$res = $pdo->YDExecute ( $couponSql );
					if (! $res) {
						YDLib::testlog ( '优惠券发放失败' );
						return FALSE;
					}
					$upcoupandata = [ ];
					$upcoupandata ['give_num'] = $num;
					$upcoupandata ['remain_num'] = - $num;
					$res = CoupanModel::autoUpdateByID ( $upcoupandata, $value ['id'] );
					if (! $res) {
						YDLib::testlog ( '更新优惠券数量失败' );
						return FALSE;
					}
				}
			}
		}
		
		return TRUE;
	}
}
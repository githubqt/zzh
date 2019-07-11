<?php

/**
 * 角色model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-06-28
 */
namespace Score;

use Custom\YDLib;
use Common\CommonBase;
use Admin\AdminModel;
use Order\OrderChildModel;
use Score\UserStorageModel;
use Score\ScoreRuleModel;
use Order\OrderChildProductModel;
use Supplier\SupplierModel;
use User\UserSupplierModel;
use Grade\GradeModel;
use ErrnoStatus;
use Grade\GradeRightsModel;

class UserScoreModel extends \Common\CommonBase {
	protected static $_tableName = 'user_score';
	
	/* 获取列表 */
	public static function getList($attribute = array(), $page = 0, $rows = 10) {
		$limit = ($page) * $rows;
		if (! empty ( $attribute ['info'] ) && is_array ( $attribute ['info'] ) && count ( $attribute ['info'] ) > 0) {
			extract ( $attribute ['info'] );
		}
		
		$pdo = YDLib::getPDO ( 'db_r' );
		$fileds = " a.* ";
		$sql = 'SELECT 
        		   [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a 
		        WHERE
        		    a.is_del = 2
		        AND 
	                 a.type = 2
		        AND 
	                 a.supplier_id=' . SUPPLIER_ID;
		
		if (isset ( $name ) && ! empty ( $name )) {
			$sql .= " AND a.name like '%" . $name . "%' ";
		}
		
		if (isset ( $id ) && ! empty ( $id )) {
			$sql .= " AND a.id like '%" . $id . "%' ";
		}
		
		if (isset ( $note ) && ! empty ( $note )) {
			$sql .= " AND a.note like '%" . $note . "%' ";
		}
		if (isset ( $status ) && ! empty ( $status )) {
			$sql .= " AND a.status = " . $status . " ";
		}
		
		if (isset ( $start_time ) && isset ( $end_time ) && ! empty ( $start_time ) && ! empty ( $end_time )) {
			$sql .= " AND a.created_at >= '" . $start_time . " 00:00:00' ";
			$sql .= " AND a.created_at <= '" . $end_time . " 23:59:59' ";
		}
		$result ['total'] = $pdo->YDGetOne ( str_replace ( "[*]", "count(*) as num", $sql ) );
		$sql .= " limit {$limit},{$rows}";
		$result ['list'] = $pdo->YDGetAll ( str_replace ( "[*]", $fileds, $sql ) );
		
		if ($result) {
			return $result;
		} else {
			return false;
		}
	}
	
	/**
	 * 添加信息
	 * 
	 * @param array $info        	
	 * @return mixed
	 */
	public static function addRole($info) {
		$db = YDLib::getPDO ( 'db_w' );
		$info ['supplier_id'] = SUPPLIER_ID;
		$info ['is_del'] = '2';
		$info ['created_at'] = date ( "Y-m-d H:i:s" );
		$info ['updated_at'] = date ( "Y-m-d H:i:s" );
		$result = $db->insert ( self::$_tableName, $info );
		
		return $result;
	}
	
	/**
	 * 根据id获取
	 * 
	 * @param array $id        	
	 * @return array
	 */
	public static function getInfoById($id) {
		$pdo = YDLib::getPDO ( 'db_r' );
		$ret = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( [ 
				'id' => $id,
				'is_del' => '2' 
		] )->getRow ();
		
		return $ret ? $ret : [ ];
	}
	
	/**
	 * 根据id获取
	 * 
	 * @param array $id        	
	 * @return array
	 */
	public static function getAll() {
		$pdo = YDLib::getPDO ( 'db_r' );
		
		$info ['supplier_id'] = SUPPLIER_ID;
		$info ['type'] = '2';
		$info ['status'] = '2';
		$info ['is_del'] = '2';
		$ret = $pdo->clear ()->select ( '*' )->from ( self::$_tableName )->where ( $info )->getAll ();
		
		return $ret ? $ret : [ ];
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
	
	/* 获取今日获取的积分数 */
	public static function getTodayNum($user_id, $type = '') {
		$pdo = YDLib::getPDO ( 'db_r' );
		$fileds = " sum(a.give_score) as sum_give_score ";
		$sql = 'SELECT
        		   [*]
        		FROM
		             ' . CommonBase::$_tablePrefix . self::$_tableName . ' a
		        WHERE
        		    a.is_del = 2
		        AND
	                 a.user_id = ' . $user_id;
		
		if ($type) {
			$sql .= ' AND a.give_type = ' . $type;
		}
		
		$sql .= ' AND
	                 a.supplier_id=' . SUPPLIER_ID;
		
		$sql .= " AND a.created_at >= '" . date ( 'Y-m-d' ) . " 00:00:00' ";
		$sql .= " AND a.created_at <= '" . date ( 'Y-m-d' ) . " 23:59:59' ";
		
		$sum_give_score = $pdo->YDGetOne ( str_replace ( "[*]", $fileds, $sql ) );
		
		if ($sum_give_score) {
			return $sum_give_score;
		} else {
			return '0';
		}
	}
	
	/**
	 * 赠送积分
	 * hxg 2018-07-03
	 * user_id 用户id
	 * resource_id 赠送来源id
	 * type 来源类型 1：订单 2：活动 3：升级送积分
	 * scoreNum 活动时传此参数 积分数量
	 *
	 * @return $res $res['status'] true/false
	 *         $res['code'] code
	 */
	public static function giveScore($user_id, $type, $resource_id, $scoreNum = '0') {
		$res = [ ];
		$res ['status'] = TRUE;
		$res ['code'] = '0';
		
		// 订单
		if ($type == '1') {
			// //获取本商户所有积分规则
			// $score_rule = ScoreRuleModel::getAll();
			// if ($score_rule) {
			// //查询订单信息计算赠送积分数量
			// $order = OrderChildModel::getInfoByID($resource_id);
			// if (!$order || $order['is_give_score'] == '2') {
			// YDLib::output(ErrnoStatus::STATUS_60579);
			// }
			// //判断该用户的等级是否有福利
			// $user = UserSupplierModel::getAdminInfo($user_id);
			// $feedback = '1';
			// $user_rights = GradeRightsModel::getInfoByGradeID($user['grade_id']);
			// if ($user_rights && $user_rights['is_feedback'] == '1') {//如果有积分回馈倍率（倍）
			// $feedback = $user_rights['feedback'];
			// }
			// //循环所有可用的积分规则
			// $order_id[] = $resource_id;
			// foreach ($score_rule as $key=>$value) {
			// $value['score_num'] = floor(bcmul($value['score_num'], $feedback,2));//积分X倍率向下取整
			// if ($value['receive_type'] == '1') {//每成功交易几笔
			// if ($value['receive_num'] >= '1') {
			// //查询该用户是否满足笔数
			// $orderList = OrderChildModel::getInfoByUserIdAndStatus($user_id,$resource_id);
			// $newOrder[] = $order;
			// $orderList = array_merge($newOrder, $orderList);
			// if ($value['receive_num'] <= count($orderList)) {//如果条数过多去除多余的条数
			// $orderList = array_slice($orderList, 0 ,$value['receive_num']);
			// }
			// $order_ids = [];
			// foreach ($orderList as $k=>$val) {
			// $order_ids[$k] = $val['id'];
			// }
			// $order_id = $order_ids;
			// self::addScoreInfo($user_id, $type, $value, $Publicb,$order_ids);
			// }
			// } else {//每购买多少金额
			// //订单钱数是否足够参加活动
			// if ($order['child_product_actual_amount'] >= $value['receive_num']) {
			// if ($value['product_type'] == '1') {//部分商品
			// //取出该规则的商品
			// $rule_product = ScoreRuleProductModel::getinfoByRuleID($value['id']);
			// //取出本订单的商品
			// $order_product = OrderChildProductModel::getProductByChildID($order['id']);
			// $is_yes = false;
			// foreach ($rule_product as $k=>$val) {
			// foreach ($order_product as $kk=>$v) {
			// if ($v['product_id'] == $val['product_id']) {
			// $is_yes = true;
			// }
			// }
			// }
			// if ($is_yes == true) {
			// self::addScoreInfo($user_id, $type, $value, $Publicb,$order['id']);
			// }
			// } else {
			// self::addScoreInfo($user_id, $type, $value, $Publicb,$order['id']);
			// }
			// }
			// }
			// }
			// $db = YDLib::getPDO('db_w');
			// foreach ($order_id as $k=>$val) {
			// //更新为以发放状态
			// $data['is_give_score'] = '2';
			// $data['updated_at'] = date("Y-m-d H:i:s");
			// $where['id'] = intval($val);
			// $where['supplier_id'] = SUPPLIER_ID;
			// $up = $db->update('order_child', $data, $where);
			// if ($up == false) {
			// YDLib::output(ErrnoStatus::STATUS_60579);
			// }
			// }
			// }
		} else {
			$res = self::addScoreInfo ( $user_id, $type, [ 
					'score_num' => $scoreNum 
			], $resource_id );
		}
		return $res;
	}
	
	/**
	 * 赠送积分数据库部分
	 * hxg 2018-07-03
	 * user_id 用户id
	 * type 来源类型 1：订单 2：活动 3：升级送积分
	 * resource_id 赠送来源id
	 * Publicb class引入
	 */
	public static function addScoreInfo($user_id, $type, $rule, $resource_id) {
		$res = [ ];
		$res ['status'] = TRUE;
		$res ['code'] = '0';
		
		$Publicb = new \Publicb ();
		
		$db = YDLib::getPDO ( 'db_w' );
		$supplier = SupplierModel::getInfoByID ( SUPPLIER_ID );
		
		if (is_array ( $resource_id ) && count ( $resource_id ) > 0) {
			$resource_id = implode ( ',', $resource_id );
		}
		// 获取用户积分信息
		$user_score_info = UserStorageModel::getInfoById ( $user_id , $db);
		if ($user_score_info == false) {
			UserStorageModel::addData ( [ 
					'user_id' => $user_id 
			] );
			$user_score_info = UserStorageModel::getInfoById ( $user_id , $db);
		}
		
		// 是否开启积分上限
		$is_surplus = false;
		if ($supplier ['score_top'] == '2') {
			// 判断用户今日获取的积分是否已达上限
			$today_num = UserScoreModel::getTodayNum ( $user_id );
			if ($today_num >= $supplier ['score_top_num']) {
				if ($type == '2') {
					// 摇一摇今日已达上限提示明日领取
					$res ['status'] = FALSE;
					$res ['code'] = ErrnoStatus::STATUS_60577;
					return $res;
				}
				
				// 今日消费获取达到上限（记录两条库存日志
				// 积分日志表
				$user_score_log = [ ];
				$user_score_log ['user_id'] = $user_id;
				$user_score_log ['score'] = $rule ['score_num'];
				$user_score_log ['before_score'] = $user_score_info ['give_score'];
				$user_score_log ['after_score'] = bcadd ( $user_score_info ['give_score'], $rule ['score_num'] );
				$user_score_log ['action_id'] = $resource_id;
				if ($type == '1') {
					$user_score_log ['action_type'] = 'order';
					$user_score_log ['note'] = '下单赠送';
				} else if ($type == '2') {
					$user_score_log ['action_type'] = 'active';
					$user_score_log ['note'] = '活动积分领取';
				} else if ($type == '3') {
					$user_score_log ['action_type'] = 'level';
					$user_score_log ['note'] = '会员升级赠送';
				}
				$user_score_log ['ip'] = $Publicb::GetIP ();
				$user_score_log ['supplier_id'] = SUPPLIER_ID;
				$user_score_log ['is_del'] = '2';
				$user_score_log ['created_at'] = date ( "Y-m-d H:i:s" );
				$user_score_log ['updated_at'] = date ( "Y-m-d H:i:s" );
				$user_score_log_id = $db->insert ( 'user_score_log', $user_score_log );
				if ($user_score_log_id == false) {
					$res ['status'] = FALSE;
					$res ['code'] = ErrnoStatus::STATUS_60580;
					return $res;
				}
				
				$user_score_log ['score'] = '-' . bcsub ( $user_score_log ['after_score'], $supplier ['score_top_num'] );
				$user_score_log ['before_score'] = $user_score_log ['after_score'];
				$user_score_log ['after_score'] = bcadd ( $user_score_log ['after_score'], $user_score_log ['score'] );
				$user_score_log ['note'] = '本日获取达到限额扣除';
				$user_score_log ['updated_at'] = date ( "Y-m-d H:i:s" );
				$user_score_log_id = $db->insert ( 'user_score_log', $user_score_log );
				if ($user_score_log_id == false) {
					$res ['status'] = FALSE;
					$res ['code'] = ErrnoStatus::STATUS_60580;
					return $res;
				}
			} else { // 未超过顶峰
			         // 计算剩余积分
				$surplus_num = bcsub ( $supplier ['score_top_num'], $today_num );
				
				if ($type == '1' || $type == '3') {
					if ($surplus_num <= $rule ['score_num']) {
						// 原积分数
						$score_num = $rule ['score_num'];
						// 赠送积分=剩余可获取积分
						$rule ['score_num'] = $surplus_num;
						$is_surplus = true;
					}
				} else if ($type == '2') {
					// 今日剩余可领取 < 本次领取
					if ($surplus_num < $rule ['score_num']) {
						// 摇一摇今日不可领取全部提示明日领取
						$res ['status'] = FALSE;
						$res ['code'] = ErrnoStatus::STATUS_60578;
						return $res;
					}
				}
			}
		}
		
		// 赠送积分表
		$user_score = [ ];
		$user_score ['user_id'] = $user_id;
		$user_score ['give_score'] = $rule ['score_num'];
		$user_score ['give_type'] = $type;
		$user_score ['resource_id'] = $resource_id;
		$user_score ['overdue_time'] = date ( 'Y-' . $supplier ['score_rule_time'], strtotime ( "+" . $supplier ['score_rule_year'] . " year" ) );
		$user_score ['supplier_id'] = SUPPLIER_ID;
		$user_score ['is_del'] = '2';
		$user_score ['created_at'] = date ( "Y-m-d H:i:s" );
		$user_score ['updated_at'] = date ( "Y-m-d H:i:s" );
		$user_score_id = $db->insert ( self::$_tableName, $user_score );
		if ($user_score_id == false) {
			$res ['status'] = FALSE;
			$res ['code'] = ErrnoStatus::STATUS_60581;
			return $res;
		}
		
		if ($is_surplus == false) {
			// 积分日志表
			$user_score_log = [ ];
			$user_score_log ['user_id'] = $user_id;
			$user_score_log ['score'] = $rule ['score_num'];
			$user_score_log ['before_score'] = $user_score_info ['give_score'];
			$user_score_log ['after_score'] = bcadd ( $user_score_info ['give_score'], $rule ['score_num'] );
			$user_score_log ['action_id'] = $resource_id;
			if ($type == '1') {
				$user_score_log ['action_type'] = 'order';
				$user_score_log ['note'] = '下单赠送';
			} else if ($type == '2') {
				$user_score_log ['action_type'] = 'active';
				$user_score_log ['note'] = '活动积分领取';
			} else if ($type == '3') {
				$user_score_log ['action_type'] = 'level';
				$user_score_log ['note'] = '会员升级赠送';
			}
			$user_score_log ['ip'] = $Publicb::GetIP ();
			$user_score_log ['supplier_id'] = SUPPLIER_ID;
			$user_score_log ['is_del'] = '2';
			$user_score_log ['created_at'] = date ( "Y-m-d H:i:s" );
			$user_score_log ['updated_at'] = date ( "Y-m-d H:i:s" );
			$user_score_log_id = $db->insert ( 'user_score_log', $user_score_log );
			if ($user_score_log_id == false) {
				$res ['status'] = FALSE;
				$res ['code'] = ErrnoStatus::STATUS_60580;
				return $res;
			}
		} else { // 积分达到限额时的日志
		         // 积分日志表
			$user_score_log = [ ];
			$user_score_log ['user_id'] = $user_id;
			$user_score_log ['score'] = $score_num;
			$user_score_log ['before_score'] = $user_score_info ['give_score'];
			$user_score_log ['after_score'] = bcadd ( $user_score_info ['give_score'], $score_num );
			$user_score_log ['action_id'] = $resource_id;
			if ($type == '1') {
				$user_score_log ['action_type'] = 'order';
				$user_score_log ['note'] = '下单赠送';
			} else if ($type == '2') {
				$user_score_log ['action_type'] = 'active';
				$user_score_log ['note'] = '活动积分领取';
			} else if ($type == '3') {
				$user_score_log ['action_type'] = 'level';
				$user_score_log ['note'] = '会员升级赠送';
			}
			$user_score_log ['ip'] = $Publicb::GetIP ();
			$user_score_log ['supplier_id'] = SUPPLIER_ID;
			$user_score_log ['is_del'] = '2';
			$user_score_log ['created_at'] = date ( "Y-m-d H:i:s" );
			$user_score_log ['updated_at'] = date ( "Y-m-d H:i:s" );
			$user_score_log_id = $db->insert ( 'user_score_log', $user_score_log );
			if ($user_score_log_id == false) {
				$res ['status'] = FALSE;
				$res ['code'] = ErrnoStatus::STATUS_60580;
				return $res;
			}
			// 如果剩余积分不足记录一条扣除积分的日志
			$user_score_log ['score'] = '-' . bcsub ( $user_score_log ['after_score'], $supplier ['score_top_num'] );
			$user_score_log ['before_score'] = $user_score_log ['after_score'];
			$user_score_log ['after_score'] = bcadd ( $user_score_log ['after_score'], $user_score_log ['score'] );
			$user_score_log ['note'] = '本日获取达到限额扣除';
			$user_score_log ['updated_at'] = date ( "Y-m-d H:i:s" );
			$user_score_log_id = $db->insert ( 'user_score_log', $user_score_log );
			if ($user_score_log_id == false) {
				$res ['status'] = FALSE;
				$res ['code'] = ErrnoStatus::STATUS_60580;
				return $res;
			}
		}
		
		// 用户资产表
		$user_storage = [ ];
		$user_storage ['all_score'] = bcadd ( $user_score_info ['all_score'], $rule ['score_num'] );
		$user_storage ['give_score'] = bcadd ( $user_score_info ['give_score'], $rule ['score_num'] );
		$user_storage ['updated_at'] = date ( "Y-m-d H:i:s" );
		
		$user_storage_where ['user_id'] = $user_id;
		$user_storage_where ['supplier_id'] = SUPPLIER_ID;
		$user_storage_where ['is_del'] = '2';
		$user_storage_id = $db->update ( 'user_storage', $user_storage, $user_storage_where );
		if ($user_storage_id == false) {
			$res ['status'] = FALSE;
			$res ['code'] = ErrnoStatus::STATUS_60582;
			return $res;
		}
		
		if ($type == '1') {
			// 积分规则表
			$score_rule_add = [ ];
			$score_rule_add ['have_score_num'] = bcadd ( $rule ['have_score_num'], $rule ['score_num'] );
			
			$score_rule_add_where ['id'] = $rule ['id'];
			$score_rule_add_where ['supplier_id'] = SUPPLIER_ID;
			$score_rule_add_where ['is_del'] = '2';
			$score_rule_add_id = $db->update ( 'score_rule', $score_rule_add, $score_rule_add_where );
			if ($score_rule_add_id == false) {
				$res ['status'] = FALSE;
				$res ['code'] = ErrnoStatus::STATUS_60583;
				return $res;
			}
		}
		
		// 商户表
		$supplier_ud = [ ];
		$supplier_ud ['all_score_num'] = bcadd ( $supplier ['all_score_num'], $rule ['score_num'] );
		
		$supplier_ud_where ['id'] = SUPPLIER_ID;
		$supplier_ud_where ['is_del'] = '2';
		$supplier_ud_id = $db->update ( 'supplier', $supplier_ud, $supplier_ud_where );
		if ($supplier_ud_id == false) {
			$res ['status'] = FALSE;
			$res ['code'] = ErrnoStatus::STATUS_60584;
			return $res;
		}
		
		return $res;
	}
}
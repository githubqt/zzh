<?php
/**
 * 活动Controllers
 * @version v0.01
 * @author huangxianguo
 * @time 2018-06-21
 */
use Custom\YDLib;
use Seckill\SeckillModel;
use Seckill\SeckillPrizeUserModel;
use Common\CommonBase;
use User\UserModel;
use User\UserSupplierBindModel;
use Score\UserScoreModel;
class ActivityController extends BaseController {
	// 摇一摇类型
	const TYPE_SHAKE = 2;
	public $_userLogin = 'FGRTYUSDSC';
	
	/**
	 * 摇一摇获取活动基础信息接口
	 *
	 * <pre>
	 * POST参数
	 * aid : 活动id，必填
	 * user_id : 用户id，必填
	 * bind_id : 绑定id，必填
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Activity/shakeInfo
	 * 测试： http://testapi.qudiandang.com/v1/Activity/shakeInfo
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' => {
	 *         'note' => '活动信息',
	 *         'count' => '当前剩余次数'
	 *         'total' => '历史参与总次数'
	 *         'today' => '今天参与总次数'
	 *         }
	 *         ]
	 *        
	 *         失败：
	 *         [
	 *         'errno' => -1,
	 *         'errormsg' => '系统繁忙'
	 *         'result' => {}
	 *         ]
	 *         </pre>
	 */
	public function shakeInfoAction() {
		$aid = $this->_request->get ( 'aid' );
		$user_id = $this->user_id;
		$bind_id = $this->_request->get ( 'bind_id' );
		
		if (empty ( $aid )) {
			YDLib::output ( ErrnoStatus::STATUS_40103 );
		}
		
		if (empty ( $user_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		
		if (empty ( $bind_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40105 );
		}
		
		$bindInfo = UserSupplierBindModel::getInfoByID ( $bind_id );
		if (! $bindInfo || $bindInfo ['user_id'] != $user_id) {
			YDLib::output ( ErrnoStatus::STATUS_10004 );
		}
		
		$shakeInfo = SeckillModel::getInfoByID ( $aid );
		if (! $shakeInfo) {
			YDLib::output ( ErrnoStatus::STATUS_60570 );
		}
		
		if ($shakeInfo ['type'] != self::TYPE_SHAKE) {
			YDLib::output ( ErrnoStatus::STATUS_60570 );
		}
		
		$data = [ ];
		$data ['note'] = str_replace ( array (
				"\r\n",
				"\r",
				"\n" 
		), "<br>", $shakeInfo ['note'] );
		
		// 获取用户摇一摇次数
		$userCount = SeckillPrizeUserModel::getUserCount ( $aid, $user_id );
		$data ['total'] = $userCount ['total'];
		$data ['today'] = $userCount ['today'];
		if ($shakeInfo ['spey'] == '1') {
			$data ['count'] = $shakeInfo ['number'] - $userCount ['today'];
		} else if ($shakeInfo ['spey'] == '2') {
			$data ['count'] = $shakeInfo ['number'] - $userCount ['total'];
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data );
	}
	
	/**
	 * 摇一摇获取用户中奖记录接口
	 *
	 * <pre>
	 * POST参数
	 * aid : 活动id，必填
	 * user_id : 用户id，必填
	 * bind_id : 三方id，必填
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Activity/shakeList
	 * 测试： http://testapi.qudiandang.com/v1/Activity/shakeList
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' => {
	 *         "total": "0",
	 *         "list": []
	 *         }
	 *         ]
	 *        
	 *         失败：
	 *         [
	 *         'errno' => -1,
	 *         'errormsg' => '系统繁忙'
	 *         'result' => {}
	 *         ]
	 *         </pre>
	 */
	public function shakeListAction() {
		$search = [ ];
		$search ['aid'] = $this->_request->get ( 'aid' );
		$page = $this->_request->getPost ( 'page' );
		$page = ! empty ( $page ) ? intval ( $page ) : 1;
		$page = $page > 0 ? $page : 1;
		
		$rows = $this->_request->getPost ( 'rows' );
		$rows = ! empty ( $rows ) ? intval ( $rows ) : 10;
		$rows = $rows > 0 ? $rows : 10;
		
		$search ['user_id'] = $this->user_id;
		
		if (empty ( $search ['aid'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40103 );
		}
		
		if (! empty ( $search ['user_id'] )) {
			$bind_id = $this->_request->get ( 'bind_id' );
			if (empty ( $bind_id )) {
				YDLib::output ( ErrnoStatus::STATUS_40105 );
			}
			
			$bindInfo = UserSupplierBindModel::getInfoByID ( $bind_id );
			if (! $bindInfo || $bindInfo ['user_id'] != $search ['user_id']) {
				YDLib::output ( ErrnoStatus::STATUS_10004 );
			}
			$search ['bind_id'] = $bind_id;
		}
		
		$shakeInfo = SeckillModel::getInfoByID ( $search ['aid'] );
		if (! $shakeInfo) {
			YDLib::output ( ErrnoStatus::STATUS_60570 );
		}
		
		if ($shakeInfo ['type'] != self::TYPE_SHAKE) {
			YDLib::output ( ErrnoStatus::STATUS_60570 );
		}
		
		$data = SeckillPrizeUserModel::getList ( $search, $page, $rows );
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data );
	}
	
	/**
	 * 摇一摇摇奖接口
	 *
	 * <pre>
	 * POST参数
	 * aid : 活动id，必填
	 * user_id : 用户id，必填
	 * bind_id : 绑定id，必填
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Activity/shake
	 * 测试： http://testapi.qudiandang.com/v1/Activity/shake
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' => {
	 *         "result": {
	 *         "tips": "恭喜您！~",//提示信息
	 *         "is_prize": "2",//是否中奖1未中奖2中奖
	 *         "level": "4",//奖品等级1一等奖2二等奖3三等奖4普通奖
	 *         "note": "10积分"//奖品内容
	 *         "count": "99"//剩余次数
	 *         }
	 *         ]
	 *        
	 *         失败：
	 *         [
	 *         'errno' => -1,
	 *         'errormsg' => '系统繁忙'
	 *         'result' => {}
	 *         ]
	 *         </pre>
	 */
	public function shakeAction() {
		$aid = $this->_request->get ( 'aid' );
		$user_id = $this->user_id;
		$bind_id = $this->_request->get ( 'bind_id' );
		
		if (empty ( $aid )) {
			YDLib::output ( ErrnoStatus::STATUS_40103 );
		}
		
		if (empty ( $user_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		
		if (empty ( $bind_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40105 );
		}
		
		$bindInfo = UserSupplierBindModel::getInfoByID ( $bind_id );
		if (! $bindInfo || $bindInfo ['user_id'] != $user_id) {
			YDLib::output ( ErrnoStatus::STATUS_10004 );
		}
		
		$shakeInfo = SeckillModel::getInfoByID ( $aid );
		if (! $shakeInfo) {
			YDLib::output ( ErrnoStatus::STATUS_60570 );
		}
		
		if ($shakeInfo ['type'] != self::TYPE_SHAKE) {
			YDLib::output ( ErrnoStatus::STATUS_60570 );
		}
		
		if (strtotime ( $shakeInfo ['starttime'] ) >= time () && strtotime ( $shakeInfo ['endtime'] ) >= time ()) {
			YDLib::output ( ErrnoStatus::STATUS_60562 ); // 未开始
		} else if (strtotime ( $shakeInfo ['starttime'] ) <= time () && strtotime ( $shakeInfo ['endtime'] ) <= time ()) {
			YDLib::output ( ErrnoStatus::STATUS_60561 ); // 已结束
		}
		
		// 判断有没有摇奖机会
		$count = 0;
		$userCount = SeckillPrizeUserModel::getUserCount ( $aid, $user_id );
		if ($shakeInfo ['spey'] == '1') {
			$count = $shakeInfo ['number'] - $userCount ['today'];
		} else if ($shakeInfo ['spey'] == '2') {
			$count = $shakeInfo ['number'] - $userCount ['total'];
		}
		
		if ($count <= 0) {
			YDLib::output ( ErrnoStatus::STATUS_60572 ); // 没有机会了
		}
		
		// 随机取摇奖机会
		SeckillModel::shake ( $aid, $user_id, $shakeInfo ['pass_note'], $count, $bind_id );
	}
	
	/**
	 * 摇一摇领奖接口
	 *
	 * <pre>
	 * POST参数
	 * aid : 奖品id，必填 【中奖表id】
	 * user_id : 用户id，必填
	 * bind_id : 绑定id，必填
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Activity/prize
	 * 测试： http://testapi.qudiandang.com/v1/Activity/prize
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' => {
	 *         "total": "0",
	 *         "list": []
	 *         }
	 *         ]
	 *        
	 *         失败：
	 *         [
	 *         'errno' => -1,
	 *         'errormsg' => '系统繁忙'
	 *         'result' => {}
	 *         ]
	 *         </pre>
	 */
	public function prizeAction() {
		$aid = $this->_request->get ( 'aid' );
		$user_id = $this->user_id;
		$bind_id = $this->_request->get ( 'bind_id' );
		
		if (empty ( $aid )) {
			YDLib::output ( ErrnoStatus::STATUS_40104 );
		}
		
		if (empty ( $user_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		
		if (empty ( $bind_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40105 );
		}
		
		$bindInfo = UserSupplierBindModel::getInfoByID ( $bind_id );
		if (! $bindInfo || $bindInfo ['user_id'] != $user_id) {
			YDLib::output ( ErrnoStatus::STATUS_10004 );
		}
		
		// 检测手机号是否补全
		$userInfo = UserModel::getAdminInfo ( $user_id );
		if (! $userInfo || empty ( $userInfo ['mobile'] )) {
			YDLib::output ( ErrnoStatus::STATUS_50021 );
		}
		
		$prizeInfo = SeckillPrizeUserModel::getInfoByID ( $aid );
		if (! $prizeInfo || $prizeInfo ['supplier_id'] != SUPPLIER_ID || $prizeInfo ['user_id'] != $user_id || $prizeInfo ['is_prize'] != '2') {
			YDLib::output ( ErrnoStatus::STATUS_50020 );
		}
		
		if ($prizeInfo ['status'] != '1') {
			YDLib::output ( ErrnoStatus::STATUS_60575 );
		}
		
		SeckillPrizeUserModel::getPrize ( $user_id, $prizeInfo );
	}
}

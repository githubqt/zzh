<?php
/**
 * 用户Controllers
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-16
 */
use Custom\YDLib;
use User\UserModel;
use Sms\SmsModel;
use Common\CommonBase;
use User\UserSupplierModel;
use User\UserProposalModel;
use User\UserContactUsModel;
use User\UserConcernModel;
use Coupan\UserCoupanModel;
use User\UserSupplierThridModel;
use Common\XDeode;
use Product\ProductModel;
use Product\ProductChannelModel;
use Common\Crypt3Des;
use Order\OrderChildModel;
use Supplier\SupplierModel;
use Appraisal\AppraisalModel;
class UserController extends BaseController {
	
	/**
	 * 用户注册接口
	 *
	 * <pre>
	 * POST参数
	 * mobile : 注册手机，必填，页面先验证格式是否正确
	 * password : 密码，必填
	 * repassword : 确认密码，必填 验证是否一致
	 * msg_code ：页面验证码
	 * phone_code : 手机验证码
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/User/reg
	 * 测试： http://testapi.qudiandang.com/v1/User/reg
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' => {}
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
	public function regAction() {
		$data = [ ];
		$type = $this->_request->get ( 'type' );
		$data ['mobile'] = $this->_request->get ( 'mobile' );
		$data ['password'] = $this->_request->get ( 'password' );
		$repassword = $this->_request->get ( 'repassword' );
		$msg_code = $this->_request->get ( 'msg_code' );
		$phone_code = $this->_request->get ( 'phone_code' );
		$data ['invitation_id'] = $this->_request->get ( 'invitation_id' );
		if ($data ['invitation_id']) {
			$data ['invitation_id'] = XDeode::decode ( $data ['invitation_id'] );
		}
		
		if (empty ( $data ['mobile'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40001 );
		}
		if (empty ( $data ['password'] ) || ! YDLib::validData ( $data ['password'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40004 );
		}
		if (empty ( $repassword ) || ! YDLib::validData ( $repassword )) {
			YDLib::output ( ErrnoStatus::STATUS_40007 );
		}
		if ($data ['password'] != $repassword) {
			YDLib::output ( ErrnoStatus::STATUS_50005 );
		}
		if (empty ( $phone_code )) {
			YDLib::output ( ErrnoStatus::STATUS_40003 );
		}
		if (empty ( $msg_code ) && $type != 'help') {
			YDLib::output ( ErrnoStatus::STATUS_40003 );
		}
		
		// 验证验证码是否正确
		if (SmsModel::codeIsYes ( $phone_code, $data ['mobile'], '2' ) == false) {
			YDLib::output ( ErrnoStatus::STATUS_60007 );
		}
		
		if ($type != 'help') { // 帮助注册的不检测图形验证码
			$mem = YDLib::getMem ( 'memcache' );
			// 验证验证码是否正确
			$code = $mem->get ( UserModel::$_login . '_' . SUPPLIER_ID . '_' . session_id () );
			
			if ($code != CommonBase::getBigTosmall ( $msg_code )) {
				YDLib::output ( ErrnoStatus::STATUS_60206 );
			}
		}
		
		$info = UserModel::checkUserByMobile ( $data ['mobile'] );
		
		$pd = UserModel::setPassword ( $data ['password'] );
		$data ['salt'] = $pd ['salt'];
		$data ['password'] = $pd ['password'];
		if ($info && $info ['supplier']) { // 如果都存在
		                                   
			// 都是正常的
			if ($info ['is_del'] == CommonBase::STATUS_SUCCESS && $info ['supplier'] ['is_del'] == CommonBase::STATUS_SUCCESS) {
				
				YDLib::output ( ErrnoStatus::STATUS_60002 );
			} else if ($info ['is_del'] == CommonBase::STATUS_SUCCESS && $info ['supplier'] ['is_del'] != CommonBase::STATUS_SUCCESS) { // 子商户已删除
				
				YDLib::output ( ErrnoStatus::STATUS_60002 );
			} else { // 都被删除
			         // 添加主信息
				$last_id = UserModel::addUser ( $data );
				if (! $last_id) {
					YDLib::output ( ErrnoStatus::STATUS_60011 );
				}
			}
		} else if ($info && empty ( $info ['supplier'] )) { // 商户不存在
		                                                  
			// 修改主信息添加子商户信息
			$last_id = UserModel::updataUserAndAddItem ( $data, $info ['id'] );
		} else { // 都不存在
		         
			// 添加主信息
			$last_id = UserModel::addUser ( $data );
			if (! $last_id) {
				YDLib::output ( ErrnoStatus::STATUS_60011 );
			}
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, [ 
				'invitation_id' => $data ['invitation_id'] 
		] );
	}
	
	/**
	 * 用户退出登陆接口
	 *
	 * <pre>
	 * POST参数
	 * user_id : 用户ID
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/User/signout
	 * 测试： http://testapi.qudiandang.com/v1/User/signout
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' => {用户id}
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
	public function signoutAction() {
		$user_id = $this->user_id;
		if ($user_id) {
			\Services\Auth\TokenAuthService::remove ( $user_id, SUPPLIER_ID, 'user' );
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
	}
	
	/**
	 * 快速登陆接口
	 *
	 * <pre>
	 * POST参数
	 * mobile : 注册手机，必填，页面先验证格式是否正确
	 * code : 验证码,必填；
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/User/fastLogin
	 * 测试： http://testapi.qudiandang.com/v1/User/fastLogin
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
	 *         'user_id' : 1
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
	public function fastLoginAction() {
		$mobile = $this->_request->get ( 'mobile' );
		$code = $this->_request->get ( 'code' );
        $invite_code = $this->_request->get ( 'invite_code' );

		try{
		    //解码邀请码
            $invite_code = $invite_code ? XDeode::decode ( $invite_code ):0;
            if (strlen($mobile) !== 11) {
                throw new Exception(ErrnoStatus::STATUS_40001 );
            }
            if (empty ( $code )) {
                throw new Exception(ErrnoStatus::STATUS_40003 );
            }
            // 验证验证码是否正确
            if (SmsModel::codeIsYes ( $code, $mobile, '5' ) == false) {
                throw new Exception ( ErrnoStatus::STATUS_60007 );
            }
            // 验证手机号是否正常
            $info = UserModel::checkUserByMobile ( $mobile );
            // 验证用户是否被禁用
            if ($info ['supplier'] ['status'] == '1') {
                throw new Exception ( ErrnoStatus::STATUS_10006 );
            }

            $token = '';

            if (! empty ( $info ) && ! empty ( $info ['supplier'] )) { // 都存在
                if ($info ['is_del'] != CommonBase::STATUS_SUCCESS || $info ['supplier'] ['is_del'] != CommonBase::STATUS_SUCCESS) { // 用户已注销
                    throw new Exception ( ErrnoStatus::STATUS_60029 );
                } else if ($info ['is_del'] == CommonBase::STATUS_SUCCESS && $info ['supplier'] ['is_del'] == CommonBase::STATUS_SUCCESS) {

                    $token = Publicb::getLoginToken ( $info ['id'] );
                    $user_id = $info ['id'];
                }
            } else if (! empty ( $info ) && empty ( $info ['supplier'] )) { // 该商户下不存在

                // 添加商户信息
                $last_id = UserSupplierModel::addUser ( $info ['id'] );
                if (! $last_id) {
                    throw new Exception ( ErrnoStatus::STATUS_60014 );
                }
                $token = Publicb::getLoginToken ( $info ['id'] );
                $user_id = $info ['id'];
            } else if (empty ( $info )) { // 都不存在
                // 添加主信息
                $data = [ ];
                $data ['mobile'] = $mobile;
                $data['invitation_id'] = $invite_code;
                $last_id = UserModel::addUser ( $data );
                if (! $last_id) {
                    throw new Exception ( ErrnoStatus::STATUS_60011 );
                }
                $user_id = $last_id;
                $token = Publicb::getLoginToken ( $user_id );
            }
            YDLib::output ( ErrnoStatus::STATUS_SUCCESS, [
                'user_id' => $user_id,
                'token' => ( string ) $token
            ] );
        }catch (Exception $exception){
            YDLib::output ( $exception->getMessage() );
        }
	}
	
	/**
	 * 密码登陆接口
	 *
	 * <pre>
	 * POST参数
	 * mobile : 注册手机，必填，页面先验证格式是否正确
	 * password : 密码,必填；
	 * msg_code : 验证码,必填；
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/User/login
	 * 测试： http://testapi.qudiandang.com/v1/User/login
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
	 *         'user_id' : 1
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
	public function loginAction() {
		$mobile = $this->_request->get ( 'mobile' );
		$msg_code = $this->_request->get ( 'msg_code' );
		$password = $this->_request->get ( 'password' );
		if (empty ( $mobile )) {
			YDLib::output ( ErrnoStatus::STATUS_40001 );
		}
		if (empty ( $password ) || ! YDLib::validData ( $password )) {
			YDLib::output ( ErrnoStatus::STATUS_40004 );
		}
		if (empty ( $msg_code ) || ! YDLib::validData ( $msg_code )) {
			YDLib::output ( ErrnoStatus::STATUS_40003 );
		}
		
		$mem = YDLib::getMem ( 'memcache' );
		// 验证验证码是否正确
		$code = $mem->get ( UserModel::$_login . '_' . SUPPLIER_ID . '_' . session_id () );
		
		if ($code != CommonBase::getBigTosmall ( $msg_code )) {
			YDLib::output ( ErrnoStatus::STATUS_60206 );
		}
		
		// 验证手机号是否正常
		$info = UserModel::checkUserByMobile ( $mobile );
		if ((! is_array ( $info ) || count ( $info ) == 0) && (! is_array ( $info ['supplier'] ) || count ( $info ['supplier'] ) == 0)) {
			YDLib::output ( ErrnoStatus::STATUS_60001 );
		}
		
		if ($info ['is_del'] != CommonBase::STATUS_SUCCESS || $info ['supplier'] ['is_del'] != CommonBase::STATUS_SUCCESS) { // 用户已注销
			YDLib::output ( ErrnoStatus::STATUS_60029 );
		}
		
		$user = UserModel::login ( $info, $password );
		if ($user == false) {
			YDLib::output ( ErrnoStatus::STATUS_60012 );
		}
		// 验证用户是否被禁用
		$child_user = UserSupplierModel::getAdminInfo ( $user ['id'] );
		
		if ($child_user ['status'] == '1') {
			YDLib::output ( ErrnoStatus::STATUS_10006 );
		}
		
		/**
		 * 此处为新增 token
		 */
		$token = Publicb::getLoginToken ( $user ['id'] );
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, [ 
				'user_id' => $info ['id'],
				'token' => ( string ) $token 
		] );
	}
	
	/**
	 * 小程序根据user_id和open_id登录接口
	 *
	 * <pre>
	 * POST参数
	 * mobile : 注册手机，必填，页面先验证格式是否正确
	 * password : 密码,必填；
	 * msg_code : 验证码,必填；
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/User/openIdAndUserIdLogin
	 * 测试： http://testapi.qudiandang.com/v1/User/openIdAndUserIdLogin
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
	 *         'user_id' : 1
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
	public function openIdAndUserIdLoginAction() {
		$user_id = $this->user_id;
		$open_id = $this->_request->get ( 'open_id' );
		if (empty ( $user_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		if (empty ( $open_id )) {
			YDLib::output ( ErrnoStatus::STATUS_60568 );
		}
		
		// 验证账号是否存在
		$data ['user_id'] = $user_id;
		$data ['openid'] = $open_id;
		
		$info = UserSupplierThridModel::getInfoByOtherId ( $data, 'mini' );
		if ($info == false) {
			YDLib::output ( ErrnoStatus::STATUS_10004 );
		}
		// 验证用户是否被禁用
		if ($info ['supplier'] ['status'] == '1') {
			YDLib::output ( ErrnoStatus::STATUS_10006 );
		}
		
		/**
		 * 此处为新增 token
		 */
		$token = Publicb::getLoginToken ( $info ['user_id'] );
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, [ 
				'user_id' => $info ['user_id'],
				'token' => ( string ) $token 
		] );
	}
	
	/**
	 * 获取图片验证码接口
	 *
	 * <pre>
	 * POST参数
	 *
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/User/imgCode
	 * 测试： http://testapi.qudiandang.com/v1/User/imgCode
	 *
	 * </pre>
	 *
	 * @return string 直接调用url
	 *         <pre>
	 *         成功：
	 *         [ ]
	 *        
	 *         失败：
	 *         []
	 *         </pre>
	 */
	public function imgCodeAction() {
		return Publicb::getCode ( 4, 110, 42, UserModel::$_login );
	}
	
	/**
	 * 是否登陆接口
	 *
	 * <pre>
	 * POST参数
	 * user_id : 用户id，必填
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/User/isLogin
	 * 测试： http://testapi.qudiandang.com/v1/User/isLogin
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' => {}
	 *         ]
	 *        
	 *         失败：
	 *         [
	 *         'errno' => 50006,
	 *         'errormsg' => '未登录'
	 *         'result' => {}
	 *         ]
	 *         </pre>
	 */
	public function isLoginAction() {
		if (! $this->user_id) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		} else {
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS, [ 
					'user_id' => $this->user_id 
			] );
		}
	}
	
	/**
	 * 用户重置密码接口
	 *
	 * <pre>
	 * POST参数
	 * mobile : 重置手机，必填，页面先验证格式是否正确
	 * password : 密码，必填
	 * repassword : 确认密码，必填 验证是否一致
	 * msg_code ：页面验证码
	 * phone_code : 手机验证码
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/User/resetPassword
	 * 测试： http://testapi.qudiandang.com/v1/User/resetPassword
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' => {}
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
	public function resetPasswordAction() {
		$data = [ ];
		$data ['mobile'] = $this->_request->get ( 'mobile' );
		$data ['password'] = $this->_request->get ( 'password' );
		$repassword = $this->_request->get ( 'repassword' );
		$msg_code = $this->_request->get ( 'msg_code' );
		$phone_code = $this->_request->get ( 'phone_code' );
		if (empty ( $data ['mobile'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40001 );
		}
		if (empty ( $data ['password'] ) || ! YDLib::validData ( $data ['password'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40004 );
		}
		if (empty ( $repassword ) || ! YDLib::validData ( $repassword )) {
			YDLib::output ( ErrnoStatus::STATUS_40007 );
		}
		if ($data ['password'] != $repassword) {
			YDLib::output ( ErrnoStatus::STATUS_50005 );
		}
		if (empty ( $msg_code ) || empty ( $phone_code )) {
			YDLib::output ( ErrnoStatus::STATUS_40003 );
		}
		
		// 验证验证码是否正确
		if (SmsModel::codeIsYes ( $phone_code, $data ['mobile'], '3' ) == false) {
			YDLib::output ( ErrnoStatus::STATUS_60007 );
		}
		
		$mem = YDLib::getMem ( 'memcache' );
		// 验证验证码是否正确
		$code = $mem->get ( UserModel::$_login . '_' . SUPPLIER_ID . '_' . session_id () );
		
		if ($code != CommonBase::getBigTosmall ( $msg_code )) {
			YDLib::output ( ErrnoStatus::STATUS_60206 );
		}
		
		$info = UserModel::checkUserByMobile ( $data ['mobile'] );
		if ((! is_array ( $info ) || count ( $info ) == 0) && (! is_array ( $info ['supplier'] ) || count ( $info ['supplier'] ) == 0)) {
			YDLib::output ( ErrnoStatus::STATUS_60001 );
		}
		
		if ($info ['is_del'] != CommonBase::STATUS_SUCCESS || $info ['supplier'] ['is_del'] != CommonBase::STATUS_SUCCESS) { // 用户已注销
			YDLib::output ( ErrnoStatus::STATUS_60029 );
		}
		
		$pd = UserModel::setPassword ( $data ['password'] );
		$update ['salt'] = $pd ['salt'];
		$update ['password'] = $pd ['password'];
		// 添加主信息
		$last_id = UserModel::updateByID ( $update, $info ['id'] );
		if (! $last_id) {
			YDLib::output ( ErrnoStatus::STATUS_60017 );
		}
		
		// 清除
		\Services\Auth\TokenAuthService::remove ( $info ['id'], SUPPLIER_ID, 'user' );
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
	}
	
	/**
	 * 获取个人信息接口
	 *
	 * <pre>
	 * POST参数
	 * user_id : 用户id，必填
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/User/userInfo
	 * 测试： http://testapi.qudiandang.com/v1/User/userInfo
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
	 *         "id": 1,
	 *         "name": "黄大国",
	 *         "mobile": 18513854271,
	 *         "user_img":XXXXXX.img
	 *         "sex": 1,性别：0保密 1男 2女
	 *         "birthday": "1994-08-13",
	 *         "qq": 714982518,
	 *         "wchat": 714982518,
	 *         "province_id": 13,
	 *         "city_id": 1099,
	 *         "area_id": 2773,
	 *         "address": "闫什镇",
	 *         "province_name": "山东",
	 *         "city_name": "菏泽市",
	 *         "area_name": "鄄城县"
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
	public function userInfoAction() {
		$user_id = $this->user_id;
		if (empty ( $user_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		$user = UserModel::getAdminInfo ( $user_id );
		if (! $user) {
			YDLib::output ( ErrnoStatus::STATUS_60001 );
		}
		
		if (! empty ( $user ['user_img'] )) {
			$user ['user_img'] = HOST_FILE . CommonBase::imgSize ( $user ['user_img'], 1 );
		} else {
			$user ['user_img'] = HOST_STATIC . 'common/images/user_photo.jpg';
		}
		$user ['province_name'] = AreaModel::getInfoByID ( $user ['province_id'] ) ['area_name'];
		$user ['city_name'] = AreaModel::getInfoByID ( $user ['city_id'] ) ['area_name'];
		$user ['area_name'] = AreaModel::getInfoByID ( $user ['area_id'] ) ['area_name'];
		
		$user ['coupan_num'] = UserCoupanModel::getNum ( [ 
				'user_id' => $user_id,
				'status' => '1' 
		] );
		
		// 【1：待付款，2：待发货，3：待收货，4：已完成，6：售后】
		$search ['user_id'] = $user ['id'];
		// 待付款
		$status = CommonBase::ORDER_STATUS_VALUE ['1'];
		$search ['status'] = implode ( ',', $status );
		
		$pending_payment = OrderChildModel::getList ( $search, '1', '1' );
		$user ['pending_payment'] = $pending_payment ['total'];
		
		// 待发货
		$status = CommonBase::ORDER_STATUS_VALUE ['2'];
		$search ['status'] = implode ( ',', $status );
		
		$pending_delivery = OrderChildModel::getList ( $search, '1', '1' );
		$user ['pending_delivery'] = $pending_delivery ['total'];
		
		// 待收货
		$status = CommonBase::ORDER_STATUS_VALUE ['3'];
		$search ['status'] = implode ( ',', $status );
		
		$goods_to_be_received = OrderChildModel::getList ( $search, '1', '1' );
		$user ['goods_to_be_received'] = $goods_to_be_received ['total'];
		
		// 已完成
		$status = CommonBase::ORDER_STATUS_VALUE ['4'];
		$search ['status'] = implode ( ',', $status );
		
		$completed = OrderChildModel::getList ( $search, '1', '1' );
		$user ['completed_over'] = $completed ['total'];
		
		// 售后
		$status = CommonBase::ORDER_STATUS_VALUE ['6'];
		$search ['status'] = implode ( ',', $status );
		
		$after_sale = OrderChildModel::getList ( $search, '1', '1' );
		$user ['after_sale'] = $after_sale ['total'];
		
		// 邀请数
		$user ['invitation_num'] = 0;
		
		$invitation = UserModel::getInvitationList ( $user_id );
		if (isset ( $invitation ['total'] ) && $invitation ['total']) {
			$user ['invitation_num'] = $invitation ['total'];
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $user );
	}
	
	/**
	 * 编辑个人信息接口
	 *
	 * <pre>
	 * POST参数
	 * user_id : 用户id，必填
	 * user_img : 头像地址，非必填
	 * name : 姓名，非必填
	 * sex : 性别，有默认值
	 * birthday : 生日，非必填 不含时分秒
	 * qq : qq，非必填
	 * wchat : 微信，非必填
	 * province_id : 省，非必填
	 * city_id : 市，非必填
	 * area_id : 区，非必填
	 * address : 详细地址，非必填
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/User/editUser
	 * 测试： http://testapi.qudiandang.com/v1/User/editUser
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' => {}
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
	public function editUserAction() {
		$user_id = $this->user_id;
		if (empty ( $user_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		$data = [ ];
		$data ['user_img'] = $this->_request->get ( 'user_img' );
		$data ['name'] = $this->_request->get ( 'name' );
		$data ['sex'] = $this->_request->get ( 'sex' ) ? $this->_request->get ( 'sex' ) : '0';
		$data ['birthday'] = $this->_request->get ( 'birthday' );
		$data ['qq'] = $this->_request->get ( 'qq' );
		$data ['wchat'] = $this->_request->get ( 'wchat' );
		$data ['province_id'] = $this->_request->get ( 'province_id' );
		$data ['city_id'] = $this->_request->get ( 'city_id' );
		$data ['area_id'] = $this->_request->get ( 'area_id' );
		$data ['address'] = $this->_request->get ( 'address' );
		foreach ( $data as $key => $val ) {
			if (empty ( $data ['province_id'] )) {
				unset ( $data ['province_id'] );
			}
			if (empty ( $data ['city_id'] )) {
				unset ( $data ['city_id'] );
			}
			if (empty ( $data ['area_id'] )) {
				unset ( $data ['area_id'] );
			}
			if (empty ( $data ['user_img'] )) {
				unset ( $data ['user_img'] );
			}
		}
		
		$user = UserModel::updateByID ( $data, $user_id );
		if (! $user) {
			YDLib::output ( ErrnoStatus::STATUS_60084 );
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
	}
	
	/**
	 * 补全手机号接口
	 *
	 * <pre>
	 * POST参数
	 * user_id : 用户id，必填
	 * mobile : 手机号，必填
	 * phone_code : 手机验证码
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/User/addMobile
	 * 测试： http://testapi.qudiandang.com/v1/User/addMobile
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' => {}
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
	public function addMobileAction() {
		$user_id = $this->user_id;
		if (empty ( $user_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		$data = [ ];
		$data ['mobile'] = $this->_request->get ( 'mobile' );
		$data ['password'] = $this->_request->get ( 'password' );
		$repassword = $this->_request->get ( 'repassword' );
		$phone_code = $this->_request->get ( 'phone_code' );
		if (empty ( $data ['mobile'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40001 );
		}
		if (empty ( $data ['password'] ) || ! YDLib::validData ( $data ['password'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40004 );
		}
		if (empty ( $repassword ) || ! YDLib::validData ( $repassword )) {
			YDLib::output ( ErrnoStatus::STATUS_40007 );
		}
		if ($data ['password'] != $repassword) {
			YDLib::output ( ErrnoStatus::STATUS_50005 );
		}
		if (empty ( $phone_code )) {
			YDLib::output ( ErrnoStatus::STATUS_40003 );
		}
		
		// 验证验证码是否正确
		if (SmsModel::codeIsYes ( $phone_code, $data ['mobile'], '6' ) == false) {
			YDLib::output ( ErrnoStatus::STATUS_60007 );
		}
		$pd = UserModel::setPassword ( $data ['password'] );
		$data ['salt'] = $pd ['salt'];
		$data ['password'] = $pd ['password'];
		
		// 补全手机号信息
		UserModel::updateInfo ( $data, $user_id );
	}
	
	/**
	 * 创建投诉建议接口
	 *
	 * <pre>
	 * POST参数
	 * user_id : 用户id，必填
	 * img_url : 头像地址，非必填 （json格式的数组）
	 * proposal : 建议内容，必填
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/User/proposal
	 * 测试： http://testapi.qudiandang.com/v1/User/proposal
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' => {}
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
	public function proposalAction() {
		$data = [ ];
		$data ['user_id'] = $this->user_id;
		$data ['proposal'] = $this->_request->get ( 'proposal' );
		$img_url = $this->_request->get ( 'img_url' );
		if (empty ( $data ['user_id'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		if (empty ( $data ['proposal'] ) || ! YDLib::validData ( $data ['proposal'] )) {
			YDLib::output ( ErrnoStatus::STATUS_60550 );
		}
		
		$last_id = UserProposalModel::addData ( $data, $img_url );
		if (! $last_id) {
			YDLib::output ( ErrnoStatus::STATUS_60062 );
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
	}
	
	/**
	 * 联系我们接口
	 *
	 * <pre>
	 * POST参数
	 * mobile : 手机号，必填
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/User/contactUs
	 * 测试： http://testapi.qudiandang.com/v1/User/contactUs
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' => {}
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
	public function contactUsAction() {
		$data = [ ];
		// $data ['user_id'] = $this->_request->get ( 'user_id' );
		$data ['mobile'] = $this->_request->get ( 'mobile' );
		/*
		 * if (empty ( $data ['user_id'] )) {
		 * YDLib::output ( ErrnoStatus::STATUS_40015 );
		 * }
		 */
		if (empty ( $data ['mobile'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40001 );
		}
		
		$info = UserContactUsModel::getInfoByMobile ( $data ['mobile'] );
		if ($info && $info ['status'] == 1) {
			YDLib::output ( ErrnoStatus::STATUS_60592 );
		}
		
		$last_id = UserContactUsModel::addData ( $data );
		if (! $last_id) {
			
			YDLib::output ( ErrnoStatus::STATUS_60062 );
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
	}
	
	/**
	 * 商品收藏/取消收藏接口
	 *
	 * <pre>
	 * POST参数
	 * user_id : 用户id，必填
	 * product_id : 商品id，必填
	 * type : 1:收藏 2：取消收藏 ，必填
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/User/userConcern
	 * 测试： http://testapi.qudiandang.com/v1/User/userConcern
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' => {}
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
	public function userConcernAction() {
		$data = [ ];
		$data ['user_id'] = $this->user_id;
		$data ['product_id'] = $this->_request->get ( 'product_id' );
		$type = $this->_request->get ( 'type' );
		if (empty ( $data ['user_id'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		if (empty ( $data ['product_id'] )) {
			YDLib::output ( ErrnoStatus::STATUS_60025 );
		}
		
		if (empty ( $type )) {
			YDLib::output ( ErrnoStatus::STATUS_60021 );
		}
		$con = UserConcernModel::getInfoByID ( $data ['user_id'], $data ['product_id'] );
		$product = ProductModel::getInfoByID ( $data ['product_id'] );
		
		if ($type == '1') {
			
			if ($con) {
				$info ['is_del'] = '2';
				$concern = UserConcernModel::updateByID ( $info, $con ['id'] );
			} else {
				$concern = UserConcernModel::addData ( $data );
			}
			
			if (! $concern) {
				YDLib::output ( ErrnoStatus::STATUS_60055 );
			}
			
			$productInfo = ProductChannelModel::getInfoByID ( $data ['product_id'] );
			// 更新收藏量
			if ($productInfo) {
				$productClData ['collect_num'] = intval ( $productInfo ['collect_num'] + 1 );
				ProductChannelModel::updateByID ( $productClData, $data ['product_id'] );
			}
			// 更新收藏量
			$productData ['collect_num'] = intval ( $product ['collect_num'] + 1 );
			ProductModel::updateByID ( $productData, $data ['product_id'] );
		} else if ($type == '2') {
			
			if ($con) {
				$concern = UserConcernModel::deleteByID ( $data ['user_id'], $data ['product_id'] );
			} else {
				YDLib::output ( ErrnoStatus::STATUS_60058 );
			}
			
			if (! $concern) {
				YDLib::output ( ErrnoStatus::STATUS_60057 );
			}
			// 更新收藏量
			$productInfo = ProductChannelModel::getInfoByID ( $data ['product_id'] );
			
			// 更新收藏量
			if ($productInfo) {
				$productClData ['collect_num'] = intval ( $productInfo ['collect_num'] - 1 );
				ProductChannelModel::updateByID ( $productClData, $data ['product_id'] );
			}
			
			// 更新收藏量
			$productData ['collect_num'] = intval ( $product ['collect_num'] - 1 );
			ProductModel::updateByID ( $productData, $data ['product_id'] );
		} else {
			YDLib::output ( ErrnoStatus::STATUS_60021 );
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS );
	}
	
	/**
	 * 获取收藏列表接口
	 *
	 *
	 * <pre>
	 * 正式： http://api.qudiandang.com/v1/User/concernList
	 * 测试： http://testapi.qudiandang.com/v1/User/concernList
	 * </pre>
	 *
	 * <pre>
	 * 参数：
	 * page: 页码 非必填 【空：1】
	 * rows: 条数 非必填 【空：10】
	 * user_id： 用户ID 必填
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *        
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": 0,
	 *         "errmsg": "请求成功",
	 *         "result": [
	 *         "page": 1,
	 *         "total": 2,
	 *         "list": [
	 *         {
	 *         "id": 1,
	 *         "name": "商品1",
	 *         "market_price": 1000000,
	 *         "sale_price": 10000,
	 *         "logo_url": "http://static.qudiandang.com/common/images/common.png"
	 *         },
	 *         {
	 *         "id": 2,
	 *         "name": "商品2",
	 *         "market_price": 1000000,
	 *         "sale_price": 10000,
	 *         "logo_url": "http://static.qudiandang.com/common/images/common.png"
	 *         }
	 *         ]
	 *         }
	 *        
	 *         失败：
	 *         [
	 *         'errno' => -1,
	 *         'errormsg' => '系统繁忙'
	 *         'result' => {}
	 *         ]
	 *         </pre>
	 */
	public function concernListAction() {
		$page = $this->_request->getPost ( 'page' );
		$page = ! empty ( $page ) ? intval ( $page ) : 1;
		$page = $page > 0 ? $page : 1;
		
		$rows = $this->_request->getPost ( 'rows' );
		$rows = ! empty ( $rows ) ? intval ( $rows ) : 10;
		$rows = $rows > 0 ? $rows : 10;
		
		$user_id = $this->user_id;
		if (empty ( $user_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		$list = UserConcernModel::getList ( $user_id, $page, $rows );
		
		if ($list == false) {
			$data ['page'] = $page;
			$data ['total'] = 0;
			$data ['list'] = [ ];
		} else {
			$data ['page'] = $page;
			$data ['total'] = $list ['total'];
			$data ['list'] = $list ['list'];
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data );
	}
	
	/**
	 * 个人查询投诉建议接口
	 *
	 * <pre>
	 * POST参数
	 * user_id : 用户id，必填
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/User/userProposal
	 * 测试： http://testapi.qudiandang.com/v1/User/userProposal
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' => {}
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
	public function userProposalAction() {
		$user_id = $this->user_id;
		
		if (empty ( $user_id )) {
			YDLib::output ( ErrnoStatus::STATUS_40015 );
		}
		
		$page = $this->_request->getPost ( 'page' );
		$page = ! empty ( $page ) ? intval ( $page ) : 1;
		$page = $page > 0 ? $page : 1;
		
		$rows = $this->_request->getPost ( 'rows' );
		$rows = ! empty ( $rows ) ? intval ( $rows ) : 10;
		$rows = $rows > 0 ? $rows : 10;
		
		$userInfo = UserProposalModel::getSuggestAll ( $user_id, $page, $rows );
		
		if (! $userInfo) {
			YDLib::output ( ErrnoStatus::STATUS_60062 );
		}
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $userInfo ['rows'] );
	}
	
	/**
	 * 查询邀请用户列表
	 *
	 * <pre>
	 * POST参数
	 * 无
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/User/invitation
	 * 测试： http://testapi.qudiandang.com/v1/User/invitation
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' => {}
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
	public function invitationAction() {
		try {
			$user_id = $this->user_id;
			if (! $user_id) {
				YDLib::output ( ErrnoStatus::STATUS_40015 );
			}
			$user = UserModel::getInvitationList ( $user_id );
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $user );
		} catch ( Exception $exception ) {
			$this->error ( $exception->getMessage () );
		}
	}
	
	/**
	 * 查询鉴定证书
	 *
	 * <pre>
	 * POST参数
	 * id 商品ID
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/User/appraisal
	 * 测试： http://testapi.qudiandang.com/v1/User/appraisal
	 *
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *         <pre>
	 *         成功：
	 *         [
	 *         'errno' => 0,
	 *         'errormsg' => '操作成功'
	 *         'result' => {}
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
	public function appraisalAction() {
		$serial = $this->_request->getPost ( 'serial' );
		
		$info = AppraisalModel::getInfoByID ( $serial );
		if ($info) {
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $info );
		} else {
			YDLib::output ( ErrnoStatus::STATUS_FAIL );
		}
	}
}

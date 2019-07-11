<?php
/** 
 * 微信小程序方法
 * @version v0.01
 * @author lqt
 * @time 2018-06-13
 */
use Custom\YDLib;
use Mini\MiniWXBizDataCrypt;
use User\UserModel;
use User\UserSupplierModel;
use User\UserSupplierThridModel;
use Common\CommonBase;
use User\UserSupplierBindModel;
class MiniController extends BaseController {
	/**
	 * 解析加密数据
	 *
	 * <pre>
	 * post参数
	 * identif : 供应商标识
	 * sessionKey : sessionKey
	 * encryptedData : encryptedData
	 * iv : iv
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： https://api.qudiandang.com/v1/mini/decrypt/?identif=test
	 * 测试： https://testapi.qudiandang.com/v1/mini/decrypt/?identif=test
	 *
	 * </pre>
	 *
	 * @return string 返回json数据格式
	 *         <pre>
	 *        
	 *         </pre>
	 */
	public function decryptAction() {
		// $appid = 'wx4f4bc4dec97d474b';
		// $sessionKey = 'tiihtNczf5v6AKRyjwEUhQ==';
		// $encryptedData="CiyLU1Aw2KjvrjMdj8YKliAjtP4gsMZM
		// QmRzooG2xrDcvSnxIMXFufNstNGTyaGS
		// 9uT5geRa0W4oTOb1WT7fJlAC+oNPdbB+
		// 3hVbJSRgv+4lGOETKUQz6OYStslQ142d
		// NCuabNPGBzlooOmB231qMM85d2/fV6Ch
		// evvXvQP8Hkue1poOFtnEtpyxVLW1zAo6
		// /1Xx1COxFvrc2d7UL/lmHInNlxuacJXw
		// u0fjpXfz/YqYzBIBzD6WUfTIF9GRHpOn
		// /Hz7saL8xz+W//FRAUid1OksQaQx4CMs
		// 8LOddcQhULW4ucetDf96JcR3g0gfRK4P
		// C7E/r7Z6xNrXd2UIeorGj5Ef7b1pJAYB
		// 6Y5anaHqZ9J6nKEBvB4DnNLIVWSgARns
		// /8wR2SiRS7MNACwTyrGvt9ts8p12PKFd
		// lqYTopNHR1Vf7XjfhQlVsAJdNiKdYmYV
		// oKlaRv85IfVunYzO0IKXsyl7JCUjCpoG
		// 20f0a04COwfneQAGGwd5oa+T8yO5hzuy
		// Db/XcxxmK01EpqOyuxINew==";
		// $iv = 'r7BXXKkLb8qrSNn05n0qiA==';
		$sessionKey = $this->_request->get ( "sessionKey" );
		YDLib::testlog ( $sessionKey );
		if (! $sessionKey) {
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS, 'sessionKey' );
		}
		
		$encryptedData = $this->_request->get ( "encryptedData" );
		YDLib::testlog ( $encryptedData );
		if (! $encryptedData) {
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS, 'encryptedData' );
		}
		
		$iv = $this->_request->get ( "iv" );
		YDLib::testlog ( $iv );
		if (! $iv) {
			YDLib::output ( ErrnoStatus::STATUS_SUCCESS, 'iv' );
		}
		
		// $sessionKey = 'rSOmdZNr4r2gEpHiFtGemw==';
		// $encryptedData="/E2QXxrnEwB6ldIEHicYLH8JKN8S3XYnTmsjs6E8ALT89E/QmXpGwOgBBb/cqTS+yKZZOZ3J+aSPgcuTic22DSfXZ44jxToXlinE5YyQ0w1zZx5znO6sPznGF7r47bJTgxHvJfaPcSew3M1IZb6Ci+d5He+sU89VeuxuTgB7J6+4Mx5bQUcSt4QNbr2pE88SBS6z2q8jSEXQhSEioog/Tw==";
		// $iv = 'Yhqwz6ACdjxNu4NBIsUm8g==';
		
		$appid = 'wx942b3cc0357fd683';
		
		$pc = new MiniWXBizDataCrypt ( $appid, $sessionKey );
		$errCode = $pc->decryptData ( $encryptedData, $iv, $data );
		YDLib::testlog ( $errCode );
		YDLib::testlog ( $data );
		// print_r($errCode);
		// print_r($data);
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $data );
	}
	
	/**
	 * 小程序授权登陆
	 *
	 * <pre>
	 * post参数
	 * identif:供应商标识
	 * nickName:昵称
	 * gender:性别
	 * language:语言
	 * city:市
	 * province:省
	 * country:国家
	 * avatarUrl:头像地址
	 * code:小程序登陆dode
	 * encryptedData:手机号加密字符串
	 * iv:手机号加密序列
	 *
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： https://api.qudiandang.com/v1/mini/login
	 * 测试： https://testapi.qudiandang.com/v1/mini/login
	 *
	 * </pre>
	 *
	 * @return string 返回json数据格式
	 *         <pre>
	 *        
	 *         </pre>
	 */
	public function loginAction() {
		// identif:test
		// nickName:海歆乐滔
		// gender:1
		// language:zh_CN
		// city:Chaoyang
		// province:Beijing
		// country:China
		// avatarUrl:https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKcRygMwYWWicESmz57vFABXdB7jP3DiaxTVUTicLZE08ECha06UnP3mia9eTSDJjsuib2AkltNYkpwaVA/132
		// code:001SkPXB1GXtA30hrTXB1WDYXB1SkPX6
		// encryptedData:UKC8b8ZaEAFyWq4vuVfplOeg2RGHmR4Sa+Rm81LxxFz2EASO1GMo97xAYTeAi2IU78Ckd96eLybYGqpSE2F9OR2Zn2SjupwWOOoLV4x+/vvnwx0WqbxFpRWRpZhGdiZTGKdgIzsu/kRCCBzr5FJMKOD7B3p4hKUzqcPFpG4hIDxorzAgb76SU82e6GZI0HkqZr7QWquNCBjPpfGk8yYJUQ==
		// iv:9FMv/B+R2+15NN3A1wX8OQ==
		$user ['nickName'] = $this->_request->get ( "nickName" );
		$user ['gender'] = $this->_request->get ( "gender" );
		$user ['language'] = $this->_request->get ( "language" );
		$user ['city'] = $this->_request->get ( "city" );
		$user ['province'] = $this->_request->get ( "province" );
		$user ['country'] = $this->_request->get ( "country" );
		$user ['avatarUrl'] = $this->_request->get ( "avatarUrl" );
		YDLib::testlog ( 'user.........' );
		YDLib::testlog ( $user );
		
		$encryptedData = $_REQUEST ['encryptedData'];
		YDLib::testlog ( 'encryptedData:' . $encryptedData );
		if (! $encryptedData) {
			YDLib::output ( ErrnoStatus::STATUS_60567, 'encryptedData is null' );
		}
		$iv = $_REQUEST ['iv'];
		YDLib::testlog ( 'iv:' . $iv );
		if (! $iv) {
			YDLib::output ( ErrnoStatus::STATUS_60567, 'iv is null' );
		}
		
		$session_key = $_REQUEST ['session_key'];
		$openid = $_REQUEST ['open_id'];
		YDLib::testlog ( '$session_key:' . $session_key );
		YDLib::testlog ( '$openid:' . $openid );
		
		$pc = new MiniWXBizDataCrypt ( MINI_WEIXIN_APPID, $session_key );
		$errCode = $pc->decryptData ( $encryptedData, $iv, $data );
		YDLib::testlog ( $errCode );
		YDLib::testlog ( $data );
		$data_array = json_decode ( $data, TRUE ); // 对JSON格式的字符串进行编码
		YDLib::testlog ( $data_array );
		
		YDLib::testlog ( $data_array );
		$user ['purePhoneNumber'] = $data_array ['purePhoneNumber'];
		$user ['openid'] = $openid;
		YDLib::testlog ( 'user.........' );
		YDLib::testlog ( $user );
		
		if (! $user ['purePhoneNumber']) {
			YDLib::output ( ErrnoStatus::STATUS_60567 );
		}
		
		// 手机号是否存在
		$old_user = UserModel::checkUserByMobile ( $data_array ['purePhoneNumber'] );
		YDLib::testlog ( '$old_user.........' );
		YDLib::testlog ( $old_user );
		YDLib::testlog ( '$old_user.........' );
		if ($old_user) {
			// 本门店是否存在该用户
			$supplier_user_id = false;
			if ($old_user ['supplier'] == false) {
				// 不存在，添加用户本门店信息
				$supplier_user_id = UserSupplierModel::addUser ( $old_user ['id'] );
				if ($supplier_user_id == false) {
					YDLib::output ( ErrnoStatus::STATUS_60565 );
				}
			}
			
			// 小程序是否已经授权
			// $min_three = UserSupplierThridModel::getInfoByUserId($old_user['id'],'mini');
			$search ['user_id'] = $old_user ['id'];
			$search ['type'] = CommonBase::USER_THRID_TYPE_2;
			$min_three = UserSupplierBindModel::getInfo ( $search );
			
			YDLib::testlog ( '$$min_three.........' );
			YDLib::testlog ( $min_three );
			YDLib::testlog ( '$$min_three.........' );
			if ($min_three == false) {
				// 不存在添加信息进三方表
				$thrid = [ ];
				$thrid ['user_id'] = $old_user ['id'];
				$thrid ['openid'] = $user ['openid'];
				$thrid ['nickname'] = $user ['nickName'];
				$thrid ['sex'] = $user ['gender'];
				$thrid ['head_img_url'] = $user ['avatarUrl'];
				$thrid ['thirdparty'] = 'mini';
				$thrid ['source_file'] = json_encode ( $user );
				if ($user ['country'] == 'CN') {
					$province = AreaModel::getProvinceByPinyin ( CommonBase::getBigTosmall ( $user ['province'] ) );
					$city = AreaModel::getProvinceByPinyin ( CommonBase::getBigTosmall ( $user ['city'] ), $user ['province_id'] );
					$thrid ['province_name'] = $province ['area_name'];
					$thrid ['city_name'] = $city ['area_name'];
				}
				if (isset ( $user ['unionid'] )) {
					$thrid ['unionid'] = $user ['unionid'];
				}
				$thrid_add = UserSupplierThridModel::addUser ( $thrid );
				
				YDLib::testlog ( '$$$thrid_add.........' );
				YDLib::testlog ( $thrid_add );
				YDLib::testlog ( '$$$thrid_add.........' );
				if (! $thrid_add) {
					if ($supplier_user_id != false) {
						$del_supplier = UserSupplierModel::deleteByID ( $old_user ['id'] );
					}
					
					YDLib::output ( ErrnoStatus::STATUS_60565 );
				}
				
				// 添加绑定表
				$add = [ ];
				$add ['user_id'] = $old_user ['id'];
				$add ['thrid_id'] = $thrid_add;
				$add ['type'] = CommonBase::USER_THRID_TYPE_2;
				
				$bind_id = UserSupplierBindModel::addData ( $add );
				if (! $bind_id) {
					UserSupplierThridModel::deleteByUserID ( $old_user ['id'] );
					if ($supplier_user_id != false) {
						$del_supplier = UserSupplierModel::deleteByID ( $old_user ['id'] );
					}
					YDLib::output ( ErrnoStatus::STATUS_60565 );
				}
			} else {
				// openid是否一致不一致进行更新
				$old_three = UserSupplierThridModel::getInfoByUserId ( $old_user ['id'], 'mini' );
				if ($old_three ['openid'] != $user ['openid']) {
					$thrid ['openid'] = $user ['openid'];
					$thrid ['nickname'] = $user ['nickName'];
					$thrid ['sex'] = $user ['gender'];
					$thrid ['head_img_url'] = $user ['avatarUrl'];
					$thrid ['source_file'] = json_encode ( $user );
					if ($user ['country'] == 'CN') {
						$province = AreaModel::getProvinceByPinyin ( CommonBase::getBigTosmall ( $user ['province'] ) );
						$city = AreaModel::getProvinceByPinyin ( CommonBase::getBigTosmall ( $user ['city'] ), $user ['province_id'] );
						$thrid ['province_name'] = $province ['area_name'];
						$thrid ['city_name'] = $city ['area_name'];
					}
					if (isset ( $user ['unionid'] )) {
						$thrid ['unionid'] = $user ['unionid'];
					}
					$update = UserSupplierThridModel::updateByID ( $thrid, $old_user ['id'], 'mini' );
					
					YDLib::testlog ( '$update.........' );
					YDLib::testlog ( $update );
					YDLib::testlog ( '$update.........' );
				}
			}
			
			// 登录
//			Publicb::loginCookie ( $old_three ['user_id']);
			$user_id = $old_user ['id'];

			$token = Publicb::getLoginToken($user_id);
			
			YDLib::testlog ( '$$user_id......111...' );
			YDLib::testlog ( $user_id );
			YDLib::testlog ( '$$user_id......111...' );
		} else {
			// 存数据库
			$userinfo = [ ];
			$userinfo ['user_img'] = Publicb::getImage ( $user ['avatarUrl'] );
			$userinfo ['name'] = $user ['nickName'];
			$userinfo ['sex'] = $user ['gender'];
			$userinfo ['mobile'] = $data_array ['purePhoneNumber'];
			if ($user ['country'] == 'CN') {
				$province = AreaModel::getProvinceByPinyin ( CommonBase::getBigTosmall ( $user ['province'] ) );
				$userinfo ['province_id'] = $province ['area_id'];
				$city = AreaModel::getProvinceByPinyin ( CommonBase::getBigTosmall ( $user ['city'] ), $user ['province_id'] );
				$userinfo ['city_id'] = $city ['area_id'];
			}
			$last_id = UserModel::addUser ( $userinfo );
			
			YDLib::testlog ( '$$last_id.......2222..' );
			YDLib::testlog ( $last_id );
			YDLib::testlog ( '$$last_id......22222...' );
			if (! $last_id) {
				YDLib::output ( ErrnoStatus::STATUS_60567 );
			}
			// 添加第三方信息
			$thrid = [ ];
			$thrid ['user_id'] = $last_id;
			$thrid ['openid'] = $user ['openid'];
			$thrid ['nickname'] = $user ['nickName'];
			$thrid ['sex'] = $user ['gender'];
			$thrid ['head_img_url'] = $user ['avatarUrl'];
			$thrid ['thirdparty'] = 'mini';
			$thrid ['source_file'] = json_encode ( $user );
			if ($user ['country'] == 'CN') {
				$thrid ['province_name'] = $province ['area_name'];
				$thrid ['city_name'] = $city ['area_name'];
			}
			if (isset ( $user ['unionid'] )) {
				$thrid ['unionid'] = $user ['unionid'];
			}
			$thrid_add = UserSupplierThridModel::addUser ( $thrid );
			
			YDLib::testlog ( '$$$thrid_add.....2222....' );
			YDLib::testlog ( $thrid_add );
			YDLib::testlog ( '$$$thrid_add......2222...' );
			if ($thrid_add == false) {
				$del = UserModel::deleteByID ( $last_id );
				YDLib::output ( ErrnoStatus::STATUS_60565 );
			}
				
//			Publicb::loginCookie ( $last_id);
            $token = Publicb::getLoginToken($last_id);
		
			$user_id = $last_id;
			
			// 添加绑定表
			$add = [ ];
			$add ['user_id'] = $user_id;
			$add ['thrid_id'] = $thrid_add;
			$add ['type'] = CommonBase::USER_THRID_TYPE_2;
			$bind_id = UserSupplierBindModel::addData ( $add );
			if (! $bind_id) {
				$del = UserModel::deleteByID ( $last_id );
				UserSupplierThridModel::deleteByUserID ( $last_id, 'mini' );
				YDLib::output ( ErrnoStatus::STATUS_60565 );
			}
			
			YDLib::testlog ( '$$$$user_id.....22223....' );
			YDLib::testlog ( $user_id );
			YDLib::testlog ( '$$$$user_id......22223...' );
		}
		
		YDLib::testlog ( '$user_id.....22223444....' );
		YDLib::testlog ( $user_id );
		YDLib::testlog ( '$user_id......222234444...' );
		YDLib::testlog ( '$supplier_id......222234444...' . SUPPLIER_ID );
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, [ 
				'user_id' => $user_id,
				'open_id' => $user ['openid'] ,
                'token' => $token
		] );
	}
}

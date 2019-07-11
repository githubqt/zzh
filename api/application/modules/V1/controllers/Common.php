<?php
/** 
 * 公共方法
 * @version v0.01
 * @author lqt
 * @time 2018-05-11
 */
use Custom\YDLib;
use Common\CommonBase;
use User\UserModel;
use Sms\SmsModel;
use Common\XDeode;
use User\UserSupplierThridModel;
use User\UserSupplierBindModel;
use Common\Crypt3Des;
use Pushmsg\PushmsgMassModel;
use Product\ProductModel;
use Services\Msg\MsgService;
class CommonController extends BaseController {
	const SMS_DO = [ 
			1,
			2,
			3,
			4,
			5,
			6 
	];
	const fileType = [ 
			1,
			2,
			3,
			4 
	];
	const CLASSIFY = array (
			1 => 'head',
			2 => 'tosu',
			3 => 'diandnag',
			4 => 'other' 
	);
	
	/* 验证码短信配置 */
	/* 短信时间间隔 （秒） */
	const SMS_INTERVAL = 60;
	/* 一天内短信条数 */
	const SMS_LIMIT = 10;
	/* 验证码长度 */
	const SMS_LENGTH = 6;
	/* 验证码有效期（秒） */
	const SMS_VALID = 300;

	public function demoAction()
    {
        $es = YDLib::getES('elasticsearch');
        //ProductModel::createIndex();
        //exit;
        $params = [
            'index' =>  'my_index',
            'type' => 'my_type',
            'body' => [
                'from' => '0',
                'size' => '16'
            ]

        ];

        //模糊匹配 单个查询
        $list = $es -> match("name","金")
            -> limit(0,10)
            -> index('my_index')
            -> type('my_product')
            -> query();


        //模糊匹配 “金” OR  “大” 查询
        $list = $es -> match("name","金 大")
            -> limit(0,10)
            -> index('my_index')
            -> type('my_product')
            -> query();

        // 模糊匹配“金” AND “大” 查询
        $list = $es -> match("name","金")
            -> match("name","大")
            -> limit(0,10)
            -> index('my_index')
            -> type('my_product')
            -> query();

        // 查询全部数据
        $list = $es -> matchAll()
            -> limit(0,10)
            -> index('my_index')
            -> type('my_product')
            -> query();

        //获取索引信息
        $list = $es->indexStats();

        //获取节点信息
        $list = $es->nodesStats();

        //获得集群信息
        $list = $es->clusterStats();


        //模糊匹配 查询金的类型并对 id 进行排序
        $list = $es -> match("title","北京")
            -> limit(0,10)
            -> sort("id" , "asc")  // desc asc
            -> index('weather')
            -> type('person')
            -> query();

        //完整匹配
        $list = $es -> match_phrase("category_name","金 条")
            -> limit(0,10)
            -> index('my_index')
            -> query();

        //多个字段模糊查询，查询name和category_name字段
        $list = $es -> multi_match("金条",['name','category_name'])
            -> limit(0,10)
            -> index('my_index')
            -> query();


        //语法查询 相当于 sql语句 select * from table where name in("金","条") or  category_name in("金","条")
        $list = $es -> query_string("金 OR 条",['name','category_name'])
            -> limit(0,10)
            -> index('my_index')
            -> query();

        //语法查询 相当于 sql语句 select * from table where (name = "金" and name = "条") or  (category_name  = "金" and category_name "条")
        $list = $es -> query_string("金 AND 条",['name','category_name'])
            -> limit(0,10)
            -> index('my_index')
            -> query();

        //语法查询
        $list = $es -> query_string("（ElasticSearch AND 大法） OR Python")
            -> limit(0,10)
            -> index('my_index')
            -> query();

        //查询 id >= 1
        $list = $es -> range("id",1)
            -> limit(0,10)
            -> index('weather')
            -> query();

        //查询 id 3 -  5 之间
        $list = $es -> range("id",3,5)
            -> limit(0,10)
            -> index('weather')
            -> query();

        //查询 id 4 -  5 之间
        $list = $es -> range("id",3,5,'gt')
            -> limit(0,10)
            -> index('weather')
            -> query();

        //查询 id 3 -  4 之间
        $list = $es -> range("id",3,5,'gte','lt')
            -> limit(0,10)
            -> index('weather')
            -> query();

        echo "<pre>";
        print_r($list);
    }

	/**
	 * 公共-获取手机验证码接口(登陆/注册/重置密码/其他)-发送单条短信
	 * <pre>
	 * 正式： http://api.qudiandang.com/v1/common/sendSms
	 * 测试： http://testapi.qudiandang.com/v1/common/sendSms
	 * </pre>
	 *
	 * <pre>
	 * POST参数
	 * mobile　：手机号 必填
	 * msg_code　：短信用途 必填 【登陆：1，注册：2，重置密码：3 挂失:4 快速登录：5 补全手机：6】
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *        
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": "0",
	 *         "errmsg": "请求成功"
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": "60002",
	 *         "errmsg": "用户已存在"
	 *         }
	 *         </pre>
	 */
	public function sendSmsAction() {
		$data ['mobile'] = $this->_request->get ( "mobile" );
		$data ['msg_code'] = $this->_request->get ( "msg_code" );
		
		if (empty ( $data ['mobile'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40001 );
		}
		
		if (YDLib::validMobile ( $data ['mobile'] )) {
			YDLib::output ( ErrnoStatus::STATUS_50001 );
		}
		
		if (empty ( $data ['msg_code'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40002 );
		}
		
		if (! in_array ( $data ['msg_code'], self::SMS_DO )) {
			YDLib::output ( ErrnoStatus::STATUS_50002 );
		}
		
		$info = UserModel::checkUserByMobile ( $data ['mobile'] );
		switch ($data ['msg_code']) {
			case 1 :
				if ((! is_array ( $info ) || count ( $info ) == 0) && (! is_array ( $info ['supplier'] ) || count ( $info ['supplier'] ) == 0)) {
					YDLib::output ( ErrnoStatus::STATUS_60001 );
				}
				
				if ($info ['is_del'] != CommonBase::STATUS_SUCCESS || $info ['supplier'] ['is_del'] != CommonBase::STATUS_SUCCESS) {
					YDLib::output ( ErrnoStatus::STATUS_60013 );
				}
				
				break;
			
			case 2 :
				if (is_array ( $info ) && count ( $info ) > 0 && is_array ( $info ['supplier'] ) && count ( $info ['supplier'] ) > 0) {
					if ($info ['supplier'] ['is_del'] == CommonBase::STATUS_SUCCESS) { // 用户未注销
						YDLib::output ( ErrnoStatus::STATUS_60002 );
					}
				}
				
				break;
			
			case 3 :
				if ((! is_array ( $info ) || count ( $info ) == 0) && (! is_array ( $info ['supplier'] ) || count ( $info ['supplier'] ) == 0)) {
					YDLib::output ( ErrnoStatus::STATUS_60001 );
				}
				if ($info ['is_del'] != CommonBase::STATUS_SUCCESS || $info ['supplier'] ['is_del'] != CommonBase::STATUS_SUCCESS || $info ['supplier'] ['status'] != CommonBase::STATUS_SUCCESS) { // 用户已注销
					YDLib::output ( ErrnoStatus::STATUS_60029 );
				}
				
				break;
			
			case 4 :
				if ((! is_array ( $info ) || count ( $info ) == 0) && (! is_array ( $info ['supplier'] ) || count ( $info ['supplier'] ) == 0)) {
					YDLib::output ( ErrnoStatus::STATUS_60001 );
				}
				
				if ($info ['is_del'] != CommonBase::STATUS_SUCCESS || $info ['supplier'] ['is_del'] != CommonBase::STATUS_SUCCESS || $info ['supplier'] ['status'] != CommonBase::STATUS_SUCCESS) { // 用户已注销
					YDLib::output ( ErrnoStatus::STATUS_60029 );
				}
				
				break;
			case 5 :
				break;
			case 6 :
				// 判断手机号是否绑定过
//				if ($info) {
//					if ($info ['is_del'] == CommonBase::STATUS_SUCCESS) { // 用户已存在
//					                                                     // 判断是否绑定过微信
//						$search = [ ];
//						$search ['user_id'] = $info ['id'];
//						$search ['type'] = CommonBase::USER_THRID_TYPE_1;
//						$bindInfo = UserSupplierBindModel::getInfo ( $search );
//						if ($bindInfo) {
//							YDLib::output ( ErrnoStatus::STATUS_40106 ); // 该手机号已绑定过微信
//						}
//					}
//				}
				
				break;
			default :
				YDLib::output ( ErrnoStatus::STATUS_50002 );
				break;
		}
		
		/* 60秒内只能发送一次短信 */
		$lastOne = SmsModel::getLastOne ( $data ['mobile'], $data ['msg_code'] );
		if (is_array ( $lastOne ) && count ( $lastOne ) > 0) {
			if (date ( 'Y-m-d H:i:s', time () - self::SMS_INTERVAL ) < $lastOne ['created_at']) {
				YDLib::output ( ErrnoStatus::STATUS_60005 );
			}
		}
		
		/* 一天内最多发送十条短信 */
		$todayNum = SmsModel::getTodayNum ( $data ['mobile'], $data ['msg_code'] );
		if ($todayNum >= self::SMS_LIMIT) {
			YDLib::output ( ErrnoStatus::STATUS_60006 );
		}
		
		$smsdata = [ ];
		$smsdata ['mobile'] = $data ['mobile'];
		if ($data ['msg_code'] == '1') {
			$smsdata ['model_id'] = '1';
		} else if ($data ['msg_code'] == '2') {
			$smsdata ['model_id'] = '2';
		} else if ($data ['msg_code'] == '3') {
			$smsdata ['model_id'] = '3';
		} else {
			$smsdata ['model_id'] = '4';
		}
		$code = rand ( '1000', '9999' );
		$params = array (
				$code,
				bcdiv ( self::SMS_VALID, 60 ) 
		);
		$smsdata ['params'] = $params;
		
		$smsdata ['sms_type'] = $data ['msg_code'];
		$smsdata ['code'] = $code;
		$smsdata ['valid'] = self::SMS_VALID;
		
		/* 发送短信 */
		SmsModel::SendSms ( $smsdata );
	}
	
	/**
	 * 公共-发送短信-发送单条短信
	 * <pre>
	 * 正式： http://api.qudiandang.com/v1/common/commonSms
	 * 测试： http://testapi.qudiandang.com/v1/common/commonSms
	 * </pre>
	 *
	 * <pre>
	 * POST参数
	 * mobile　：手机号 必填 //15811137696
	 * model_id：模板ID 必填 //1
	 * params　：参数 必填 //["123456","1"]
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *        
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": "0",
	 *         "errmsg": "请求成功"
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": "60002",
	 *         "errmsg": "用户已存在"
	 *         }
	 *         </pre>
	 */
	public function commonSmsAction() {
		$data = [ ];
		$data ['mobile'] = $this->_request->get ( "mobile" );
		$data ['model_id'] = $this->_request->get ( "model_id" );
		$data ['params'] = $this->_request->get ( "params" );
		
		if (empty ( $data ['mobile'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40001 );
		}
		
		if (YDLib::validMobile ( $data ['mobile'] )) {
			YDLib::output ( ErrnoStatus::STATUS_50001 );
		}
		
		if (empty ( $data ['model_id'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40107 );
		}
		
		if (empty ( $data ['params'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40108 );
		}
		$data ['params'] = json_decode ( $data ['params'], TRUE );
		if (! is_array ( $data ['params'] ) || count ( $data ['params'] ) == 0) {
			YDLib::output ( ErrnoStatus::STATUS_40108 );
		}
		/* 发送短信 */
		SmsModel::SendSms ( $data );
	}
	
	/**
	 * 公共-群发短信
	 * <pre>
	 * 正式： http://api.qudiandang.com/v1/common/commonSmsMass
	 * 测试： http://testapi.qudiandang.com/v1/common/commonSmsMass
	 * </pre>
	 *
	 * <pre>
	 * POST参数
	 * mobiles　：手机号数组 必填 //["15811137696","18513854271"]
	 * content　：短信内容 必填 //"【扎呵呵】欢迎加入扎呵呵！"
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *        
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": "0",
	 *         "errmsg": "请求成功"
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": "60002",
	 *         "errmsg": "用户已存在"
	 *         }
	 *         </pre>
	 */
	public function commonSmsMassAction() {
		$data = [ ];
		$data ['mobiles'] = $this->_request->get ( "mobiles" );
		$data ['content'] = $this->_request->get ( "content" );
		
		if (empty ( $data ['mobiles'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40001 );
		}
		
		$data ['mobiles'] = json_decode ( $data ['mobiles'], TRUE );
		if (! is_array ( $data ['mobiles'] ) || count ( $data ['mobiles'] ) == 0) {
			YDLib::output ( ErrnoStatus::STATUS_40001 );
		}		
		
		foreach ($data ['mobiles'] as $key => $value) {
			if (YDLib::validMobile ( $value )) {
				YDLib::output ( ErrnoStatus::STATUS_50001 );
			}			
		}

		if (empty ( $data ['content'] )) {
			YDLib::output ( ErrnoStatus::STATUS_40113 );
		}

		/* 群送短信 */
		SmsModel::SendSmsMass ( $data );
	}	
	
	/**
	 * 群发短信-群发id
	 * <pre>
	 * 正式： http://api.qudiandang.com/v1/common/commonSmsMassID
	 * 测试： http://testapi.qudiandang.com/v1/common/commonSmsMassID
	 * </pre>
	 *
	 * <pre>
	 * POST参数
	 * id　：群发id 必填 
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *        
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": "0",
	 *         "errmsg": "请求成功"
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": "60002",
	 *         "errmsg": "用户已存在"
	 *         }
	 *         </pre>
	 */
	public function commonSmsMassIDAction() {
		
		$id = $this->_request->get ( "id" );
		
		if (!isset($id) || empty($id) || !is_numeric($id) || $id <= 0)  {
			YDLib::output ( ErrnoStatus::STATUS_40114 );
		}
		
		$info = PushmsgMassModel::getInfoByID($id);
		if (!$info) {
			YDLib::output ( ErrnoStatus::STATUS_40115 );
		}
		
		if ($info['status'] != PushmsgMassModel::MASS_STATUS_2) {
			YDLib::output ( ErrnoStatus::STATUS_40116 );
		}
		
		if ($info['type'] == PushmsgMassModel::MASS_TYPE_2 && $info['send_time'] > date("Y-m-d H:i:s") ) {
			YDLib::output ( ErrnoStatus::STATUS_40117 );
		}

		/* 群送短信 */
		SmsModel::SendSmsMassID ( $info );
	}	
	
	/**
	 * 公共-获取省市县接口
	 * <pre>
	 * 正式： http://api.qudiandang.com/v1/Common/area
	 * 测试： http://testapi.qudiandang.com/v1/Common/area
	 * </pre>
	 *
	 * <pre>
	 * POST参数
	 * pid　：父级ID 非必填 空/0：获取所有省份
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
	 *         {
	 *         "area_id": 51892,
	 *         "parent_id": 1,
	 *         "area_name": "北京"
	 *         }
	 *         ]
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": "60002",
	 *         "errmsg": "用户已存在"
	 *         }
	 *         </pre>
	 */
	public function areaAction() {
		$pid = $this->getRequest ()->getPost ( "pid" );
		$pid = isset ( $pid ) && is_numeric ( $pid ) ? $pid : 0;
		$area = AreaModel::getChild ( $pid );
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, $area );
	}
	
	/**
	 * 公共-上传单张图片接口
	 * <pre>
	 * 正式： http://file.qudiandang.com/mobile_img.php
	 * 测试： http://testfile.qudiandang.com/mobile_img.php
	 * </pre>
	 *
	 * <pre>
	 * POST参数
	 * filetype　：类型 必填 【1:头像,2:投诉,3:在线售卖,4:其他】
	 * file　：文件 必填
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
	 *         {
	 *         "errno": 0,
	 *         "errmsg": "上传成功",
	 *         "data": {
	 *         "auth_url":"http://testfile.qudiandang.com/upload/diandnag/2018/06/04/3b55836d400aba5564e54331946fa48c_363.jpg",
	 *         "url":"/upload/diandnag/2018/06/04/3b55836d400aba5564e54331946fa48c_363.jpg"
	 *         }
	 *         }
	 *         ]
	 *         }
	 *        
	 *         失败：
	 *         {
	 *         "errno": "60002",
	 *         "errmsg": "用户已存在"
	 *         }
	 *         </pre>
	 */
	public function fileAction() {
		die ( "www" );
	}
	
	/**
	 * 数字加密接口
	 *
	 * <pre>
	 * POST参数
	 * code : 要加密的数字，必填
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Common/xencode
	 * 测试： http://testapi.qudiandang.com/v1/Common/xencode
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
	 *         "code": "lxNXSizkk"
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
	public function xencodeAction() {
		$user_id = $this-> user_id;
		if (empty ( $user_id )) {
			YDLib::output ( ErrnoStatus::STATUS_60574 );
		}
		
		$en = XDeode::encode ( $user_id );
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, [ 
				'code' => $en 
		] );
	}
	
	/**
	 * 数字解密接口
	 *
	 * <pre>
	 * POST参数
	 * code : 要加密的数字，必填
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Common/xdecode
	 * 测试： http://testapi.qudiandang.com/v1/Common/xdecode
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
	 *         "code": "11"
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
	public function xdecodeAction() {
		$code = $this->_request->get ( 'code' );
		if (empty ( $code )) {
			YDLib::output ( ErrnoStatus::STATUS_60574 );
		}
		
		$en = XDeode::decode ( $code );
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, [ 
				'code' => $en 
		] );
	}
	
	/**
	 * 3des加密接口
	 *
	 * <pre>
	 * POST参数
	 * code : 要加密的数字，必填
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Common/encrypt
	 * 测试： http://testapi.qudiandang.com/v1/Common/encrypt
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
	 *         "code": "lxNXSizkk"
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
	public function encryptAction() {
		$code = $this->_request->get ( 'code' );
		if (empty ( $code )) {
			YDLib::output ( ErrnoStatus::STATUS_60574 );
		}
		
		$en = Crypt3Des::encrypt ( $code );
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, [ 
				'code' => $en 
		] );
	}
	
	/**
	 * 3des解密接口
	 *
	 * <pre>
	 * POST参数
	 * code : 要解密的数字，必填
	 * </pre>
	 *
	 * <pre>
	 * 调用方式：
	 * 正式： http://api.qudiandang.com/v1/Common/decrypt
	 * 测试： http://testapi.qudiandang.com/v1/Common/decrypt
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
	 *         "code": "11"
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
	public function decryptAction() {
		$code = $this->_request->get ( 'code' );
		if (empty ( $code )) {
			YDLib::output ( ErrnoStatus::STATUS_60574 );
		}
		
		$en = Crypt3Des::decrypt ( $code );
		
		YDLib::output ( ErrnoStatus::STATUS_SUCCESS, [ 
				'code' => $en 
		] );
	}
	
	
	
	
	/**
	 * 公共-发送短信-消息设置发送消息
	 * <pre>
	 * 正式： http://api.qudiandang.com/v1/common/commonSms
	 * 测试： http://testapi.qudiandang.com/v1/common/commonSms
	 * </pre>
	 *
	 * <pre>
	 * POST参数
	 * remind_type :消息类型 必填
	 * mobile　           ：手机号 必填 //15811137696
	 * $user_id    ：用户ID 必填 //1
	 * $data　              ：短信匹配的内容数组一个params另一个weixin_params
	 * $data[
	 *      'params' => [
	 *          //短信匹配的内容
	 *      ],
	 *      'weixin_params'=>[
	 *          //微信模板内容
	 *      ]
	 *  ]
	 * 
	 * </pre>
	 *
	 * @return string 返回JSON数据格式
	 *
	 *         <pre>
	 *         成功：
	 *         {
	 *         "errno": "0",
	 *         "errmsg": "请求成功"
	 *         }
	 *
	 *         失败：
	 *         {
	 *         "errno": "60002",
	 *         "errmsg": "用户已存在"
	 *         }
	 *         </pre>
	 */
	public function commonWechatSmsAction() {
	    
	    $remind_type = $this->_request->get ( "remind_type" );
	    $mobile = $this->_request->get ( "mobile" );
	    $user_id = $this->_request->get ( "user_id" );
	    $data = $this->_request->get ( "data" );
	
	    if (empty ( $mobile )) {
	        YDLib::output ( ErrnoStatus::STATUS_40001 );
	    }
	    
	    if (empty ( $mobile )) {
	        YDLib::output ( ErrnoStatus::STATUS_40001 );
	    }
	
	    if (YDLib::validMobile ( $mobile )) {
	        YDLib::output ( ErrnoStatus::STATUS_50001 );
	    }
	
	    if (empty ( $user_id )) {
	        YDLib::output ( ErrnoStatus::STATUS_40015 );
	    }
	    if (empty ( $remind_type )) {
	        YDLib::output ( ErrnoStatus::STATUS_40107 );
	    }
	    
	    if (empty ( $data )) {
	        YDLib::output ( ErrnoStatus::STATUS_40108 );
	    }
	    $data = json_decode ( $data, TRUE );
	    if (! is_array ( $data ) || count ( $data ) == 0) {
	        YDLib::output ( ErrnoStatus::STATUS_40108 );
	    }
	    /* 发送短信 */
	    MsgService::fireMsg($remind_type, $mobile, $user_id,$data);
	}
	
	
	
	
	
}

<?php

/**
 * 公共基础类
 * 
 * @package modules
 * @subpackage Common
 * @author 邱胜奇 <shancci@sina.com>
 */

namespace Common;

use Custom\YDLib;
use Weixin\WexinMaterialModel;
class CommonBase
{
    /**
     * 表前缀
     *
     * @var string
     */
    public static $_tablePrefix = 'qdd_';
	
    // /**
     // * 表名
     // *
     // * @var string
     // */
    // protected $_tableName = NULL;	
	
	/** 状态码 */
	const STATUS_SUCCESS = 2;
	const STATUS_FAIL = 1;

	/** 是否显示 */
	const IS_SHOW_SUCCESS = 2;
	const IS_SHOW_FAIL = 1;	
	
	/** 删除码 */
	const DELETE_SUCCESS = 2;  
	const DELETE_FAIL = 1;	
	
	/** 到付待审核 */					   //前台状态
 	const  STATUS_AUDIT 			= '10';//待审核
	/** 到付待拣货 */
	const  STATUS_AUDITED   		= '11';//待收货
	/** 待付款 */
	const  STATUS_PENDING_PAYMENT 	= '20';//待付款 
	/** 待成团 */
	const  STATUS_ALREADY_PIN 		= '22';//待成团	 	
	/** 待审核 */
	const  STATUS_ALREADY_PAID 		= '21';//待发货
	/** 待拣货 */
	const  STATUS_PICKING_GOODS		= '30';//待发货	
	/** 待发货 */
	const  STATUS_BE_SHIPPED		= '40';//待发货
	/** 已发货 */
	const  STATUS_ALREADY_SHIPPED 	= '50';//待收货
	/** 交易成功 */
	const  STATUS_SUCCESSFUL_TRADE  = '60';//已完成
	/** 交易关闭 */
	const  STATUS_CLOSED		 	= '70';//已完成
	/** 用户取消 */
	const  STATUS_USER_CANCEL	  	= '80';//已取消
	/** 客服取消 */
	const  STATUS_CUSTOM_CANCEL 	= '90';//已取消
	
	/** 可审核 */							
	const ORDER_CAN_AUDIT = [10,21];
	/** 待成团 */							
	const ORDER_CAN_PIN = [22];				
	/** 可取消 */							
	const ORDER_CAN_CANCLE= [10,11,20];		
	/** 可修改 */							
	const ORDER_CAN_EDIT = [10,11,20,21,30];		
	/** 可拣货 */							
	const ORDER_CAN_AUDITED = [11,30];	
	/** 可发货 */							
	const ORDER_CAN_SHIPPED = [40];	
	/** 可售后 */							
	const ORDER_CAN_TRADE = [60];
	
	/** 销售额统计状态 */							
	const ORDER_SALE_STATUS = [21,30,40,50,60,70];
			
	const STATUS_VALUE = [
							//10=>'到付待审核',
							//11=>'到付待拣货',
							20=>'待付款',
							21=>'待审核',
							22=>'待成团',
							30=>'待拣货',
							40=>'待发货',
							50=>'已发货',
							60=>'交易成功',
							70=>'交易关闭',
							80=>'用户取消',
							90=>'客服取消'
							];
							
	/** 支付类型货到付款 */
	const  ORDER_PAY_TYPE_DELIVERY 		= 2;
	/** 支付类型在线 */
	const  ORDER_PAY_TYPE_ONLINE  		= 1;
	
	/** 快递 */
	const  DELIVERY_TYPE_EXP 			= 0;
	/** 门店自提 */
	const  DELIVERY_TYPE_SELF  			= 1;	
	
	/** 是否售后 */ 
	const  SERVICE_CUSTOMER			= 2;//已申请
	const  SERVICE_NONE				= 1;//未申请
	
	/** 是否评价 */ 
	const  COMMENT_CUSTOMER			= 2;//已评价
	const  COMMENT_NONE				= 1;//未评价	
	
	const ORDER_PAY_TYPE_VALUE = [1=>'在线支付',2=>'货到付款'];	
								
	const DELIVERY_TYPE_VALUE = [0=>'快递',1=>'门店自提'];	
								
	const AFTER_SALES_VALUE = [1=>'未申请',2=>'已申请'];	
		
	const COMMENT_VALUE = [1=>'未评价',2=>'已评价'];	
		
	const SEX_VALUE = [0=>'保密',1=>'男',2=>'女'];		
										   
	
	/** 待审核  */
	const ORDER_RETURN_STATUS_AUDIT 			= 10;
	
	/** 驳回(备注) */
	const ORDER_RETURN_STATUS_AUDIT_REJECT 		= 11;
	
	/** 用户取消售后 */
	const ORDER_RETURN_STATUS_AUDIT_REJECT_USER = 12;
	
	/** 售后发货(取件) */
	const ORDER_RETURN_STATUS_AUDIT_PICKUP 		= 20;
	
	/** 待收货 */
	const ORDER_RETURN_STATUS_RECEIPT_GOODS 	= 30;
	
	/** 待质检 */
	const ORDER_RETURN_STATUS_QUALITY_CONTROL 	= 31;
	
	/** 待入库  */
	const ORDER_RETURN_STATUS_STORAGE			= 32;
	
	/** 待退款 */
	const ORDER_RETURN_STATUS_RETURN_GOODS 		= 40;
	
	/** 售后完成 */
	const ORDER_RETURN_STATUS_SUCCESS			= 50;
	
	/** 退款驳回 */
	const ORDER_RETURN_STATUS_REFUND_REJECT 	= 60;
	
	/** 退货单 */
	const ORDER_RETURN_TYPE_BACK				= 1;
	/** 换货单 */
	const ORDER_RETURN_TYPE_CHANGE				= 2;
	
	/** 优惠券状态-待审核 */
	const COUPAN_STATUS_KEY_1 		= 1;
	
	/** 优惠券状态-进行中 */
	const COUPAN_STATUS_KEY_2		= 2;
	
	/** 优惠券状态-已失效 */
	const COUPAN_STATUS_KEY_3 		= 3;
			
	/** 优惠券状态-已过期 */
	const COUPAN_STATUS_KEY_4 		= 4;		
	
	/** 优惠券状态 */
	const COUPAN_STATUS_VALUE = [
							self::COUPAN_STATUS_KEY_1=>'待审核',
							self::COUPAN_STATUS_KEY_2=>'进行中',
							self::COUPAN_STATUS_KEY_3=>'已失效',
							self::COUPAN_STATUS_KEY_4=>'已过期'
							];	
	/** 优惠券类型-店铺优惠券 */
	const COUPAN_USE_TYPE_KEY_1 	= 1;
	
	/** 优惠券类型-商品优惠券 */
	const COUPAN_USE_TYPE_KEY_2		= 2;	
	
	/** 优惠券类型 */
	const COUPAN_STATUS_VALUE_VALUE = [
							self::COUPAN_USE_TYPE_KEY_1=>'店铺券',
							self::COUPAN_USE_TYPE_KEY_2=>'商品券'
							];	
    
	// {{{
	/**
	 * 时间: 10 分钟
	 * @var integer
	 */
	const TEN_MINITES = 600;
	/**
	 * 时间: 30 分钟
	 * @var integer
	 */
	const HALF_HOUR = 1800;
	/**
	 * 时间: 1 小时
	 * @var integer
	 */
	const ONE_HOUR = 3600;
	/**
	 * 时间: 半天
	 * @var integer
	 */
	const HALF_DAY = 43200;
	/**
	 * 时间: 1 天
	 * @var integer
	 */
	const ONE_DAY = 86400;
	/**
	 * 时间: 1 周
	 * @var integer
	 */
	const ONE_WEEK = 604800;
	// }}}

    // 会员三方类型
    const USER_THRID_TYPE_1 = '1'; // 微信公众号
    const USER_THRID_TYPE_2 = '2'; // 微信小程序

	/**
	 * 手机号码验证正则
	 * @var string
	 */
	const REGX_MOBILE = '/^(?:130|131|132|133|134|135|136|137|138|139|147|150|151|152|153|155|156|157|158|159|170|180|181|182|183|185|186|187|188|189)\d{8}$/';

	//暂时没用
	// /**
	 // * 数据库类型: 通行证读库
	 // * @var integer
	 // */
	// const DB_PASSPORT_R = 0x10;
	// /**
	 // * 数据库类型: 通行证写库
	 // * @var integer
	 // */
	// const DB_PASSPORT_W = 0x11;
	// /**
	 // * 数据库类型: 用户读库
	 // * @var integer
	 // */
	// const DB_USER_R = 0x20;
	// /**
	 // * 数据库类型: 用户写库
	 // * @var integer
	 // */
	// const DB_USER_W = 0x21;
	// /**
	 // * 数据库类型: 个人中心读库
	 // * @var integer
	 // */
	// const DB_CENTER_R = 0x30;
	// /**
	 // * 数据库类型: 个人中心写库
	 // * @var integer
	 // */
	// const DB_CENTER_W = 0x31;
	// /**
	 // * 数据库类型: 收藏读库
	 // * @var integer
	 // */
	// const DB_FAVORITE_R = 0x40;
	// /**
	 // * 数据库类型: 收藏写库
	 // * @var integer
	 // */
	// const DB_FAVORITE_W = 0x41;
	// /**
	 // * 数据库类型: 评论读库
	 // * @var integer
	 // */
	// const DB_COMMENT_R = 0x50;
	// /**
	 // * 数据库类型: 评论写库
	 // * @var integer
	 // */
	// const DB_COMMENT_W = 0x51;
	// /**
	 // * 数据库类型: 标签读库
	 // * @var integer
	 // */
	// const DB_TAG_R = 0x60;
	// /**
	 // * 数据库类型: 标签写库
	 // * @var integer
	 // */
	// const DB_TAG_W = 0x61;
	// /**
	 // * 数据库类型: APP读库
	 // * @var integer
	 // */
	// const DB_MOBILE_R = 0x70;
	// /**
	 // * 数据库类型: APP写库
	 // * @var integer
	 // */
	// const DB_MOBILE_W = 0x71;
	// /**
	 // * 数据库类型: 活动读库
	 // * @var integer
	 // */
	// const DB_ACT_R = 0x80;
	// /**
	 // * 数据库类型: 活动写库
	 // * @var integer
	 // */
	// const DB_ACT_W = 0x81;
	// /**
	 // * 数据库类型: 商城读库
	 // * @var integer
	 // */
	// const DB_MALL_R = 0x80;
	// /**
	 // * 数据库类型: 商城写库
	 // * @var integer
	 // */
	// const DB_MALL_W = 0x81;
// 	
	// protected static $_db_cfgs = [
		// self::DB_R => 'db_r',
		// self::DB_W => 'db_w',
		// self::DB_PASSPORT_R => 'db_passport_r',
		// self::DB_PASSPORT_W => 'db_passport_w',
		// self::DB_USER_R => 'db_user_r',
		// self::DB_USER_W => 'db_user_w',
		// self::DB_CENTER_R => 'db_center_r',
		// self::DB_CENTER_W => 'db_center_w',
		// self::DB_FAVORITE_R => 'db_favorite_r',
		// self::DB_FAVORITE_W => 'db_favorite_w',
		// self::DB_COMMENT_R => 'db_comment_r',
		// self::DB_COMMENT_W => 'db_comment_w',
		// self::DB_TAG_R => 'db_tag_r',
		// self::DB_TAG_W => 'db_tag_w',
		// self::DB_MOBILE_R => 'db_mobile_r',
		// self::DB_MOBILE_W => 'db_mobile_w',
		// self::DB_ACT_R => 'db_act_r',
		// self::DB_ACT_W => 'db_act_w',
		// self::DB_MALL_R => 'db_mall_r',
		// self::DB_MALL_W => 'db_mall_w',
	// ];

	const REDIS_USER = 0x10;

	protected static $_redis_cfgs = [
		self::REDIS_USER => 'redis_user',
	];
	// }}}
	
	/**
	 * 列表元素个数
	 * @var integer
	 */
	const LIST_SIZE = 10;
	
	/**
	 * 最大缓存总数
	 * @var integer
	 */
	const MAX_CACHE_SIZE = 1000;
	
	// {{{
	/**
	 * 位操作: 清除位
	 * @var integer
	 */
	const BIT_MODE_CLEAR = 1;
	/**
	 * 位操作: 设置位
	 * @var integer
	 */
	const BIT_MODE_SET = 2;
	
	protected static $_bit_modes = [
		self::BIT_MODE_CLEAR => 1,
		self::BIT_MODE_SET => 2,
	];
	// }}}
	
	// {{{
	/**
	 * 图片: 无(不计算图片地址)
	 * @var integer
	 */
	const COVER_NONE = 0;
	/**
	 * 图片: 小图
	 * @var integer
	 */
	const COVER_SMALL = 1;
	/**
	 * 图片: 中图
	 * @var integer
	 */
	const COVER_MIDDLE = 2;
	/**
	 * 图片: 大图
	 * @var integer
	 */
	const COVER_LARGE = 3;
	/**
	 * 图片: 原图
	 * @var integer
	 */
	const COVER_ORIGIN = 4;
	// }}}
	
	protected static $_cover_sizes = [
		self::COVER_SMALL => 'small',
		self::COVER_MIDDLE => 'middle',
		self::COVER_LARGE => 'big',
		self::COVER_ORIGIN => 'source',
	];
	
	// {{{
	/**
	 * 头像尺寸: 无
	 * @var integer
	*/
	const AVATAR_NONE = 0;
	/**
	 * 头像尺寸: 小
	 * @var integer
	 */
	const AVATAR_SMALL = 22;
	/**
	 * 头像尺寸: 中
	 * @var integer
	 */
	const AVATAR_MIDDLE = 48;
	/**
	 * 头像尺寸: 大
	 * @var integer
	 */
	const AVATAR_LARGE = 70;
	/**
	 * 头像尺寸: 超大
	 * @var integer
	 */
	const AVATAR_XLARGE = 185;
	// }}}
	
	protected static $_avatar_sizes = [
		//self::AVATAR_NONE => 0,
		self::AVATAR_SMALL => 22,
		self::AVATAR_MIDDLE => 48,
		self::AVATAR_LARGE => 70,
		self::AVATAR_XLARGE => 185,
	];
	// }}}
	
	// {{{
	/**
	 * 状态: 所有
	 * @var integer
	 */
	const STATUS_ALL = 0;
	/**
	 * 状态: 正常
	 * @var integer
	 */
	const STATUS_APPROVED = 1;
	/**
	 * 状态: 未审核
	 * @var integer
	 */
	const STATUS_PENDING = 2;
	// }}}
	
	// {{{
	/**
	 * 数据类型: 只取ID
	 * @var integer
	*/
	const FETCH_ID = 1;
	/**
	 * 数据类型: 取表中完整数据
	 * @var integer
	 */
	const FETCH_DATA_FIELD = 2;
	/**
	 * 数据类型: 取用户昵称
	 * @var integer
	 */
	const FETCH_USER_NICK = 4;
	/**
	 * 数据类型: 取用户信息
	 * @var integer
	 */
	const FETCH_USER_DATA = 8;
	/**
	 * 数据类型: 取城市信息
	 * @var integer
	 */
	const FETCH_CITY_INFO = 16;
	/**
	 * 数据类型: 取额外详细数据
	 * @var integer
	 */
	const FETCH_EXTRA_DATA = 32;
	/**
	 * 数据类型: 取关联数据
	 * @var integer
	 */
	const FETCH_EXTENDS = 64;
	/**
	 * 数据类型: 获取所有数据
	 * @var integer
	 */
	const FETCH_ALL = 128;
	/**
	 * 数据类型: 随机获取数据
	 * @var integer
	 */
	const FETCH_RANDOM = 256;
	/**
	 * 数据类型: 按其他逻辑取数据(不同方法有不同实现)
	 * @var integer
	 */
	const FETCH_CUSTOM = 512;
	// }}}
	
	// {{{
	/**
	 * 排序方式: 升序
	 * @var integer
	 */
	const SORT_ORDER_ASC = 0;
	/**
	 * 排序方式: 降序
	 * @var integer
	 */
	const SORT_ORDER_DESC = 1;
	
	protected static $_sort_orders = [
		self::SORT_ORDER_ASC => 'ASC',
		self::SORT_ORDER_DESC => 'DESC',
	];
	// }}}
	
	// {{{
	/**
	 * 坐标: 纬度
	 * @var string
	 */
	const COORD_LATITUDE = 'Lat';
	/**
	 * 坐标: 经度
	 * @var string
	 */
	const COORD_LONGITUDE = 'Lng';
	// }}}
	
	// {{{
	/**
	 * 额外参数: 项目状态
	 * @var string
	 */
	const EXTRA_STATUS = 'status';
	/**
	 * 额外参数: 当前登录用户的ID
	 * @var string
	 */
	const EXTRA_LOGIN_UID = 'uid';
	/**
	 * 额外参数: 是否计算用户头像
	 * @var string
	 */
	const EXTRA_AVATAR = 'avatar';
	/**
	 * 额外参数: 用户数据
	 * @var string
	 */
	const EXTRA_USER_DATA = 'user_data';
	/**
	 * 额外参数: 用户关系
	 * @var string
	 */
	const EXTRA_USER_RELATION = 'user_relation';
	/**
	 * 额外参数: 是否计算餐馆图片URL
	 * @var string
	 */
	const EXTRA_COVER = 'cover';
	/**
	 * 额外参数: 数据的类型: 只取ID|取用户昵称|取关联数据|取额外数据|..
	 * @var string
	 */
	const EXTRA_FETCH = 'fetch';
	/**
	 * 额外参数: 是否强制刷新缓存
	 * @var string
	 */
	const EXTRA_REFRESH = 'refresh';
	/**
	 * 额外参数: 筛选包含过滤字段
	 * @var string
	 */
	const EXTRA_FILTER_INCLUDE = 'filter_include';
	/**
	 * 额外参数: 筛选排除过滤字段
	 * @var string
	 */
	const EXTRA_FILTER_EXCLUDE = 'filter_exclude';
	/**
	 * 额外参数: 包含范围限制
	 * @var string
	 */
	const EXTRA_RANGE_INCLUDE = 'range_include';
	/**
	 * 额外参数: 排除范围限制
	 * @var string
	 */
	const EXTRA_RANGE_EXCLUDE = 'range_exclude';
	/**
	 * 额外参数: 某个参数的类别(不同方法有不同实现)
	 * @var string
	 */
	const EXTRA_TYPE = 'type';
	/**
	 * 额外参数: 要获取的字段
	 * @var string
	 */
	const EXTRA_FIELDS = 'fields';
	/**
	 * 额外参数: 是否从写库取数据
	 * @var string
	 */
	const EXTRA_FROM_W = 'from_w';
	/**
	 * 额外参数: 是否使用缓存
	 * @var string
	 */
	const EXTRA_USE_CACHE = 'use_cache';
	/**
	 * 额外参数: 返回结果集偏移(负数表不限制,取所有结果)
	 * @var string
	 */
	const EXTRA_OFFSET = 'offset';
	/**
	 * 额外参数: 返回结果集的个数(负数表示不限制,取自 EXTRA_OFFSET 开始的所有结果;当 EXTRA_OFFSET 为负数时, EXTRA_LIMIT 无意义)
	 * @var string
	 */
	const EXTRA_LIMIT = 'limit';
	/**
	 * 额外参数: 排序字段
	 * @var string
	 */
	const EXTRA_SORT_FIELD = 'sort_field';
	/**
	 * 额外参数: 地理坐标
	 * @var string
	 */
	const EXTRA_COORDINATE = 'coord';
	/**
	 * 额外参数: 时间点
	 * @var string
	 */
	const EXTRA_DATE_TIME = 'date_time';
	/**
	 * 额外参数: 开始时间点
	 * @var string
	 */
	const EXTRA_TIME_START = 'time_start';
	/**
	 * 额外参数: 结束时间点
	 * @var string
	 */
	const EXTRA_TIME_END = 'time_end';
	/**
	 * 额外参数: 性别
	 * @var string
	 */
	const EXTRA_GENDER = 'gender';
	/**
	 * 额外参数: 城市
	 * @var string
	 */
	const EXTRA_CITY = 'city';
	/**
	 * 额外参数: 区域
	 * @var string
	 */
	const EXTRA_AREA = 'area';
	/**
	 * 额外参数: 同步数据
	 * @var string
	 */
	const EXTRA_SYNC = 'sync';
	/**
	 * 额外参数: 标识
	 * @var string
	 */
	const EXTRA_FLAG = 'flag';
	/**
	 * 额外参数: 调试开关
	 * @var string
	 */
	const EXTRA_DEBUG = 'debug';
	// }}}
	
	protected static $_extra_meta = [
		self::EXTRA_LOGIN_UID => [
			'filter' => FILTER_VALIDATE_INT,
			'default' => 0,
		],
		self::EXTRA_AVATAR => [
			'filter' => FILTER_VALIDATE_REGEXP,
			'options' => [
				'regexp' => '/^(?:0|22|48|70|185)$/',
			],
			'default' => 48,
		],
		self::EXTRA_FETCH => [
			'filter' => FILTER_VALIDATE_INT,
			'options' => [
				'min_range' => self::FETCH_ID,
			],
			'default' => self::FETCH_DATA_FIELD,
		],
		self::EXTRA_FROM_W => [
			'filter' => FILTER_VALIDATE_BOOLEAN,
			'default' => false,
		],
		self::EXTRA_REFRESH => [
			'filter' => FILTER_VALIDATE_BOOLEAN,
			'default' => false,
		],
		self::EXTRA_OFFSET => [
			'filter' => FILTER_VALIDATE_INT,
			'default' => 0,
		],
		self::EXTRA_LIMIT => [
			'filter' => FILTER_VALIDATE_INT,
			'default' => self::LIST_SIZE,
		],
	];
	
	// {{{
	/**
	 * 返回值: 操作成功
	 * @var integer
	*/
	const RET_CODE_SUCC = 0x00;
	/**
	 * 返回值: 操作失败
	 * @var integer
	 */
	const RET_CODE_FAIL = 0x01;
	/**
	 * 返回值: 参数错误
	 * @var integer
	 */
	const RET_CODE_WRONG_ARGS = 0x10;
	/**
	 * 返回值: 缺少参数
	 * @var integer
	 */
	const RET_CODE_LACK_ARGS = 0x11;
	/**
	 * 返回值: 未提供合格数据
	 * @var integer
	 */
	const RET_CODE_NO_VALID_DATA = 0x12;
	/**
	 * 返回值: 无可供更新数据
	 * @var integer
	 */
	const RET_CODE_NO_NEW_DATA = 0x13;
	/**
	 * 返回值: 数据未更改
	 * @var integer
	 */
	const RET_CODE_NOT_MODIFIED = 0x14;
	/**
	 * 返回值: 对象不存在
	 * @var integer
	 */
	const RET_CODE_NOT_EXISTS = 0x20;
	/**
	 * 返回值: 禁止操作
	 * @var integer
	 */
	const RET_CODE_FORBIDDEN = 0x21;
	/**
	 * 返回值: 唯一键冲突
	 * @var integer
	 */
	const RET_CODE_UNIQUE_CONFLICT = 0x22;
	/**
	 * 返回值: 数目抵达上限
	 * @var integer
	 */
	const RET_CODE_LIMITED = 0x30;
	/**
	 * 返回值: 系统错误
	 * @var integer
	 */
	const RET_CODE_SYS_ERROR = 0x31;
	/**
	 * 返回值: 数据库错误
	 * @var integer
	 */
	const RET_CODE_DB_ERROR = 0x32;
	/**
	 * 返回值: 文件上传失败
	 * @var integer
	 */
	const RET_CODE_UPLOAD_ERROR = 0x33;
	/**
	 * 返回值: 文件大小错误
	 * @var integer
	 */
	const RET_CODE_FILESIZE_ERROR = 0x34;
	/**
	 * 返回值: 文件损坏
	 * @var integer
	 */
	const RET_CODE_FILE_CORRUPTED = 0x35;
	/**
	 * 返回值: 图片裁剪失败
	 * @var integer
	 */
	const RET_CODE_CROP_ERROR = 0x36;
	/**
	 * 返回值: 未知错误
	 * @var integer
	 */
	const RET_CODE_UNKNOWN_ERROR = 0x40;
	// }}}

	protected static $_ret_map = [
		self::RET_CODE_SUCC => [
			'code' => self::RET_CODE_SUCC,
			'msg' => '操作成功',
		],
		self::RET_CODE_FAIL => [
			'code' => self::RET_CODE_FAIL,
			'msg' => '操作失败',
		],
		self::RET_CODE_WRONG_ARGS => [
			'code' => self::RET_CODE_WRONG_ARGS,
			'msg' => '参数错误',
		],
		self::RET_CODE_LACK_ARGS => [
			'code' => self::RET_CODE_LACK_ARGS,
			'msg' => '缺少参数',
		],
		self::RET_CODE_NO_VALID_DATA => [
			'code' => self::RET_CODE_NO_VALID_DATA,
			'msg' => '未提供合格数据',
		],
		self::RET_CODE_NO_NEW_DATA => [
			'code' => self::RET_CODE_NO_NEW_DATA,
			'msg' => '无可供更新数据',
		],
		self::RET_CODE_NOT_MODIFIED => [
			'code' => self::RET_CODE_NOT_MODIFIED,
			'msg' => '数据未更改',
		],
		self::RET_CODE_NOT_EXISTS => [
			'code' => self::RET_CODE_NOT_EXISTS,
			'msg' => '对象不存在',
		],
		self::RET_CODE_UNIQUE_CONFLICT => [
			'code' => self::RET_CODE_UNIQUE_CONFLICT,
			'msg' => '唯一索引冲突',
		],
		self::RET_CODE_LIMITED => [
			'code' => self::RET_CODE_LIMITED,
			'msg' => '数目抵达上限',
		],
		self::RET_CODE_SYS_ERROR => [
			'code' => self::RET_CODE_SYS_ERROR,
			'msg' => '系统错误',
		],
		self::RET_CODE_DB_ERROR => [
			'code' => self::RET_CODE_DB_ERROR,
			'msg' => '数据库错误',
		],
		self::RET_CODE_UPLOAD_ERROR => [
			'code' => self::RET_CODE_UPLOAD_ERROR,
			'msg' => '文件上传失败',
		],
		self::RET_CODE_FILESIZE_ERROR => [
			'code' => self::RET_CODE_FILESIZE_ERROR,
			'msg' => '文件大小错误',
		],
		self::RET_CODE_FILE_CORRUPTED => [
			'code' => self::RET_CODE_FILE_CORRUPTED,
			'msg' => '文件损坏',
		],
		self::RET_CODE_CROP_ERROR => [
			'code' => self::RET_CODE_CROP_ERROR,
			'msg' => '图片裁剪失败',
		],
		self::RET_CODE_UNKNOWN_ERROR => [
			'code' => self::RET_CODE_UNKNOWN_ERROR,
			'msg' => '未知错误',
		],
	];
	
	//emoji表情包名字
	public static $emoji_name = [
	    '01' => '微笑',
	    '02' => '撇嘴',
	    '03' => '色',
	    '04' => '发呆',
	    '05' => '得意',
	    '06' => '流泪',
	    '07' => '害羞',
	    '08' => '闭嘴',
	    '09' => '睡',
	    '10' => '大哭',
	    '11' => '尴尬',
	    '12' => '发怒',
	    '13' => '调皮',
	    '14' => '呲牙',
	    '15' => '惊讶',
	    '16' => '难过',
	    '17' => '酷',
	    '18' => '冷汗',
	    '19' => '抓狂',
	    '20' => '吐',
	    '21' => '偷笑',
	    '22' => '愉快',
	    '23' => '白眼',
	    '24' => '傲慢',
	    '25' => '饥饿',
	    '26' => '困',
	    '27' => '惊恐',
	    '28' => '流汗',
	    '29' => '憨笑',
	    '30' => '悠闲',
	    '31' => '奋斗',
	    '32' => '咒骂',
	    '33' => '疑问',
	    '34' => '嘘',
	    '35' => '晕',
	    '36' => '疯了',
	    '37' => '衰',
	    '38' => '骷髅',
	    '39' => '敲打',
	    '40' => '再见',
	    '41' => '擦汗',
	    '42' => '抠鼻',
	    '43' => '鼓掌',
	    '44' => '糗大了',
	    '45' => '坏笑',
	    '46' => '左哼哼',
	    '47' => '右哼哼',
	    '48' => '哈欠',
	    '49' => '鄙视',
	    '50' => '委屈',
	    '51' => '快哭了',
	    '52' => '阴险',
	    '53' => '亲亲',
	    '54' => '吓',
	    '55' => '可怜',
	    '56' => '菜刀',
	    '57' => '西瓜',
	    '58' => '啤酒',
	    '59' => '篮球',
	    '60' => '乒乓',
	    '61' => '咖啡',
	    '62' => '饭',
	    '63' => '猪头',
	    '64' => '玫瑰',
	    '65' => '凋谢',
	    '66' => '嘴唇',
	    '67' => '爱心',
	    '68' => '心碎',
	    '69' => '蛋糕',
	    '70' => '闪电',
	    '71' => '炸弹',
	    '72' => '刀',
	    '73' => '足球',
	    '74' => '瓢虫',
	    '75' => '便便',
	    '76' => '月亮',
	    '77' => '太阳',
	    '78' => '礼物',
	    '79' => '拥抱',
	    '80' => '强',
	    '81' => '弱',
	    '82' => '握手',
	    '83' => '胜利',
	    '84' => '抱拳',
	    '85' => '勾引',
	    '86' => '拳头',
	    '87' => '差劲',
	    '88' => '爱你',
	    '89' => 'NO',
	    '90' => 'OK',
	    '91' => '爱情',
	    '92' => '飞吻',
	    '93' => '跳跳',
	    '94' => '发抖',
	    '95' => '怄火',
	    '96' => '转圈',
	    '97' => '磕头',
	    '98' => '回头',
	    '99' => '跳绳',
	    '100' => '投降',
	    '101' => '激动',
	    '102' => '乱舞',
	    '103' => '献吻',
	    '104' => '左太极',
	    '105' => '右太极'
	];
	
	
	//emoji表情匹配
	public static function getemojiname($content) {
	    $emoji_code = self::$emoji_name;
	    foreach ($emoji_code as $key=>$val) {
	        $content = str_replace('[', '', $content);
	        $content = str_replace(']', '', $content);
	        $content = str_replace($val, '<img src="'.HOST_STATIC.'common/images/emoji/'.$key.'.png">', $content);
	    }
	    return $content;
	}
	
	
	//emoji表情包代码
	public static $emoji_code = [
	    '01' => '/::)',
	    '02' => '/::~',
	    '03' => '/::B',
	    '04' => '/::|',
	    '05' => '/:8-)',
	    '06' => '/::<',
	    '07' => '/::$',
	    '08' => '/::X',
	    '09' => '/::Z',
	    '10' => "/::'(",
	    '11' => '/::-|',
	    '12' => '/::@',
	    '13' => '/::P',
	    '14' => '/::D',
	    '15' => '/::O',
	    '16' => '/::(',
	    '17' => '/::+',
	    '18' => '/:--b',
	    '19' => '/::Q',
	    '20' => '/::T',
	    '21' => '/:,@P',
	    '22' => '/:,@-D',
	    '23' => '/::d',
	    '24' => '/:,@o',
	    '25' => '/::g',
	    '26' => '/:|-)',
	    '27' => '/::!',
	    '28' => '/::L',
	    '29' => '/::>',
	    '30' => '/::,@',
	    '31' => '/:,@f',
	    '32' => '/::-S',
	    '33' => '/:?',
	    '34' => '/:,@x',
	    '35' => '/:,@@',
	    '36' => '/::8',
	    '37' => '/:,@!',
	    '38' => '/:!!!',
	    '39' => '/:xx',
	    '40' => '/:bye',
	    '41' => '/:wipe',
	    '42' => '/:dig',
	    '43' => '/:handclap',
	    '44' => '/:&-(',
	    '45' => '/:B-)',
	    '46' => '/:<@',
	    '47' => '/:@>',
	    '48' => '/::-O',
	    '49' => '/:>-|',
	    '50' => '/:P-(',
	    '51' => "/::'|",
	    '52' => '/:X-)',
	    '53' => '/::*',
	    '54' => '/:@x',
	    '55' => '/:8*',
	    '56' => '/:pd',
	    '57' => '/:<W>',
	    '58' => '/:beer',
	    '59' => '/:basketb',
	    '60' => '/:oo',
	    '61' => '/:coffee',
	    '62' => '/:eat',
	    '63' => '/:pig',
	    '64' => '/:rose',
	    '65' => '/:fade',
	    '66' => '/:showlove',
	    '67' => '/:heart',
	    '68' => '/:break',
	    '69' => '/:cake',
	    '70' => '/:li',
	    '71' => '/:bome',
	    '72' => '/:kn',
	    '73' => '/:footb',
	    '74' => '/:ladybug',
	    '75' => '/:shit',
	    '76' => '/:moon',
	    '77' => '/:sun',
	    '78' => '/:gift',
	    '79' => '/:hug',
	    '80' => '/:strong',
	    '81' => '/:weak',
	    '82' => '/:share',
	    '83' => '/:v',
	    '84' => '/:@)',
	    '85' => '/:jj',
	    '86' => '/:@@',
	    '87' => '/:bad',
	    '88' => '/:lvu',
	    '89' => '/:no',
	    '90' => '/:ok',
	    '91' => '/:love',
	    '92' => '/:<L>',
	    '93' => '/:jump',
	    '94' => '/:shake',
	    '95' => '/:<O>',
	    '96' => '/:circle',
	    '97' => '/:kotow',
	    '98' => '/:turn',
	    '99' => '/:skip',
	    '100' => '/:oY',
	    '101' => '/:#-0',
	    '102' => '/:hiphot',
	    '103' => '/:kiss',
	    '104' => '/:<&amp;',
	    '105' => '/:&amp;>'
	];
	
	
	//emoji表情匹配
	public static function getemojicode($content) {
	    $emoji_code = self::$emoji_code;
	    foreach ($emoji_code as $key=>$val) {
	        $content = str_replace('[', '', $content);
	        $content = str_replace(']', '', $content);
	        $content = str_replace($val, '<img src="'.HOST_STATIC.'common/images/emoji/'.$key.'.png">', $content);
	    }
	    return $content;
	}
	
	
	/**
	 * 较验额外参数
	 *
	 * @param array $extra		参数列表
	 * @param array $metas		较验模板,若为空则使用默认的
	 * @return array
	*/
	protected static function _parseExtras($extra, $metas = null)
	{
		$metas = empty($metas) ? static::$_extra_meta : $metas;
		$args = filter_var_array((array) $extra, $metas);
		
		foreach ($metas as $fkey => $fval)
		{
			$val = isset($args[$fkey]) ? $args[$fkey] : null;
			if ($val === null || ($val === false && $fval['filter'] !== FILTER_VALIDATE_BOOLEAN))
			{
				$args[$fkey] = $fval['default'];
			}
		}
		return $args;
	}
	
	/**
	 * 数据库连接工厂
	 *
	 * @param integer $type		数据库索引
	 * @return object
	 */
	protected static function _pdo($type)
	{
		static $db = [];
		if (!isset($db[$type]))
		{
			//$cfgs = static::$_db_cfgs;
			//$key = isset($cfgs[$type]) ? $cfgs[$type] : $cfgs[self::DB_USER_R];
			$key = isset($type)?$type:'db_r';
			$db[$type] = YDLib::getPDO($key);
		}
		return $db[$type];
	}
	
	/**
	 * MC连接工厂
	 *
	 * @return object
	 */
	protected static function _mc($key = 'memcache_mobile')
	{
		static $mc = [];
		if (!isset($mc[$key]))
		{
			$mc[$key] = YDLib::getMem($key);
		}
		return $mc[$key];
	}
	
	/**
	 * Redis连接工厂
	 *
	 * @return object
	 */
	protected static function _redis($db_key, $db_idx = -1)
	{
		static $redis = [];
		
		$uniq = "{$db_key}:{$db_idx}";
		
		if (!isset($redis[$uniq]))
		{
			$prop = ($db_key & self::DB_MASK_WRITE) ? 'w' : 'r';
			$db_base = static::$_redis_cfgs[$db_key & self::DB_MASK];
			$db_index = $db_idx < 0 ? $db_idx : $db_idx;
			$redis[$uniq] = YDLib::getRedis($db_base, $prop);
			//$redis[$uniq] = $app->cache('redis', $app->cfg[$db_base][$prop], $db_index);
		}
		
		return $redis[$uniq];
	}

	/**
	 * 处理计数字段更新
	 *
	 * @param array $origin     原始数据
	 * @param mixed $info       要更新的数据
	 * @param array $fields     计数字段schema
	 * @return array
	 */
	protected static function _parseCount($origin, $info, $fields)
	{
		$data = [];

		if (is_array($origin) && is_array($info) && is_array($fields))
		{
			$info = array_intersect_key($info, $fields);

			foreach ($info as $type => $seed)
			{
				if (isset($fields[$type]) && is_numeric($seed))
				{
					$field = $fields[$type];
					$seed_int = (int) $seed;

					if ($seed_int !== 0 && isset($origin[$field]))
					{
						$seed_str = "{$seed}";
						$sign = $seed_str[0];

						// 在原值基础上进行加减
						if ($sign === '+' || $sign === '-')
						{
							$expr = $seed_int > 0 ? "`{$field}`+{$seed_int}" : "GREATEST(`{$field}`{$seed_int}, 0)";
						}
						else
						{
							$expr = $seed_int;
						}

						$data[$field] = "`{$field}` = {$expr}";
					}
				}
			}
		}

		return $data;
	}

	/**
	 * 净化字符串
	 *
	 * @param string $str			目标字符串
	 * @param integer $len			截取指定长度,为-1时表不截取
	 * @param boolean $strip_tags	是否剔除HTML标签,默认不剔除
	 * @return string
	 */
	protected static function _sanitizeString($str, $len = -1, $strip_tags = false, $quote = true)
	{
		$pured_str = iconv(mb_detect_encoding($str), 'UTF-8', $str);
		$strip_tags && $pured_str = trim(strip_tags($pured_str));
		$len > 0 && $pured_str = trim(mb_substr($pured_str, 0, $len, 'utf8'));
		$quote && $pured_str = mysql_quote($pured_str);

		if (empty($pured_str))
		{
			$trace = debug_backtrace();
			foreach ($trace as &$t)
			{
				if (isset($t['object']))
				{
					unset($t['object']);
				}
			}
			runtime_log('CommonBase/_sanitizeString', serialize(['$str' => $str, '$pured_str' => $pured_str, '$trace' => $trace]));
		}

		return $pured_str;
	}
	
	
	/**
	 * 大小写转换方法
	 *
	 * @param string $big	数字或大小写字母
	 * @return string
	 */
	public static function getBigTosmall($big)
	{
	    $num = array('1','2','3','4','5','6','7','8','9');
	    $code = '';
	    foreach (str_split($big) as $key=>$val) {
	        if (in_array($val, $num)) {
	            $code .= $val;
	        } else {
	            $code .= strtolower($val);
	        }
	    }
	
	    return $code;
	}
	
	
	//比对图片信息
	public static function contrastImg($content,$is_con)
	{
	    $all = WexinMaterialModel::getAllByWhere(['is_con'=>$is_con]);
	    if ($all) {
	        foreach ($all as $key=>$val) {
	            $content = str_replace($val['location'],$val['url'] , $content);
	        }
	    }
	    return $content;
	}
	
	
	
	function validateURL($URL) {
	    $pattern_1 = "/^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se)$)(:(\d+))?\/?/i";
	    $pattern_2 = "/^(www)((\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se)$)(:(\d+))?\/?/i";
	    if(preg_match($pattern_1, $URL) || preg_match($pattern_2, $URL)){
	        return true;
	    } else{
	        return false;
	    }
	}

	public static function sexToNum($string)
    {
        $num = 0;
	    foreach (self::SEX_VALUE as $key => $value) {
	        if ($value == $string) {
                $num = $key;
            }
        }
        return $num;
    }
	
}	

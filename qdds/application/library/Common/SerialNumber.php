<?php
/** 
 * 用户计算所有编号序列号操作类
 * 
 * SKU = 3位分类(主键取三位) + 6位商品id + 3位自增  =12位 SKU数累加
 * C端
 *	订单(子单母单)  主单 6位日期 + 01标识  + 10自增 = 18位
 *            子单 6位日期 + 02标识  + 10自增 = 18位
 *	售后单(退换货单) 
 *			   退单 6位日期  + 03标识  + 10自增 = 18位
 *			   换单 6位日期  + 04标识  + 10自增 = 18位
 *	支付单         6位日期 + 05 标识  + 10自增 = 18位
 * ERP
 * 分类编号       'CY'  + 10自增 = 12位
 * 商品编号       'PT' + 10自增 = 12位
 * 供应商编号  'SP'  + 10自增 = 12位
 * 采购单            'PO'  + 10自增 = 12位
 * 合同编号       'CN'  + 10自增 = 12位
 *
 *WMS
 *拣货单         6位日期  + 06标识  + 10自增 = 18位
 *发货单         6位日期  + 07标识  + 10自增 = 18位
 *盘点单         PD标识  + 6位日期 + 10自增 = 18位
 *损益单        SY标识  + 6位日期  + 10自增 = 18位
 *
 *  TPL
 *物流公司  		   	公司标识
 *对账编号    		 	公司标识  + 6位日期 + 10自增 = 长度任意
 * 
 * $sn =  new SerialNumber();
 * $nostr = $sn->createSN(SerialNumber::SN_ORDER_MAIN);
 * 
 * @version v1.0
 * @package admin\service\SerialNumber
 * @author zhaoyu <zhaoyu@houhouyun.com>
 * @time  2016-11-25
 */
namespace Common;

use SerialNumber\SerialNumberModel;

class SerialNumber
{
	/** 主订单 */
	const SN_ORDER_MAIN						= '10';
	/** 子订单 */
	const SN_ORDER_CHILD				 	= '20';
	/** 换货单*/
	const SN_ORDER_CHANGE					= '30';
	/** 退货单*/
	const SN_ORDER_RETURN				 	= '40';
	/** 拣货 */
	const SN_ORDER_PICK						= '60';
	/** 发货*/
	const SN_ORDER_DELIVERY					= '70';

	/** 
	 * 盘点单
	 * @todo 修改2016-12-20  
	 */ 
	const SN_ORDER_INVENTORY				= 'PD';
	
	/** 
	 * 损益单
	 * @todo 修改2016-12-20
	 */
	const SN_ORDER_LOSSES					= 'SY';
	
	/** 返厂订单 */
	const SN_ORDER_RETURN_FACTORY			= 'FC';
	
	/** 品牌 */
	const SN_BRAND							= 'BD';
	/** 其他出入库*/
	const SN_OTHER_STOCK					= 'OS';
	
	/** 物流对账单号 */
	const SN_EXPRESS						= '10';
	/** COD对账单号 */
	const SN_COD_ACCOUNT					= '20';
	
	/** 经销单号*/
	const SN_SUPPLIER_JX					= 'JX';
	/** 代销单号*/
	const SN_SUPPLIER_DX					= 'DX';
	
	
	/** 支出单号*/
	const SN_ZC_NUMBER						= 'ZC';
	/** 收入单号 */
	const SN_SR_NUMBER						= 'SR';
	
	/** 供应商  */
	const SN_ERP_SP	 			= 'SP';  
	/** 分类编号  */
	const SN_ERP_CY	 			= 'CY';
	/** 商品编号  */
	const SN_ERP_PT	 			= 'PT';
	/** 采购单  */
	const SN_ERP_PO	 			= 'PO';
    /** 采购子单  */
	const SN_ERP_PC	 			= 'PC';
	/** 合同编号  */
	const SN_ERP_CN	 			= 'CN';
	/** 促销编号  */
	const SN_ERP_CX	 			= 'CX';
	/** 计次编号  */
	const SN_ERP_JC	 			= 'JC';
	/** 计次方案编号  */
	const SN_ERP_JF	 			= 'JF';
	/** 调拨单编号  */
	const SN_ERP_DB	 			= 'DB';
	/** 区域编号  */
	const SN_ERP_QY	 			= 'QY';
	/** 货位编号  */
	const SN_ERP_HW	 			= 'HW';
    /** 批发单 */
    const SN_ERP_PF               = 'SO';
    /** 批发退货单 */
    const SN_ERP_FT               = 'RI';
    /** 批发出库单 */
    const SN_ERP_SU               = 'SU';
    /** 批发入库单 */
    const SN_ERP_RU               = 'RU';
    /** 结算单编号*/
    const SN_TPL_RP               = 'RP';
    /** 门店配送单编号*/
    const SN_TPL_PD               = 'PD';
    /** 卡券编号 */
    const SN_ERP_CD               = 'CD';
    /** 代金券编号 */
    const SN_ERP_DJQ              = 'DJQ';

    /** 商品调价单编号*/
    const SN_ERP_TJ               = 'TJ';

    /** 批量添加新卡*/
    const SN_CRM_CP               = 'CP';

    /** 结算单编号*/
    const SN_TPL_JS               = 'JS';

    /** 服务储值项*/
    const SN_ERP_SAI               = 'SAI';

    /** 会员卡号*/
    const SN_CRM_CD               = '8';
	
    /** 短信充值*/
    const SN_SMS                  = '80';
	
    /** 自提码 */
    const SN_DELIVERY             = '90';
	
    /**
     * 10位自增数字
     */
    public static function createNubmer()
    {
        return SerialNumberModel::addData(['num'=>1]);
    }
	
	/**
	 * 创建日期
	 */
	public static function createData()
	{
		return date("ymd");
	}
	
	/**
	 * 补全字符串
	 */
	public static function createString($string)
	{
		return str_pad($string,5,'0',STR_PAD_LEFT);
	}			
	
	/**
	 * 创建编号统一方法
	 * 
	 * $sn =  new SerialNumber();
     * $nostr = $sn->createSN(SerialNumber::SN_ORDER_MAIN);
	 * 
	 * @param string  $type 订单类型
	 * @return string 
	 */
	public static function createSN($type = self::SN_ORDER_MAIN)
	{
		$nostr = $type.(self::createData()).(self::createNubmer());
			
		return $nostr;
	}

}
 		
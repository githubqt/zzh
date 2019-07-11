<?php
// +----------------------------------------------------------------------
// | 回收操作抽象类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://qudiandang.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：黄献国 
// +----------------------------------------------------------------------
// | Author: 黄献国  Date:2018/11/5 Time:19:00
// +----------------------------------------------------------------------


namespace Services\Recovery;


use Recovery\RecoveryModel;
use Services\BaseService;

abstract class RecoveryBaseService extends BaseService
{

    /**
     * 商户id
     * @var int
     */
    protected $supplierId = 0;

    
    /**
     * 回收类型:待审核
     */
    const RECOVERY_STATUS_TEN = 10;
    
    /**
     * 回收类型:审核拒绝
     */
    const RECOVERY_STATUS_FIFTEEN = 15;
    
    /**
     * 回收类型:出价中
     */
    const RECOVERY_STATUS_TWENTY = 20;
    
    /**
     * 回收类型:已估价
     */
    const RECOVERY_STATUS_THIRTY = 30;
    
    /**
     * 回收类型:无人估价
     */
    const RECOVERY_STATUS_FORTY = 40;
    
    /**
     * 回收类型:回收中
     */
    const RECOVERY_STATUS_FIFTY = 50;
    
    /**
     * 回收类型:已回收
     */
    const RECOVERY_STATUS_SIXTY = 60;
    
    /**
     * 回收类型:已售出
     */
    const RECOVERY_STATUS_SEVENTY = 70;
    
    /**
     * 回收类型:取消
     */
    const RECOVERY_STATUS_EIGHTY = 80;
    
    
    /**
     * 回收类型描述
     */
    const SETTLEMENT_VALUE = [
        self::RECOVERY_STATUS_TEN               => '待审核',
        self::RECOVERY_STATUS_FIFTEEN           => '审核拒绝',
        self::RECOVERY_STATUS_TWENTY            => '估价中',
        self::RECOVERY_STATUS_THIRTY            => '已估价',
        self::RECOVERY_STATUS_FORTY             => '无人估价',
        self::RECOVERY_STATUS_FIFTY             => '回收中',
        self::RECOVERY_STATUS_SIXTY             => '已回收',
        self::RECOVERY_STATUS_SEVENTY           => '已售出',
        self::RECOVERY_STATUS_EIGHTY            => '取消'
    ];

    /**
     * 瑕疵
     */
    const RECOVERY_FLAW_STATUS = [
        '1'                   => '印染',
        '2'                   => '变色',
        '3'                   => '变形',
        '4'                   => '污渍',
        '5'                   => '破损',
        '6'                   => '金属褪色',
        '7'                   => '无瑕疵',
    ];

    /**
     * 附件
     */
    const RECOVERY_ENCLOSURE_STATUS = [
        '1'              => '保卡',
        '2'              => '防尘袋',
        '3'              => '肩带',
        '4'              => '收据或发票',
        '5'              => '五金配件',
        '6'              => '其他附件',
        '7'              => '无附件',
    ];

    /**
     * 设置商户id
     * @param $supplier_id
     */
    public function setSupplierId($supplier_id)
    {
        $this->supplierId = $supplier_id;
    }

    /**
     * 获取商户id
     * @return int
     */
    public function getSupplierId()
    {
        return $this->supplierId;
    }

}
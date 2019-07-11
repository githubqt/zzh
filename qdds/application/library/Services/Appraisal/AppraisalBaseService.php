<?php
// +----------------------------------------------------------------------
// | 鉴定操作抽象类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：黄献国 
// +----------------------------------------------------------------------
// | Author: 黄献国  Date:2018/11/20 Time:17:44
// +----------------------------------------------------------------------


namespace Services\Appraisal;

abstract class AppraisalBaseService
{

    /**
     * 商户id
     * @var int
     */
    protected $supplierId = 0;

    /**
     * 是否是真品
     * @var integer  1：真品 2：仿品
     */
    protected $isGenuine = 1;

    /**
     * 拒绝原因
     * @var string
     */
    protected $failReason = '';

    /**
     * 补充说明
     * @var string
     */
    protected $supplementNote = '';

    /**
     * 证书编号
     * @var string
     */
    protected $appraisalCode = '';

    /**
     * 鉴定状态
     * @var integer
     */
    protected $appraisalStatus = 10;

    /**
     * 鉴定类型:待付款
     */
    const APPRAISAL_STATUS_TEN = 10;

    /**
     * 鉴定类型:付款审核
     */
    const APPRAISAL_STATUS_ELEVEN = 11;


    /**
     * 鉴定类型:付款拒绝
     */
    const APPRAISAL_STATUS_FIFTEEN = 15;
    
    /**
     * 鉴定类型:待处理
     */
    const APPRAISAL_STATUS_TWENTY = 20;
    
    /**
     * 鉴定类型:待补全资料
     */
    const APPRAISAL_STATUS_THIRTY = 30;
    
    /**
     * 鉴定类型:处理中
     */
    const APPRAISAL_STATUS_FORTY = 40;
    
    /**
     * 鉴定类型:待收货
     */
    const APPRAISAL_STATUS_FIFTY = 50;
    
    /**
     * 鉴定类型:已完成
     */
    const APPRAISAL_STATUS_SIXTY = 60;

    /**
     * 鉴定类型:已取消
     */
    const APPRAISAL_STATUS_SEVENTY = 70;
    
    /**
     * 是否是真品:真品
     */
    const APPRAISAL_IS_GENUINE_1 = 1;

    /**
     * 是否是真品:仿品
     */
    const APPRAISAL_IS_GENUINE_2 = 2;
    
    const APPRAISAL_IMAGE_VALUE = [
        ["id" => "1", "name" => "正面", "cover" => HOST_STATIC."common/images/bags/08.png"],
        ["id" => "2", "name" => "背面", "cover" => HOST_STATIC."common/images/bags/01.png"],
        ["id" => "3", "name" => "编号", "cover" => HOST_STATIC."common/images/bags/02.png"],
        ["id" => "4", "name" => "侧面", "cover" => HOST_STATIC."common/images/bags/03.png"],
        ["id" => "5", "name" => "底部", "cover" => HOST_STATIC."common/images/bags/04.png"],
        ["id" => "6", "name" => "肩带五金件", "cover" => HOST_STATIC."common/images/bags/05.png"],
        ["id" => "7", "name" => "拉链", "cover" => HOST_STATIC."common/images/bags/06.png"],
        ["id" => "8", "name" => "内衬", "cover" => HOST_STATIC."common/images/bags/07.png"],
    ];
    
    /**
     * 鉴定类型描述
     */
    const SETTLEMENT_VALUE = [
        self::APPRAISAL_STATUS_TEN                  => '待付款',
        self::APPRAISAL_STATUS_ELEVEN               => '付款审核',
        self::APPRAISAL_STATUS_FIFTEEN              => '付款拒绝',
        self::APPRAISAL_STATUS_TWENTY               => '待处理',
        self::APPRAISAL_STATUS_THIRTY               => '待补全资料',
        self::APPRAISAL_STATUS_FORTY                => '处理中',
        self::APPRAISAL_STATUS_FIFTY                => '待收货',
        self::APPRAISAL_STATUS_SIXTY                => '已完成',
        self::APPRAISAL_STATUS_SEVENTY              => '已取消'
    ];

    /**
     * 是否是真品  1：真品 2：仿品
     */
    const APPRAISAL_IS_GENUINE_VALUE = [
        self::APPRAISAL_IS_GENUINE_1                  => '真品',
        self::APPRAISAL_IS_GENUINE_2                  => '仿品'
    ];

    /**
     * 瑕疵
     */
    const APPRAISAL_FLAW_STATUS = [
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
    const APPRAISAL_ENCLOSURE_STATUS = [
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

    /**
     * 设置是否是真品
     * @param $is_genuine 1：真品 2：仿品
     */
    public function setIsGenuine($is_genuine)
    {
        $this->supplierId = $is_genuine;
    }

    /**
     * 获取是否是真品
     * @return integer 1：真品 2：仿品
     */
    public function getIsGenuine()
    {
        return $this->isGenuine;
    }

    /**
     * 设置拒绝原因
     * @param $fail_reason
     */
    public function setFailReason($fail_reason)
    {
        $this->failReason = $fail_reason;
    }

    /**
     * 获取拒绝原因
     * @return string
     */
    public function getFailReason()
    {
        return $this->failReason;
    }

    /**
     * 设置补充说明
     * @param $supplement_note
     */
    public function setSupplementNote($supplement_note)
    {
        $this->supplementNote = $supplement_note;
    }

    /**
     * 获取补充说明
     * @return string
     */
    public function getSupplementNote()
    {
        return $this->supplementNote;
    }

    /**
     * 设置证书编号
     * @param $appraisal_code
     */
    public function setAppraisalCode($appraisal_code)
    {
        $this->appraisalCode = $appraisal_code;
    }

    /**
     * 获取证书编号
     * @return string
     */
    public function getAppraisalCode()
    {
        return $this->appraisalCode;
    }

    /**
     * 设置鉴定状态
     * @param $supplier_id
     */
    public function setAppraisalStatus($appraisal_status)
    {
        $this->appraisalStatus = $appraisal_status;
    }

    /**
     * 获取鉴定状态
     * @return integer
     */
    public function getAppraisalStatus()
    {
        return $this->appraisalStatus;
    }

    /**
     * 通过keys获取values值
     * @param $keys string
     * @param $values array
     * @return string
     */
    public static function getValuesByKeys($keys,$values)
    {
        $txt = '';
        $value = [];
        if ($keys) {
            $keys = explode(',',$keys);
            foreach ($keys as $key=>$val) {
                $value[$key] = $values[$val];
            }
            $txt = implode(',', $value);
        }

        return $txt;
    }

    /**
     * 获取瑕疵
     * @param $flaw string
     *        瑕疵id，多个以逗号分开
     * @return string
     */
    public static function getFlaw($flaw)
    {
        return self::getValuesByKeys($flaw, self::APPRAISAL_FLAW_STATUS);
    }

    /**
     * 获取附件
     * @param $enclosure string
     *        附件id，多个以逗号分开
     * @return string
     */
    public static function getEnclosure($enclosure)
    {
        return self::getValuesByKeys($enclosure, self::APPRAISAL_ENCLOSURE_STATUS);
    }

}
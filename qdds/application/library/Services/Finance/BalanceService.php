<?php
/**
 * 财务结算管理
 * @version v0.01
 * @author laiqingtao
 * @time 2018-09-11
 */


namespace Services\Finance;


use Balance\BalanceItemsModel;
use Balance\BalanceModel;
use Common\CommonBase;
use Common\SerialNumber;
use Services\BaseService;
use Supplier\SupplierModel;

class BalanceService extends BaseService
{
    /**
     * 结算类型
     */
    const BALANCE_TYPE_1 = 1;
    const BALANCE_TYPE_2 = 2;
    const BALANCE_TYPE_3 = 3;
    const BALANCE_TYPE_4 = 4;
    const BALANCE_TYPE_VALUE = [
        self::BALANCE_TYPE_1       => '采购单',
        self::BALANCE_TYPE_2       => '订单',
        self::BALANCE_TYPE_3       => '快递查询',
        self::BALANCE_TYPE_4       => '会员充值'
    ];
    /**
     * 结算状态
     */
    const BALANCE_STATUS_1 = 1;
    const BALANCE_STATUS_2 = 2;
    const BALANCE_STATUS_3 = 3;
    const BALANCE_STATUS_4 = 4;
    const BALANCE_STATUS_5 = 5;
    const BALANCE_STATUS_6 = 6;
    const BALANCE_STATUS_VALUE = [
        self::BALANCE_STATUS_1       => '待审核',
        self::BALANCE_STATUS_2       => '待确认',
        self::BALANCE_STATUS_3       => '待打款',
        self::BALANCE_STATUS_4       => '待收款',
        self::BALANCE_STATUS_5       => '已结算',
        self::BALANCE_STATUS_6       => '已取消'
    ];

    const BALANCE_STATUS_SP = [
        self::BALANCE_STATUS_2,
        self::BALANCE_STATUS_3,
        self::BALANCE_STATUS_4,
        self::BALANCE_STATUS_5
    ];

    const BALANCE_STATUS_SP_VALUE = [
        self::BALANCE_STATUS_2       => '待确认',
        self::BALANCE_STATUS_3       => '待打款',
        self::BALANCE_STATUS_4       => '待收款',
        self::BALANCE_STATUS_5       => '已结算',
    ];

    /**
     * 错误提示
     * @var string
     */
    protected $error = '';

    /**
     * 获取错误提示
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /* 结算列表*/
    public static function getList(array $search = [])
    {
        $result = BalanceModel::getList($search);

        foreach ($result['rows'] as $key => $value) {
            $result['rows'][$key]['note'] = str_replace(array("\r\n", "\r", "\n"), "<br>", $value['note']);
            $result['rows'][$key]['balance_type_txt'] = self::BALANCE_TYPE_VALUE[$value['balance_type']];
            $result['rows'][$key]['status_txt'] = self::BALANCE_STATUS_VALUE[$value['status']];
        }

        return $result;
    }

    /* 添加结算单*/
    public function addData(array $info = [], array $finance_list = [])
    {
        // 开始事务
        $this->newWrite()->beginTransaction();
        try {
            //创建结算单
            $auth = BalanceModel::auth();
            $balanceModel = new BalanceModel();
            $balanceModel->supplier_id = $info['supplier_id'];
            $balanceModel->balance_type = $info['balance_type'];
            $balanceModel->starttime = $info['starttime'];
            $balanceModel->endtime = $info['endtime'];
            $balanceModel->actual_amount = $info['actual_amount'];
            $balanceModel->note = date("Y-m-d H:i:s").' '.$auth['fullname'].' 添加结算单 备注：'.trim($info['note']);
            $balanceModel->in_amount = $info['in_amount'];
            $balanceModel->out_amount = $info['out_amount'];
            $balanceModel->original_amount = $info['original_amount'];
            $balanceModel->service_amount = $info['service_amount'];
            $balanceModel->balance_no = SerialNumber::createSN(SerialNumber::SN_TPL_JS);
            $balanceModel->status = self::BALANCE_STATUS_1;
            $balanceModel->admin_id = $auth['id'];
            $balanceModel->admin_name = $auth['fullname'];
            $balanceModel->is_del = CommonBase::DELETE_SUCCESS;
            $balanceModel->created_at = date('Y-m-d H:i:s');
            $balanceModel->updated_at = date('Y-m-d H:i:s');
            $balanceModel->save();
            /**
             * 创建结算单详情
             */
            foreach ($finance_list as $key => $value) {
                $balanceItemsModel = new BalanceItemsModel();
                $balanceItemsModel->supplier_id = $info['supplier_id'];
                $balanceItemsModel->balance_id = $balanceModel->id;
                $balanceItemsModel->finance_id = $value['id'];
                $balanceItemsModel->is_del = CommonBase::DELETE_SUCCESS;
                $balanceItemsModel->created_at = date('Y-m-d H:i:s');
                $balanceItemsModel->updated_at = date('Y-m-d H:i:s');
                $balanceItemsModel->save();
                $updata = [];
                $updata['settle_type'] = FinanceBaseService::SETTLEMENT_ING;
                $updata['settle_id'] = $balanceModel->id;
                $updata['settle_no'] = $balanceModel->balance_no;
                FinanceService::updateByID($updata,$value['id']);
            }

            return $this->newWrite()->commit();

        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            $this->newWrite()->rollback();
            return false;
        }
    }


    /* 删除结算单*/
    public function delete(int $id)
    {
        // 开始事务
        $this->newWrite()->beginTransaction();
        try {
            //删除结算单
            $auth = BalanceModel::auth();
            $balanceModel = BalanceModel::find($id);
            $balanceModel->note = $balanceModel['note'].'
            '.date("Y-m-d H:i:s").' '.$auth['fullname'].' 删除结算单';
            $balanceModel->is_del = CommonBase::DELETE_FAIL;
            $balanceModel->updated_at = date('Y-m-d H:i:s');
            $balanceModel->deleted_at = date('Y-m-d H:i:s');
            $balanceModel->save();

            $finance_list = BalanceItemsModel::findWhere(['balance_id'=>$id]);
            /**
             * 删除结算单详情
             */
            foreach ($finance_list as $key => $value) {
                BalanceItemsModel::find($value['id'])->delete();
                $updata = [];
                $updata['settle_type'] = FinanceBaseService::SETTLEMENT_NO;
                $updata['settle_id'] = 0;
                $updata['settle_no'] = '';
                FinanceService::updateByID($updata,$value['finance_id']);
            }

            return $this->newWrite()->commit();

        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            $this->newWrite()->rollback();
            return false;
        }
    }

    /* 结算单详情*/
    public static function getInfoByID($id)
    {
        $balance = BalanceModel::find($id)->toArray();
        $balance['note'] = str_replace(array("\r\n", "\r", "\n"), "<br>", $balance['note']);
        $balance['balance_type_txt'] = self::BALANCE_TYPE_VALUE[$balance['balance_type']];
        $balance['status_txt'] = self::BALANCE_STATUS_VALUE[$balance['status']];

        return $balance;
    }

    /* 确认结算单*/
    public function check(array $info, int $id)
    {
        // 开始事务
        $this->newWrite()->beginTransaction();
        try {
            $auth = BalanceModel::auth();
            $balanceModel = BalanceModel::find($id);
            $balanceModel->note = $balanceModel['note'].'
            '.date("Y-m-d H:i:s").' '.$auth['fullname'].' 确认结算单';
            $balanceModel->status = self::BALANCE_STATUS_3;
            $balanceModel->save();

            return $this->newWrite()->commit();

        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            $this->newWrite()->rollback();
            return false;
        }
    }

    /* 更新结算单*/
    public static function updateByID($data, $id)
    {
        return BalanceModel::update($data, ['id'=>$id]);
    }

    /* 收款结算单*/
    public function finance(array $info, int $id)
    {
        // 开始事务
        $this->newWrite()->beginTransaction();
        try {
            $auth = BalanceModel::auth();
            $balanceModel = BalanceModel::find($id);
            $balanceModel->note = $balanceModel['note'].'
            '.date("Y-m-d H:i:s").' '.$auth['fullname'].' 结算单收款';
            $balanceModel->status = self::BALANCE_STATUS_5;
            $balanceModel->save();

            //更新收支单状态
            $finance_list = BalanceItemsModel::findWhere(['balance_id'=>$id]);
            foreach ($finance_list as $key => $value) {
                $updata = [];
                $updata['settle_type'] = FinanceBaseService::SETTLEMENT_YES;
                $updata['settle_time'] = date("Y-m-d H:i:s");
                FinanceService::updateByID($updata,$value['finance_id']);
            }

            return $this->newWrite()->commit();

        } catch (\Exception $exception) {
            $this->error = $exception->getMessage();
            $this->newWrite()->rollback();
            return false;
        }
    }
}
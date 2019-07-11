<?php

/**
 * 鉴定model
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-09
 */

namespace Appraisal;

use Core\Qzcode;
use Brand\BrandModel;
use Image\ImageModel;

class AppraisalModel extends \BaseModel
{
    protected static $_tableName = 'appraisal';

    /**
     * 记录入库
     *
     * @param array $data
     *            表字段名作为key的数组
     * @return int 入库成功则返回入库记录的自增ID，否则返回FALSE
     */
    public static function addData($data)
    {
        $data ['is_del'] = self::DELETE_SUCCESS;
        $data ['created_at'] = date("Y-m-d H:i:s");
        $data ['updated_at'] = date("Y-m-d H:i:s");

        $pdo = self::_pdo('db_w');
        return $pdo->insert(self::$_tableName, $data);
    }

    /**
     * 根据证书编号获取单条数据
     *
     * @param interger $id
     * @return mixed
     *
     */
    public static function getInfoByID($serial)
    {
        $where ['is_del'] = self::DELETE_SUCCESS;
        $where ['appraisal_code'] = $serial;

        $pdo = self::_pdo('db_r');
        $info = $pdo->clear()->select('*')->from(self::$_tableName)->where($where)->getRow();


        if ($info ['product_id']) {
            $info ['product_url'] = ImageModel::getInfoByCertificateID($info ['id'], 'appraisal', '1,2');
            foreach ($info ['product_url'] as $key => $val) {

                if (!empty ($info ['product_url'])) {
                    $info ['product_url'] [$key] ['img_url'] = HOST_FILE . $val ['img_url'];
                } else {
                    $info ['product_url'] [$key] ['img_url'] = HOST_STATIC . 'common/images/common.png';
                }
            }
        }
        if ($info ['id']) {
            $info ['appraisal_enclosure_url'] = ImageModel::getInfoByCertificateID($info ['id'], 'appraisal', '3,6,7');
            foreach ($info ['appraisal_enclosure_url'] as $key => $val) {
                if (!empty ($info ['appraisal_enclosure_url'])) {
                    $info ['appraisal_enclosure_url'] [$key] ['img_url'] = HOST_FILE . $val ['img_url'];
                } else {
                    $info ['appraisal_enclosure_url'] [$key] ['img_url'] = HOST_STATIC . 'common/images/common.png';
                }
            }
        }
        if ($info ['brand_id']) {
            $brandInfo = BrandModel::getInfoByID($info ['brand_id']);
            $info ['brand_txt'] = $brandInfo ['name'] . $brandInfo ['en_name'];
        }

        if ($info ['appraisal_admin_name_url']) {
            $info ['assayer'] = HOST_FILE . $info ['appraisal_admin_name_url'];
        }

        if ($info['id'] && __ENV__ != "DEV") {
            $url = JD_URL . "/query";
            $Qzcode = new Qzcode ();
            $url = $Qzcode->scerweima($url);
            $info ['appraisal_url'] = $url;
        }

        // 获取保单
        if ($info['policy_id']){
            $policy = AppraisalPolicyModel::find($info['policy_id']);
            if ($policy){
                $info['policy'] = $policy->toArray();
                $info['policy']['policy_url'] = HOST_FILE . $info['policy'] ['policy_url'];
            }
        }
        return $info;
    }
}
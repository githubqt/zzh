<?php

/**
 * 用户model
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-11
 */

namespace User;

use Assemble\Builder;
use Common\Crypt3Des;
use Custom\YDLib;
use Common\CommonBase;
use Supplier\SupplierModel;
use User\UserSupplierThridModel;
use User\UserSupplierBindModel;
use Seckill\SeckillPrizeUserModel;
use ErrnoStatus;
use Publicb;

class UserModel extends \BaseModel
{
    protected $perPage = 10;

    protected static $_tableName = 'user';
    static $_login = 'FGRTYUSDS';

    // 登陆有效时间
    public static $login_time = '18000'; // 五小时
    public static $_userLogin = 'FGRTYUSDSCT_' . SUPPLIER_ID; // 用户登录

    public static $log_validation = "User\UserModel::LoByUserId::" . SUPPLIER_ID . "::";


    /**
     * 退出（清除cookie）
     *
     * @return boolean
     */
    public static function signout()
    {
        $del = setcookie(self::$_userLogin, "", time() - 180000, "/", COOKIE_DOMAIN);

        return $del;
    }

    /**
     * 验证账户是否存在id
     *
     * @param int $uid
     * @return boolean|string
     */
    public static function checkUserByUserId($user_id)
    {
        if (empty ($user_id))
            return false;

        $mem = YDLib::getMem('memcache');
        $key = __CLASS__ . "::" . __FUNCTION__ . "::" . SUPPLIER_ID . "::" . $user_id;
        $user = $mem->get($key);
        $pdo = self::_pdo('db_r');
        if (!$user) {
            $user = $pdo->clear()->select('*')->from(self::$_tableName)->where([
                'id' => $user_id,
                'is_del' => '2'
            ])->getRow();
        }

        if ($user) {
            // 获取本商户是否有该用户信息
            $supplier_user = $pdo->clear()->select('*')->from('user_supplier')->where([
                'user_id' => $user_id,
                'supplier_id' => SUPPLIER_ID,
                'is_del' => '2'
            ])->getRow();

            if ($supplier_user) {
                $user ['supplier'] = $supplier_user;
                return $user;
            }
        }
        return false;
    }

    /**
     * 通过用户名与密码登录
     *
     * @param string $name
     *            账号
     * @param string $password
     *            密码
     * @return array 非空返回登陆者信息
     *         null 空值登陆失败
     */
    public static function login($user, $password)
    {
        $salt = $user ['salt'];
        $password = md5($password . $salt);
        $pdo = YDLib::getPDO('db_r');
        $user = $pdo->clear()->select('*')->from(self::$_tableName)->where([
            'id' => $user ['id'],
            'password' => $password,
            'is_del' => '2'
        ])->getRow();

        if ($user) {
            // 商户用户信息
            $child_user = UserSupplierModel::getAdminInfo($user ['id']);
            if (!$child_user) {
                return false;
            }
            return $user;
        }
        return false;
    }

    /**
     * 获取用户信息
     *
     * @param unknown $UserId
     * @param number $headImgSize
     * @return multitype:|Ambigous <unknown, string>
     */
    public static function getAdminInfo($UserId)
    {
        if (!$UserId)
            return [];

        $mem = YDLib::getMem('memcache');
        $user = $mem->get('user_info_' . $UserId);
        if (!$user) {
            $pdo = YDLib::getPDO('db_r');
            $serach = 'id,name,mobile,user_img,sex,birthday,qq,wchat,province_id,city_id,area_id,address';
            $user = $pdo->clear()->select($serach)->from(self::$_tableName)->where([
                'id' => $UserId,
                'is_del' => '2'
            ])->getRow();

            if (!$user) {
                return false;
            }

            $mem->delete('user_info_' . $UserId);
            $mem->set('user_info_' . $UserId, $user);
        }
        return $user;
    }

    /**
     * 验证账户是否存在
     *
     * @param int $uid
     * @return boolean|string
     */
    public static function checkUserByMobile($mobile)
    {
        if (empty ($mobile))
            return false;

        $pdo = self::_pdo('db_r');
        $user = $pdo->clear()->select('*')->from(self::$_tableName)->where([
            'mobile' => $mobile
        ])->getRow();

        if (!$user) {
            return false;
        } else {
            // 获取本商户是否有该用户信息
            $supplier_user = $pdo->clear()->select('*')->from('user_supplier')->where([
                'user_id' => $user ['id'],
                'supplier_id' => SUPPLIER_ID,
                'is_del' => '2'
            ])->getRow();
            $user ['supplier'] = $supplier_user;
        }
        return $user;
    }

    /**
     * 通过手机号查询用户
     *
     * @param int $uid
     * @return boolean|string
     */
    public static function getInfoByMobile($mobile)
    {
        $pdo = self::_pdo('db_r');
        $user = $pdo->clear()->select('id')->from(self::$_tableName)->where([
            'mobile' => $mobile
        ])->getRow();
        return $user;
    }

    /**
     * 添加信息
     *
     * @param array $info
     * @return mixed
     */
    public static function addUser($info)
    {
        $invitation_id = $info ['invitation_id'];
        unset ($info ['invitation_id']);
        $db = YDLib::getPDO('db_w');
        $db->beginTransaction();
        try {
            $info ['is_del'] = '2';
            $info ['created_at'] = date("Y-m-d H:i:s");
            $info ['updated_at'] = date("Y-m-d H:i:s");
            $last_id = $db->insert(self::$_tableName, $info);
            if ($last_id) {
                // 插入用户商户信息
                $data = [
                    'user_id' => $last_id,
                    'supplier_id' => SUPPLIER_ID,
                    'is_del' => '2',
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s")
                ];
                if (!empty ($invitation_id)) {
                    $data ['invitation_id'] = $invitation_id;
                }
                $user_supplier = $db->insert('user_supplier', $data);
                if ($user_supplier == FALSE) {
                    $db->rollback();
                    return FALSE;
                }
            } else {
                $db->rollback();
                return FALSE;
            }

            $db->commit();
            return $last_id;
        } catch (\Exception $e) {
            $db->rollback();
            return FALSE;
        }
    }

    /**
     * 根据一条自增ID更新表记录
     *
     * @param array $data
     *            更新字段作为key的数组
     * @param array $id
     *            表自增id
     * @return boolean 更新结果
     */
    public static function updateByID($data, $id)
    {
        $data ['updated_at'] = date("Y-m-d H:i:s");

        $pdo = self::_pdo('db_w');
        $update = $pdo->update(self::$_tableName, $data, array(
            'id' => intval($id)
        ));
        if ($update) {
            $mem = YDLib::getMem('memcache');
            $mem->delete('user_info_' . $id);

            $key = __CLASS__ . "::checkUserByUserId::" . $id;
            $mem->delete($key);
            $key = __CLASS__ . "::getUserInfoById::" . SUPPLIER_ID . "::" . $id;
            $mem->delete($key);
            return $update;
        }
        return false;
    }

    /**
     * 补全手机号信息
     *
     * @param array $data
     *            更新字段作为key的数组
     * @param array $user_id
     *            会员id
     * @return boolean 更新结果
     */
    public static function updateInfo($data, $user_id)
    {
        $pdo = self::_pdo('db_w');
        $pdo->beginTransaction();
        $jsonData = [];
        try {
            $old_user = self::checkUserByMobile($data ['mobile']);
            $mem = YDLib::getMem('memcache');
            $mem->delete('user_info_' . $user_id);
            $mem->delete('user_info_' . $old_user ['id']);
            if ($old_user) {
                $new_user = self::getAdminInfo($user_id);
                // 查询手机号绑定微信
                $search = [];
                $search ['user_id'] = $old_user ['id'];
                $search ['type'] = CommonBase::USER_THRID_TYPE_1;
                $bindInfo = UserSupplierBindModel::getInfo($search);
                if ($bindInfo) {

                    //如果存在删除原信息删除原信息
                    $del = UserSupplierBindModel::deleteByUserID($old_user ['id']);
                    if (!$del) {
                        $pdo->rollback();
                        YDLib::output(ErrnoStatus::STATUS_60522);
                    }

                    //$pdo->rollback ();
                    //YDLib::output ( ErrnoStatus::STATUS_40106 );
                    //老手机号绑定关系解绑
                    //self::updateByID(['mobile'=>''],$old_user ['id']);
                }

                // 查询现在绑定信息
                $oldsearch = [];
                $oldsearch ['user_id'] = $user_id;
                $oldsearch ['type'] = CommonBase::USER_THRID_TYPE_1;
                $oldbindInfo = UserSupplierBindModel::getInfo($oldsearch);
                if (!$oldbindInfo) {
                    // 查询当前微信三方id
                    $old_user_three = UserSupplierThridModel::getInfoByUserId($user_id);
                    if (!$old_user_three) {
                        $pdo->rollback();
                        YDLib::output(ErrnoStatus::STATUS_60522);
                    }

                    // 添加绑定信息
                    $search ['thrid_id'] = $old_user_three ['id'];
                    $bind_id = UserSupplierBindModel::addData($search);
                    if (!$bind_id) {
                        $pdo->rollback();
                        YDLib::output(ErrnoStatus::STATUS_60523);
                    }
                } else {
                    $oldbindup = [];
                    $oldbindup ['user_id'] = $old_user ['id'];
                    $res = UserSupplierBindModel::updateByID($oldbindup, $oldbindInfo ['id']);
                    if (!$res) {
                        $pdo->rollback();
                        YDLib::output(ErrnoStatus::STATUS_60527);
                    }
                    $bind_id = $oldbindInfo ['id'];
                }

                //删除原用户微信认证信息  hxg 18-10-12
                $res = UserSupplierThridModel::deleteByUserID($old_user ['id']);
                if (!$res) {
                    $pdo->rollback();
                    YDLib::output(ErrnoStatus::STATUS_60524);
                }

                // 更新三方表user_id
                $res = UserSupplierThridModel::updateByID([
                    'user_id' => $old_user ['id']
                ], $user_id);

                if (!$res) {
                    $pdo->rollback();
                    YDLib::output(ErrnoStatus::STATUS_60524);
                }

                // 删除原user表
                $res = self::deleteByID($user_id);

                if (!$res) {
                    $pdo->rollback();
                    YDLib::output(ErrnoStatus::STATUS_60525);
                }

                // 更新UserSupplier表 hxg 18-10-12
                /* $res = UserSupplierModel::updateByUserID ( [
                                                                'user_id' => $old_user ['id']
                                                                ],$user_id ); */
                //删除UserSupplier表 hxg 18-10-12
                $res = UserSupplierModel::updateByUserID(['is_del' => '1'], $user_id);

                if (!$res) {
                    $pdo->rollback();
                    YDLib::output(ErrnoStatus::STATUS_60525);
                }

                // 更新数据
                unset ($data ['mobile']);
                if ($new_user) {
                    $data['user_img'] = $new_user['user_img'];
                    $data['sex'] = $new_user['sex'];
                    $data['name'] = $new_user['name'];
                    $data['birthday'] = $new_user['birthday'];
                    $data['province_id'] = $new_user['province_id'];
                    $data['city_id'] = $new_user['city_id'];
                    $data['area_id'] = $new_user['area_id'];
                }
                $res = self::updateByID($data, $old_user ['id']);
                if (!$res) {
                    $pdo->rollback();
                    YDLib::output(ErrnoStatus::STATUS_60084);
                }
                // 更新奖品表
                $updata = [];
                $updata ['user_id'] = $old_user ['id'];
                $res = SeckillPrizeUserModel::updateByBindID($updata, $bind_id);
                if (!$res) {
                    $pdo->rollback();
                    YDLib::output(ErrnoStatus::STATUS_60526);
                }
                $user_id = $old_user ['id'];
            } else {
                $res = self::updateByID($data, $user_id);
                if (!$res) {
                    $pdo->rollback();
                    YDLib::output(ErrnoStatus::STATUS_60084);
                }
            }

            $pdo->commit();
//            Publicb::loginCookie($user_id);
            $token = Publicb::getLoginToken($user_id);
            YDLib::output(ErrnoStatus::STATUS_SUCCESS, [
                'user_id' => $user_id,
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            $pdo->rollback();
            YDLib::output(ErrnoStatus::STATUS_60084);
        }
    }

    /**
     * 更新信息
     *
     * @param array $info
     * @return mixed
     */
    public static function updataUserAndAddItem($info, $user_id)
    {
        $invitation_id = $info ['invitation_id'];
        unset ($info ['invitation_id']);
        $db = YDLib::getPDO('db_w');
        $db->beginTransaction();
        try {
            $info ['updated_at'] = date("Y-m-d H:i:s");

            $update = $db->update(self::$_tableName, $info, array(
                'id' => intval($user_id)
            ));
            if ($update) {
                // 插入用户商户信息
                $data = [
                    'user_id' => $user_id,
                    'supplier_id' => SUPPLIER_ID,
                    'is_del' => '2',
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s")
                ];
                if (!empty ($invitation_id)) {
                    $data ['invitation_id'] = $invitation_id;
                }
                $user_supplier = $db->insert('user_supplier', $data);
                if ($user_supplier == FALSE) {
                    $db->rollback();
                    return FALSE;
                }
            } else {
                $db->rollback();
                return FALSE;
            }

            $db->commit();
            $mem = YDLib::getMem('memcache');
            $mem->delete('user_info_' . $user_id);

            $key = __CLASS__ . "::checkUserByUserId::" . $user_id;
            $mem->delete($key);

            return $user_id;
        } catch (\Exception $e) {
            $db->rollback();
            return FALSE;
        }
    }

    /**
     * 根据表自增 ID删除记录
     *
     * @param int $id
     *            表自增 ID
     * @return boolean 删除是否成功
     */
    public static function deleteByID($id)
    {
        $data ['is_del'] = self::DELETE_FAIL;
        $data ['updated_at'] = date("Y-m-d H:i:s");
        $data ['deleted_at'] = date("Y-m-d H:i:s");

        $pdo = self::_pdo('db_w');
        $res = $pdo->update(self::$_tableName, $data, array(
            'id' => intval($id)
        ));
        if ($res) {
            $mem = YDLib::getMem('memcache');
            $mem->delete('user_info_' . $id);

            $key = __CLASS__ . "::checkUserByUserId::" . $id;
            $mem->delete($key);
        }
        return $res;
    }

    /**
     * 生成密码及盐值
     *
     * @param int $password
     *            密码
     * @return array 生成后的密码及盐值
     */
    public static function setPassword($password)
    {
        $info = [];
        $info ['salt'] = self::random('8');
        $info ['password'] = md5($password . $info ['salt']);
        return $info;
    }

    /**
     * 产生随机字符串
     *
     * @param int $length
     *            输出长度
     * @param string $chars
     *            可选的 ，默认为 0123456789
     * @return string 字符串
     */
    public static function random($length, $chars = '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ!@#$%^&*~')
    {
        return substr(str_shuffle($chars), 0, $length);
    }

    /**
     * 验证用户是否登陆
     * @param int $user_id
     * @return boolean
     */
    public static function checkLogin($user_id)
    {
        // 验证用户是否异常
        $info = self::checkUserByUserId($user_id);
        if (!$info) {
            self::signout();
            YDLib::output(ErrnoStatus::STATUS_60001);//用户不存在
        }
        if ($info ['supplier']['status'] != CommonBase::STATUS_SUCCESS) { // 用户被禁用
            self::signout();
            YDLib::output(ErrnoStatus::STATUS_60029);
        }

        $mem = YDLib::getMem('memcache');
        $key = UserModel::$log_validation . $user_id;
        $cacheUser = $mem->get($key);
        if ($cacheUser) {
            self::signout();
            YDLib::output(ErrnoStatus::STATUS_50033);//后台修改密码或其他操作后需要再次登陆
        }

        // 是否登陆
        $user_cooker_id = $_COOKIE [UserModel::$_userLogin];

        if (isset ($user_cooker_id) && Crypt3Des::decrypt($user_cooker_id) == $user_id) {
            return true;
        } else {
            YDLib::output(ErrnoStatus::STATUS_50006);
        }

    }


    /**
     * 获取用户信息
     * @param $user_id
     * @return mixed
     */
    public static function getUserInfo($user_id)
    {
        $supplier_id = SUPPLIER_ID;
        $memid = "getUserInfo_{$supplier_id}_{$user_id}";
        $mem = YDLib::getMem('memcache');
        $user = $mem->get($memid);
        if ($user) {
            return $user;
        }

        $pdo = self::_pdo('db_r');
        $user = $pdo->clear()->select('*')->from(self::$_tableName)->where([
            'id' => $user_id,
            'is_del' => '2'
        ])->getRow();

        if (!$user) {
            return [];
        }

        // 获取本商户是否有该用户信息
        $supplier_info = $pdo->clear()->select('*')->from('user_supplier')->where([
            'user_id' => $user_id,
            'supplier_id' => SUPPLIER_ID,
            'is_del' => '2'
        ])->getRow();

        $user['supplier'] = $supplier_info;

        $mem->set($memid,$user);

        return $user;
    }

    /**
     * 获取我的邀请用户列表
     * @param $user_id
     * @return array
     */
    public static function getInvitationList($user_id){

        $build = new Builder();
        $build->from(sprintf("`%s` a LEFT JOIN `%s` b ON a.user_id = b.id",UserSupplierModel::getFullTable(),self::getFullTable()));
        $build->select(['b.id',' b.`name`',' b.mobile',' b.user_img',' b.created_at',' b.is_del']);
        $build->where('a.invitation_id',$user_id);
        $build->where('a.supplier_id',SUPPLIER_ID);
        $build->orderAlias('b');
        $build->orderBy('created_at desc');
        $list = self::paginate($build);

        foreach ($list['rows'] as $key=> $row){
            $list['rows'][$key]['is_del_text'] = $row['is_del'] == self::DELETE_SUCCESS ? '已删除':'正常';
            $list['rows'][$key]['user_img'] = $row['user_img'];
            if (!empty ($row ['user_img'])) {
                $user_img = HOST_FILE . CommonBase::imgSize($row ['user_img'], 1);
            } else {
                $user_img= HOST_STATIC . 'common/images/user_photo.jpg';
            }
            $list['rows'][$key]['user_img'] = $user_img;
            $list['rows'][$key]['name'] = $row['name']?:'';
            $list['rows'][$key]['created_at_date'] = date('Y-m-d',strtotime($row['created_at']));
        }

        return $list;
    }
}
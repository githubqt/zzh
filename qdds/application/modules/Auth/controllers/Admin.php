<?php
use Admin\AdminModel;
use Admin\RoleModel;
/**
 * 管理员控制方法
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-04
 */
class AdminController extends BaseController 
{
   
  
    
    /**
     * 管理员列表
     * @return boolean
     * @version huangxianguo 
     * @time 2018-05-08
     */
    public function listAction() 
    {
        if (!empty($_REQUEST['format']) && $_REQUEST['format'] == "list") {
           $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '';
           $rows = isset($_REQUEST['rows']) ? trim($_REQUEST['rows']) : '';
           if (!empty($_REQUEST['info'])) {
              $info['info'] = $_REQUEST['info'];
           }
           $info['info']['sort'] = isset($_REQUEST['sort']) ? trim($_REQUEST['sort']) : 'id';
           $info['info']['order'] = isset($_REQUEST['order']) ? trim($_REQUEST['order']) : 'DESC';	
   
           $jsonData = [];
           $list = AdminModel::getList($info,$page-1,$rows);
           
           if ($list == false) {
               $jsonData['code'] = '500';
               $jsonData['msg'] = '获取列表失败！';
               echo $this->apiOut($jsonData);
               exit;
           }
           foreach ($list['list'] as &$val) {
               if($val['status'] == 1) {
                   $val['status'] = '禁用';
               } else {
                   $val['status'] = '正常';
               }
               $val['role_name'] = '超级管理员';
               if ($val['role_id']>0) {
                   $val['role_name'] = RoleModel::getInfoById($val['role_id'])['name'];
               }
           }
           $jsonData['total'] = $list['total'];
		   $jsonData['rows'] = $list['list'];
           echo $this->apiOut($jsonData);
           exit;
       }
       
       $parent = RoleModel::getAll();
       
       $this->getView()->assign("parent", $parent);
    }
    
    
    /**
     * 添加管理员
     * @return boolean
     * @version huangxianguo
     * @time 2018-05-08
     */
    public function addAction()
    {
        $format = $this->_request->get('format');
        if (!empty($format) && $format == "add") {
            $info = $this->_request->get('info');
            if (!$info) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '数据不正确！';
                echo $this->apiOut($jsonData);
                exit;
            }
            if (!$info['name']) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '请输入账号！';
                echo $this->apiOut($jsonData);
                exit;
            }
            //获取登陆人信息
            $adminInfo = AdminModel::getAdminLoginInfo(AdminModel::getAdminID());
            
            $user = AdminModel::checkUserId($adminInfo['supplier_id'],$info['name']);
            if ($user) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '该账号户已存在！';
                echo $this->apiOut($jsonData);
                exit;
            }
            if (!$info['password']) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '请输入密码！';
                echo $this->apiOut($jsonData);
                exit;
            }
            if (!$info['role_id']) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '请选择岗位！';
                echo $this->apiOut($jsonData);
                exit;
            }
            
            $pd = AdminModel::setPassword($info['password']);
            $info['salt'] = $pd['salt'];
            $info['password'] = $pd['password'];
            //添加主信息
           $last_id = AdminModel::addUser($info);
           if (!$last_id) {
               $jsonData['code'] = '500';
               $jsonData['msg'] = '保存失败！';
               echo $this->apiOut($jsonData);
               exit;
           }
           
           
           $jsonData['code'] = '200';
           $jsonData['msg'] = '保存成功！';
           echo $this->apiOut($jsonData);
           exit;
        }
        
        $parent = RoleModel::getAll();
        
        $this->getView()->assign("parent", $parent); 
    }
    
    
    /**
     * 查看管理员
     * @return boolean
     * @version huangxianguo
     * @time 2018-05-08
     */
    public function detailAction()
    {
        $id = $this->_request->get('id');
        
         $parent = RoleModel::getAll();
        
        $this->getView()->assign("parent", $parent); 
        //获取详情
        $data = AdminModel::getAdminInfo($id);
       
        $this->getView()->assign("data", $data);
    }
    
    
    
    /**
     * 编辑管理员
     * @return boolean
     * @version huangxianguo
     * @time 2018-05-08
     */
    public function editAction()
    {
        $id = $this->_request->get('id');
        $format = $this->_request->get('format');
        if (!empty($format) && $format == "edit") {
            $info = $this->_request->get('info');
            if (!$info) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '数据不正确！';
                echo $this->apiOut($jsonData);
                exit;
            }
           
            if (!$info['role_id']) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '请选择岗位！';
                echo $this->apiOut($jsonData);
                exit;
            }
           
            //更新主信息
           $last_id = AdminModel::updateByID($info,$id);
            if (!$last_id) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '编辑失败！';
                echo $this->apiOut($jsonData);
                exit;
            }
            
           
            $jsonData['code'] = '200';
            $jsonData['msg'] = '编辑成功！';
            echo $this->apiOut($jsonData);
            exit;
        }
    
    
    
         $parent = RoleModel::getAll();
        
        $this->getView()->assign("parent", $parent); 
        //获取详情
        $data = AdminModel::getAdminInfo($id);
       
        $this->getView()->assign("data", $data);
    }
    
    /**
     * 删除管理员
     * @return boolean
     * @version huangxianguo
     * @time 2018-05-08
     */
    public function deleteAction()
    {
        $id = $this->_request->get('id');
        
        //删除主信息
        $remove = AdminModel::deleteByID($id);
        if (!$remove) {
            $jsonData['code'] = '500';
            $jsonData['msg'] = '删除失败！';
            echo $this->apiOut($jsonData);
            exit;
        }

        //添加强制退出memcache
        $mem = \Custom\YDLib::getMem ( 'memcache' );
        $mem->set (ADMIN_FORCED_RETURN.$id,$id);

        $jsonData['code'] = '200';
        $jsonData['msg'] = '删除成功！';
        echo $this->apiOut($jsonData);
        exit;
    }
    
    /**
     * 编辑管理员密码
     * @return boolean
     * @version huangxianguo
     * @time 2018-05-08
     */
    public function editpasswordAction()
    {
        $id = $this->_request->get('id');
        $format = $this->_request->get('format');
        if (!empty($format) && $format == "edit") {
            $info = $this->_request->get('info');
            if (!$info['password']) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '请输入密码！';
                echo $this->apiOut($jsonData);
                exit;
            }
    
            if (!$info['repassword']) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '请输入确认密码！';
                echo $this->apiOut($jsonData);
                exit;
            }
    
            if ($info['repassword'] != $info['password']) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '两次密码输入不一致！';
                echo $this->apiOut($jsonData);
                exit;
            }
             
            $pd = AdminModel::setPassword($info['password']);
            $data['salt'] = $pd['salt'];
            $data['password'] = $pd['password'];
    
            //更新主信息
            $last_id = AdminModel::updateByID($data,$id);
            if (!$last_id) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '更新密码失败！';
                echo $this->apiOut($jsonData);
                exit;
            }

            //添加强制退出memcache
            $mem = \Custom\YDLib::getMem ( 'memcache' );
            $mem->set (ADMIN_FORCED_RETURN.$id,$id);

            $jsonData['code'] = '200';
            $jsonData['msg'] = '编辑成功！';
            echo $this->apiOut($jsonData);
            exit;
        }

        $parent = RoleModel::getAll();

        $this->getView()->assign("parent", $parent);
        //获取详情
        $data = AdminModel::getAdminInfo($id);

        $this->getView()->assign("data", $data);
    
    }
    
    
}

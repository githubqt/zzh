<?php
/**
 * 角色控制方法
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-08
 */
use Admin\RoleModel; 
use Admin\RolePermissionModel; 
use Admin\PermissionModel;
use Admin\AdminModel;
class RoleController extends BaseController 
{

    
    /**
     * 角色列表
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
           $list = RoleModel::getList($info,$page-1,$rows);
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
               //查询是否被绑定
               $val['is_bind'] = '';
               if (AdminModel::getRoleById($val['id'])) {
                   $val['is_bind'] = '1';
               }
           }
           $jsonData['total'] = $list['total'];
		   $jsonData['rows'] = $list['list'];
           echo $this->apiOut($jsonData);
           exit;
       }
       
      
    }
    
    
    /**
     * 添加角色
     * @return boolean
     * @version huangxianguo
     * @time 2018-05-08
     */
    public function addAction()
    {
        $format = $this->_request->get('format');
        if (!empty($format) && $format == "add") {
            $info = $this->_request->get('info');
            $per = $this->_request->get('per');
            if (!$info) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '数据不正确！';
                echo $this->apiOut($jsonData);
                exit;
            }
            if (!$info['name']) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '请输入名称！';
                echo $this->apiOut($jsonData);
                exit;
            }
            
            //添加主信息
           $last_id = RoleModel::addRole($info);
           if (!$last_id) {
               $jsonData['code'] = '500';
               $jsonData['msg'] = '保存失败！';
               echo $this->apiOut($jsonData);
               exit;
           }
           //添加子信息
           if ($per) {
               foreach ($per as $k=>$val) {
                   $data = [];
                   $data['role_id'] = $last_id;
                   $data['permission_id'] = $val;
                   $data['status'] = $info['status'];
                   $addInfo = RolePermissionModel::addRolePermission($data);
                   if ($addInfo == false) {
                       $jsonData['code'] = '500';
                       $jsonData['msg'] = '保存详细失败！';
                       echo $this->apiOut($jsonData);
                       exit;
                   }
               }
           }
           
           $jsonData['code'] = '200';
           $jsonData['msg'] = '保存成功！';
           echo $this->apiOut($jsonData);
           exit;
        }
        
        
      
        $parent = PermissionModel::getParentList(PROJECT_TYPE);
        if ($parent) {
            foreach ($parent as $k=>$val) {
                $child = PermissionModel::getParentTwoList($val['id']);
                if ($child) {
                    foreach ($child as $key=>$value) {
                        if ($value['id']) {
                            $three = PermissionModel::getParentTwoList($value['id']);
                            if ($three) {
                                $child[$key]['childs'] = $three;
                            }
                        }
                    }
                    $parent[$k]['childs'] = $child;
                }
            }
        }
        $this->getView()->assign("parent", $parent);
    }
    
    
    /**
     * 查看角色
     * @return boolean
     * @version huangxianguo
     * @time 2018-05-08
     */
    public function detailAction()
    {
        $id = $this->_request->get('id');
        
        $parent = PermissionModel::getParentList(PROJECT_TYPE);
       
        $permission = RolePermissionModel::getInfoByPermissionId($id);
        
        if ($parent) {
            foreach ($parent as $k=>$val) {
                $parent[$k]['checked'] = '';
                if ($permission) {
                    foreach ($permission as &$per) {
                        if ($val['id'] == $per['permission_id']) {
                            $parent[$k]['checked'] = "checked";
                        }
                    }
                }
                $child = PermissionModel::getParentTwoList($val['id']);
                if ($child) {
                    foreach ($child as $key=>$value) {
                        $child[$key]['checked'] = '';
                        if ($permission) {
                            foreach ($permission as &$per) {
                                if ($value['id'] == $per['permission_id']) {
                                    $child[$key]['checked'] = "checked";
                                }
                            }
                        }
                        if ($value['id']) {
                            $three = PermissionModel::getParentTwoList($value['id']);
                            if ($three) {
                                foreach ($three as $kk=>$vv) {
                                    $three[$kk]['checked'] = '';
                                    if ($permission) {
                                        foreach ($permission as &$per) {
                                            if ($vv['id'] == $per['permission_id']) {
                                                $three[$kk]['checked'] = "checked";
                                            }
                                        }
                                    }
                                }
                                $child[$key]['childs'] = $three;
                            }
                        }
                    }
                    $parent[$k]['childs'] = $child;
                }
            }
        }
        
        $this->getView()->assign("parent", $parent);
        //获取详情
        $data = RoleModel::getInfoById($id);
         
        $this->getView()->assign("data", $data);
    }
    
    
    
    /**
     * 编辑角色
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
            $per = $this->_request->get('per');
            if (!$info) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '数据不正确！';
                echo $this->apiOut($jsonData);
                exit;
            }
            if (!$info['name']) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '请输入名称！';
                echo $this->apiOut($jsonData);
                exit;
            }
    
            //更新主信息
            $last_id = RoleModel::updateByID($info,$id);
            if (!$last_id) {
                $jsonData['code'] = '500';
                $jsonData['msg'] = '编辑失败！';
                echo $this->apiOut($jsonData);
                exit;
            }
            
            //删除子信息
            $delete = RolePermissionModel::deleteByRoleID($id);
            //添加子信息
            if ($per) {
                foreach ($per as $k=>$val) {
                    $data = [];
                    $data['role_id'] = $id;
                    $data['permission_id'] = $val;
                    $data['status'] = $info['status'];
                    $addInfo = RolePermissionModel::addRolePermission($data);
                    if ($addInfo == false) {
                        $jsonData['code'] = '500';
                        $jsonData['msg'] = '编辑详细失败！';
                        echo $this->apiOut($jsonData);
                        exit;
                    }
                }
            }
             
            $jsonData['code'] = '200';
            $jsonData['msg'] = '编辑成功！';
            echo $this->apiOut($jsonData);
            exit;
        }
    
    
    
        $parent = PermissionModel::getParentList(PROJECT_TYPE);
        $permission = RolePermissionModel::getInfoByPermissionId($id);
        if ($parent) {
            foreach ($parent as $k=>$val) {
			    $parent[$k]['checked'] = '';
				 if ($permission) {
					 foreach ($permission as &$per) {
						if ($val['id'] == $per['permission_id']) {
						    $parent[$k]['checked'] = "checked";
						}
					}
				}
                $child = PermissionModel::getParentTwoList($val['id']);
                if ($child) {
                    foreach ($child as $key=>$value) {
                        $child[$key]['checked'] = '';
                        if ($permission) {
                            foreach ($permission as &$per) {
                                if ($value['id'] == $per['permission_id']) {
                                    $child[$key]['checked'] = "checked";
                                }
                            }
                        }
                        if ($value['id']) {
                            $three = PermissionModel::getParentTwoList($value['id']);
                            if ($three) {
                                foreach ($three as $kk=>$vv) {
                                    $three[$kk]['checked'] = '';
                                    if ($permission) {
                                        foreach ($permission as &$per) {
                                            if ($vv['id'] == $per['permission_id']) {
                                                $three[$kk]['checked'] = "checked";
                                            }
                                        }
                                    }
                                }
                                
                                $child[$key]['childs'] = $three;
                            }
                        }
                    }
                    $parent[$k]['childs'] = $child;
                }
            }
        }
        $this->getView()->assign("parent", $parent);
        //获取详情
        $data = RoleModel::getInfoById($id);
       
        $this->getView()->assign("data", $data);
    }
    
    /**
     * 删除角色
     * @return boolean
     * @version huangxianguo
     * @time 2018-05-08
     */
    public function deleteAction()
    {
        $id = $this->_request->get('id');
        
        //删除主信息
        $remove = RoleModel::deleteByID($id);
        if (!$remove) {
            $jsonData['code'] = '500';
            $jsonData['msg'] = '删除失败！';
            echo $this->apiOut($jsonData);
            exit;
        }
        //删除子信息
        $delete = RolePermissionModel::deleteByRoleID($id);
        $jsonData['code'] = '200';
        $jsonData['msg'] = '删除成功！';
        echo $this->apiOut($jsonData);
        exit;
    }
       
  
}

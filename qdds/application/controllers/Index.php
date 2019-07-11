<?php
use Admin\PermissionModel;
use Admin\RolePermissionModel;
use Admin\AdminModel;
class IndexController extends BaseController 
{
   public function indexAction() 
   {
        $this->getView()->assign("content", "欢迎访问扎呵呵！");
        //获取当前登陆人
        $admin_id = AdminModel::getAdminID();
        //获取当前登陆人信息
        $admin_info = AdminModel::getAdminLoginInfo($admin_id);
        //获取一级权限
        $parent = PermissionModel::getParentList(PROJECT_TYPE);
		//加载个人权限
        if (!empty($_REQUEST['format']) && $_REQUEST['format'] == "getAuth") {
            
            $list = [];
            if ($parent) {
                if ($admin_info['role_id'] > '0') {//普通管理员
                    //获取管理员权限
                    $admin_permission = RolePermissionModel::getAuthPermissionRole($admin_info['role_id']);
                    $ids = [];
                    if ($admin_permission) {
                        foreach ($admin_permission as &$permission) {
                            $ids[] = $permission['id'];
                        } 
                        foreach ($parent as $k=>$val) {
                            if ($val['is_show'] == '2') {
                                if (!in_array($val['id'],$ids)) {
                                    unset($parent[$k]);
                                } else {
                                    $list[$k]['id'] = $val['id'];
                                    $list[$k]['title'] = $val['name'];
                                    $list[$k]['icon'] = $val['tib'];
                                    $child = PermissionModel::getParentTwoList($val['id']);
                                    if ($child) {
                                        $childer = [];
                                        foreach ($child as $key=>$value) {
                                            if ($value['is_show'] == '2') {
                                                if (!in_array($value['id'],$ids)) {
                                                    unset($child[$key]);
                                                } else {
                                                    $childer[$key]['id'] = $value['id'];
                                                    $childer[$key]['title'] = $value['name'];
                                                    $childer[$key]['href'] = '/index.php?m='.$value['modules'].'&c='.$value['method'].'&a='.$value['action'];
                                                    $childer[$key]['icon'] = $value['tib'];
                                                }
                                            } else {
                                                unset($child[$key]);
                                            }
                                        }
                                        $list[$k]['childs'] = $childer;
                                    }
                                }
                            } else {
                                unset($parent[$k]);
                            }
                            
                        }
                    }
                } else {//超级管理员
                    foreach ($parent as $k=>$val) {
                        if ($val['is_show'] == '2') {
                            $list[$k]['id'] = $val['id'];
                            $list[$k]['title'] = $val['name'];
                            $list[$k]['icon'] = $val['tib'];
                            $child = PermissionModel::getParentTwoList($val['id']);
                            if ($child) {
                                $childer = [];
                                foreach ($child as $key=>$value) {
                                    if ($value['is_show'] == '2') {
                                        $childer[$key]['id'] = $value['id'];
                                        $childer[$key]['title'] = $value['name'];
                                        $childer[$key]['href'] = '/index.php?m='.$value['modules'].'&c='.$value['method'].'&a='.$value['action'];
                                        $childer[$key]['icon'] = $value['tib'];
                                    } else {
                                        unset($child[$key]);
                                    }
                                }
                                $list[$k]['childs'] = $childer;
                            }
                        } else {
                            unset($parent[$k]);
                        }
                    }
                }
            }
            
			$menu = array (
				'title'=>' 设备平台',
				'menu'=>$list
			);
			
           $jsonData['code'] = '200';
           $jsonData['msg'] = '获取成功！';
		   $jsonData['menu'] = $menu;
		   
           echo $this->apiOut($jsonData);
           exit;	
       }	
       $this->getView()->assign("admin", $admin_info);
       $this->getView()->assign("parent", $parent);
   }
	//无权限页面
   public function noperAction() 
   {
   	
   }
}

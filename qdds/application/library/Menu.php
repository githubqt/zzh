<?php

/** 
 * 用于控制权限
 * @version v0.01
 * @package service\auth\MenuService
 * @author laiqingtao<laiqingtao@houhouyun.com>
 * @time 2016-11-07
 */
class Menu
{		
	/*未登录可以使用*/
	const NO_LOGIN_PER = array(	 	   
	    'Auth' => array(
	        'Login'=>'*',
	        'Permission'=>array(
	            'list',
	            'add',
	            'GetTop'
	        )
	    ),
	    'Index' => array(
	        'Index'=>array(
	            'noper'
	        )
	    ),
	    'Weixin' => array(
	        'Weixin'=>array(
	            'callbackr',
	            'detailGraphic'
	        )
	    )
	);
	
	/*登录后无需权限可使用.*/
	const WHILE_LIST_PER = array(
	    'Index' => '*',
	    'Public' => '*',
	    'Auth' => array(
	        'Login'=>'*',
	        'Auth'=>'*',
	        'Permission'=>array(
	            'list',
	            'add',
	            'GetTop'
	        )
	    )
	);
	
    /**
	 * 检测用户是否存在该权限
	 * @param array $adminInfo 
	  * 		 个人信息
	 * @param string $controller 
	  * 		 模块名称一般对应Action名称 
	 * @param string $action        
	 *      操作权限一般对应Action实际的方法
	 * @return bool 
	 */
   	public static function checkPER($adminInfo,$module,$controller,$action)
	{		
		/*白名单设置*/
		if (TRUE == self::checkWhiteList($module,$controller,$action)) return TRUE;
			
		$permisssionModel = new \Admin\PermissionModel;
		$RolePermisssionModel = new \Admin\RolePermissionModel;
		/*判断是否有权限*/
		if ($adminInfo['role_id'] == '0') {
		    /* 加载所有可用权限 */
		    $authPermissionListAll = $permisssionModel::GetAllList(PROJECT_TYPE);
		} else {
		    /* 加载所在组权限 */
		    $authPermissionListAll = $RolePermisssionModel::getAuthPermissionRole($adminInfo['role_id']);
		}
		
		foreach ($authPermissionListAll as $key => $value) {
		    if($value['modules'] == $module && $value['method'] == $controller && $value['action'] == $action){
		        return TRUE;
		    }
		}
		return FALSE;
	}

	/**
	 * 未登录项检测方法
	 *
	 * @param string $res 
	  * 		 模块名称一般对应Action名称 
	 * @param string $priv
	  *      操作权限一般对应Action实际的方法
	 * @return bool
	 */
	public static function checkNoLogin($module,$controller,$action)
	{		
		if (!empty($module)&& !empty($controller) && !empty($action)) {
			$no_login = self::NO_LOGIN_PER;
			if (sizeof($no_login) >0 ) {
			    if(isset($no_login[$module])){
			        if ($no_login[$module] == '*') {
			            return TRUE;
			        } else {
        				if(isset($no_login[$module][$controller])){
        					if ($no_login[$module][$controller] == '*') {
        						return TRUE;
        					} else {
        						if(is_array($no_login[$module][$controller]) && count($no_login[$module][$controller]) > 0){
        							if(in_array($action,$no_login[$module][$controller])){
        								return TRUE;
        							}
        						}						
        					}
        				}	
        			}
        		}
			}
		}
		return FALSE;
	}
	
	/**
	 * 白名单检测方法
	 *
	 * @param string $res 
	  * 		 模块名称一般对应Action名称 
	 * @param string $priv
	  *      操作权限一般对应Action实际的方法
	 * @return bool
	 */
    public static function checkWhiteList($module,$controller,$action)
    {		
        if (!empty($module) &&!empty($controller) && !empty($action)) {
        	$white_list = self::WHILE_LIST_PER;
        	if (sizeof($white_list) >0 ) {
        	    if(isset($white_list[$module])){
        	        if ($white_list[$module] == '*') {
        	            return TRUE;
        	        } else {
        	            if(isset($white_list[$module][$controller])){
        	                if ($white_list[$module][$controller] == '*') {
        	                    return TRUE;
        	                } else {
        	                    if(is_array($white_list[$module][$controller]) && count($white_list[$module][$controller]) > 0){
        	                        if(in_array($action,$white_list[$module][$controller])){
        	                            return TRUE;
        	                        }
        	                    }
        	                }
        	            }
        	        } 
                }
        	}
        }
    	return false;
    }
}
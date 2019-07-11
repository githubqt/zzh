<?php
use Admin\AdminModel;
/***
 * 管理员后台
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-04
 */
class AuthController extends BaseController 
{
       public function indexAction() 
       {
			$this->getView()->assign("content", "欢迎访问扎呵呵！");
       }
}

<?php
use Admin\AdminModel;
use Admin\LoginLogModel;
use Common\CommonBase;
/**
 * 管理员登陆控制方法
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-04
 */
class LoginController extends SimpleBaseController 
{
    private $_login = 'FGRTYUSDS';
       
    
    public function init()
	{
		if (AdminModel::isLogin()) {
    		/* 未登陆跳转登陆页 */
     		header('Location: /index.php?m=Index&c=Index&a=index');	
        }
	}
    
    
    /**
     * 用户登录
     * @return boolean
     * @version huangxianguo 
     * @time 2018-05-04
     */
    public function loginAction() 
    {
       session_start();
       if (!empty($_REQUEST['format']) && $_REQUEST['format'] == "login") {
       	   $code = isset($_REQUEST['code']) ? trim($_REQUEST['code']) : '';
           $name = isset($_REQUEST['name']) ? trim($_REQUEST['name']) : '';
           $password = isset($_REQUEST['password']) ? trim($_REQUEST['password']) : '';
           $code_char = isset($_REQUEST['code_char']) ? trim($_REQUEST['code_char']) : '';
           $jsonData = [];

		   if (empty($code)) {
               $jsonData['code'] = '500';
               $jsonData['msg'] = '请输入商户号';
               echo $this->apiOut($jsonData);
               exit;
           }
		   
           if (empty($name)) {
               $jsonData['code'] = '500';
               $jsonData['msg'] = '请输入用户名';
               echo $this->apiOut($jsonData);
               exit;
           }
		   
           if (empty($password)) {
               $jsonData['code'] = '500';
               $jsonData['msg'] = '请输入密码';
               echo $this->apiOut($jsonData);
               exit;
           }
           
           if (CommonBase::getBigTosmall($code_char) != $_SESSION["FGRTYUSDSCODE"]) {
               $jsonData['code'] = '501';
               $jsonData['msg'] = '验证码输入错误~';
               echo $this->apiOut($jsonData);
               exit;
           }	
           $user = AdminModel::checkUserId($code,$name);
           if (!$user) {
               $jsonData['code'] = '500';
               $jsonData['msg'] = '该用户不存在！';
               echo $this->apiOut($jsonData);
               exit;
           }
           $login = AdminModel::login($code,$user,$password);
           if ($login == false) {
               $jsonData['code'] = '500';
               $jsonData['msg'] = '密码错误！';
               echo $this->apiOut($jsonData);
               exit;
           }
           //记录登陆日志
           $num = LoginLogModel::getLoginNum($user['id']);
           $public = new Publicb();
           $arr = array(
               'admin_id' => $user['id'],
               'login_at' => time(),
               'num' => $num + 1,
               'ip' => $public->GetIP()
           );
           if (LoginLogModel::addLogin($arr) == false) {
               $jsonData['code'] = '500';
               $jsonData['msg'] = '记录登陆日志失败！';
               echo $this->apiOut($jsonData);
               exit;
           }
           
           //记录登陆信息进cookie
           $adminID = json_encode($user['id']);
           if (isset($_SERVER['HTTP_HOST'])) {
               setcookie($this->_login,$adminID,time()+8*60*60,"/",$_SERVER['HTTP_HOST']);
           }

           //删除强制退出memcache
           $mem = \Custom\YDLib::getMem ( 'memcache' );
           $data = $mem->get (ADMIN_FORCED_RETURN.$user['id']);
           if ($data) {
               $mem->delete (ADMIN_FORCED_RETURN.$user['id']);
           }
           
           $jsonData['code'] = '200';
           $jsonData['msg'] = '登陆成功！';
           echo $this->apiOut($jsonData);
           exit;
       }
       AdminModel::signout();
    }
       
   
   
   /* 获取登陆验证码 */
   public function codeAction()
   {
   		Yaf_Dispatcher::getInstance()->disableView();
      	$public = new Publicb();
       	return $public->getCode(4,110,42,'FGRTYUSDSCODE');
   }
}

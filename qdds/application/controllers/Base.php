<?php
/**
 * 基础控制器
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-04
 */

use Admin\AdminModel;

class BaseController extends Yaf_Controller_Abstract
{

    public function init()
    {
        //获取面包屑导航
        $this->menu();
        $this->checkRole();
    }


    /**
     * 检测登陆和权限
     */
    public function checkRole()
    {
        //print_r($this->getRequest()->getMethod());die;
        $module = $this->_request->get('m');
        $controller = $this->_request->get('c');
        $action = $this->_request->get('a');

        //主页：Index，index，index
        //登陆页：Auth，Login，login
        $menu = new Menu();
        /* 检测无需登陆的权限 */
        if (!$menu->checkNoLogin($module, $controller, $action)) {
            if (AdminModel::isLogin()) {
                $adminID = AdminModel::getAdminID();

                /* 加载个人信息 */
                $adminInfo = AdminModel::getAdminLoginInfo($adminID);

                $conntent = '';
                /* 添加操作日志 */
                //$this->addOpLog($conntent);

                if ($menu->checkPER($adminInfo, $module, $controller, $action)) {
                } else {
                    header('Location: /index.php?m=Index&c=Index&a=noper');
                    exit();
                }
            } else {
                /* 未登陆跳转登陆页 */
                header('Location: /index.php?m=Auth&c=login&a=login');
                exit();
            }
        }
    }

    /**
     * 获取面包屑导航
     */
    public function menu()
    {
        $is_menu = $this->_request->get('is_menu');
        $is_menu = isset($is_menu)?$is_menu:'2';

        $menu = [];
        $menu_html = '';
        if ($is_menu == '2') {

            $modules = $this->_request->get('m');
            $method = $this->_request->get('c');
            $action = $this->_request->get('a');
            $per = \Admin\PermissionModel::findOneWhere(array('modules' => $modules, 'method' => $method, 'action' => $action, 'type' => 5));
            $menu[] = $per;
            while ($per && $per['parent'] != '0') {
                $per = \Admin\PermissionModel::findOneWhere(array('id' => $per['parent'], 'type' => 5));
                $menu[] = $per;
            }

            if (is_array($menu) && count($menu) == 3) {
                $menu_html =
                    '<div class="panel-header">
                    <div class="panel-title">
                        <i class="icon Hui-iconfont"></i>
                        ' . $menu[2]["name"] . '->
                        <a style="text-decoration: none;" href="/index.php?m=' . $menu[1]["modules"] . '&c=' . $menu[1]["method"] . '&a=' . $menu[1]["action"] . '">                        
                            ' . $menu[1]["name"] . '
                        </a>
                        ->' . $menu[0]["name"] . '
                    </div>
                </div>';
            }
        }
        $this->getView()->assign("menu_html", $menu_html);
    }

    /**
     * 添加操作日志
     * @param $conntent 日志内容
     */
    public function addOpLog($conntent)
    {

        $controller = $this->_request->getQuery("c");
        $action = $this->_request->getQuery("a");

        $adminID = $this->_adminService->getAdminID();
        $adminInfo = $this->_adminService->getInfoByID($adminID);

        $data = array(
            "admin_id" => $adminID,
            "admin_name" => $adminInfo['fullname'],
            "url" => $this->_request->getHttpHost() . $this->_request->getURI(),
            "controller" => $controller,
            "action" => $action,
            "op_ip" => $this->_pub->GetIP(),
            "op_at" => date("Y-m-d H:i:s"),
            "`type`" => $this->_request->getMethod(),
            "content" => $conntent
        );

        $this->_authDoLogService->addData($data);

    }

    /**
     * 定义api输出 当前版本支持输出json格式
     * @param array $data
     * @return json
     */
    public function apiOut($data = array())
    {

        //if(APP_API_OUT_TYPE == 'json'){
        if (!empty($_REQUEST['jsonpcallback'])) {
            return $_REQUEST['jsonpcallback'] . "(" . json_encode($data) . ")";
        } else {
            header('Content-type:application/json');
            return json_encode($data);
        }

        /* }elseif(APP_API_OUT_TYPE == 'xml'){
            header('Content-Type:text/xml');
            $this->_loader->loaderClass('array2xml');
            $xmldom = new array2xml($data);
            return $xmldom->getXml();
        } */
    }

    public function pushTask($handleClass, $handleFunc, $taskId, $params)
    {
        //启动导入任务
        $paramInfo = [
            'domain' => SERVER_DB_DBNAME,
            'taskType' => 'downLoads',
            'class' => $handleClass,
            'func' => $handleFunc,
            'id' => $taskId,
            'search' => json_encode($params)
        ];
        try {
//            $this->_redisModel->
            $this->_redisModel->rPush(DOMAIN_GEARMAN_NAME, $paramInfo);
        } catch (Exception $e) {

            return false;
        }


        return true;
    }


    public function downloadResp($info, $fileName, $module, $controller, $action)
    {
        if ($id = $this->AdminService->getAdminID()) {
            $adminInfo = $this->AdminService->getInfoByID($id);
            if ($taskId = $this->DownloadService->addData([
                'name' => $fileName,
                'filename' => '',
                'module' => $module,
                'ctl' => $controller,
                'act' => $action,
                'ext' => 'zip',
                'admin_id' => $id,
                'admin_name' => $adminInfo['fullname']
            ])
            ) {
                if ($this->pushTask($controller, $action, $taskId, [
                    'params' => $info,
                    'adminInfo' => $adminInfo,
                    'filename' => $fileName
                ])
                ) {
                    $jsonData = [
                        'errno' => 0,
                        'errmsg' => '任务添加成功'
                    ];
                    echo $this->apiOut($jsonData);
                    exit;
                }
            }
        }

        $jsonData = [
            'errno' => -1,
            'errmsg' => '任务添加失败'
        ];
        echo $this->apiOut($jsonData);
        exit;
    }


    /**
     * 响应成功提示 状态码 200
     * @param string $msg 消息提示
     * @param array $data 返回的数据
     */
    public function success($msg = '操作成功', array $data = [])
    {
        return $this->response(json_encode([
            'msg' => $msg,
            'code' => 200,
            'data' => $data
        ]));
    }

    /**
     * 响应错误提示 状态码 500
     * @param string $msg 消息提示
     * @param array $data 返回的数据
     */
    public function error($msg = '操作失败', array $data = [])
    {
        return $this->response(json_encode([
            'msg' => $msg,
            'code' => 500,
            'data' => $data
        ]));
    }

    /**
     * 响应grid结果
     * @param array $data
     */
    public function result(array $data = [])
    {
        return $this->response(json_encode($data));
    }

    /**
     * json响应
     * @param $data
     */
    /**
     * json响应
     * @param $data
     */
    public function response($data = '')
    {
        if (!empty($_REQUEST['jsonpcallback'])) {
            echo $_REQUEST['jsonpcallback'] . "(" . json_encode(json_decode($data)) . ")";
            exit();
        } else {
            $response = new \Yaf_Response_Http();
            $response->setHeader('Content-Type', 'application/json');
            $response->setBody($data);
            $response->response();
        }
        exit();
    }
}

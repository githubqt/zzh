<?php
/**
 * 属性控制方法
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-09
 */
use Attribute\AttributeModel; 
use Attribute\AttributeValueModel;
class AttributeController extends BaseController 
{

    
    /**
     * 属性列表
     * @return boolean
     * @version huangxianguo 
     * @time 2018-05-08
     */
    public function listAction() 
    {
        if (!empty($_REQUEST['format']) && $_REQUEST['format'] == "list") {
           $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '';
           $rows = isset($_REQUEST['rows']) ? trim($_REQUEST['rows']) : '';
           $info['sort'] = isset($_REQUEST['sort']) ? trim($_REQUEST['sort']) : '';
           $info['order'] = isset($_REQUEST['order']) ? trim($_REQUEST['order']) : '';
           if (!empty($_REQUEST['info'])) {
              $info['info'] = $_REQUEST['info'];
           }
           $jsonData = [];
           $list = AttributeModel::getList($info,$page-1,$rows);
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
               $val['values'] = AttributeValueModel::getInfoByAttributeId($val['id']);
           }
           $jsonData['total'] = $list['total'];
		   $jsonData['rows'] = $list['list'];
           echo $this->apiOut($jsonData);
           exit;
       }
       
      
    }
    
    
    /**
     * 添加属性
     * @return boolean
     * @version huangxianguo
     * @time 2018-05-08
     */
    public function addAction()
    {
        $format = $this->_request->get('format');
        if (!empty($format) && $format == "add") {
            $info = $this->_request->get('info');
            $items = $this->_request->get('items');
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
           $last_id = AttributeModel::addData($info);
           if (!$last_id) {
               $jsonData['code'] = '500';
               $jsonData['msg'] = '保存失败！';
               echo $this->apiOut($jsonData);
               exit;
           }
           //添加子信息
           if ($items && $info['input_type'] != '3') {
               foreach ($items as $k=>$val) {
                   $val = json_decode($val,true);
                   $data = [];
                   $data['value'] = $val['value'];
                   $data['value_alias'] = $val['value_alias'];
                   $data['attribute_id'] = $last_id;
                   $data['sort'] = $k;
                   $addInfo = AttributeValueModel::addData($data);
                   if ($addInfo == false) {
                       $jsonData['code'] = '500';
                       $jsonData['msg'] = '保存属性失败！';
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
        
        
      
        
    }
    
    
    /**
     * 查看属性
     * @return boolean
     * @version huangxianguo
     * @time 2018-05-08
     */
    public function detailAction()
    {
        $id = $this->_request->get('id');
        
        //获取详情
        $data = AttributeModel::getInfoById($id);
        $data['info'] = AttributeValueModel::getInfoByAttributeId($id);
        $this->getView()->assign("data", $data);
    }
    
    
    
    /**
     * 编辑属性
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
            $items = $this->_request->get('items');
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
           $last_id = AttributeModel::updateByID($info,$id);
           if (!$last_id) {
               $jsonData['code'] = '500';
               $jsonData['msg'] = '编辑失败！';
               echo $this->apiOut($jsonData);
               exit;
           }
           
           //删除子信息
           $delete = AttributeValueModel::deleteByAttributeID($id);
           //添加子信息
           if ($items && $info['input_type'] != '3') {
               foreach ($items as $k=>$val) {
                   $val = json_decode($val,true);
                   $data = [];
                   $data['value'] = $val['value'];
                   $data['value_alias'] = $val['value_alias'];
                   $data['attribute_id'] = $id;
                   $data['sort'] = $k;
                   $addInfo = AttributeValueModel::addData($data);
                   if ($addInfo == false) {
                       $jsonData['code'] = '500';
                       $jsonData['msg'] = '编辑属性失败！';
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
    
    
    
        //获取详情
        $data = AttributeModel::getInfoById($id);
        $data['info'] = AttributeValueModel::getInfoByAttributeId($id);
        $this->getView()->assign("data", $data);
    }
    
    /**
     * 删除属性
     * @return boolean
     * @version huangxianguo
     * @time 2018-05-08
     */
    public function deleteAction()
    {
        $id = $this->_request->get('id');
        
        //删除主信息
        $remove = AttributeModel::deleteByID($id);
        if (!$remove) {
            $jsonData['code'] = '500';
            $jsonData['msg'] = '删除失败！';
            echo $this->apiOut($jsonData);
            exit;
        }
        //删除子信息
        $delete = AttributeValueModel::deleteByAttributeID($id);
        $jsonData['code'] = '200';
        $jsonData['msg'] = '删除成功！';
        echo $this->apiOut($jsonData);
        exit;
    }
       
  
}

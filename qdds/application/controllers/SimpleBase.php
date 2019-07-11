<?php
/** 
 * 基础控制器
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-04
 */
class SimpleBaseController extends Yaf_Controller_Abstract 
{
	public function init()
    {
    	
    }
	
    /**
     * 定义api输出 当前版本支持输出json格式
     * @param array $data
     * @return json
     */
    public function apiOut($data=array())
    {
        //if(APP_API_OUT_TYPE == 'json'){
            if (!empty($_REQUEST['jsonpcallback'])) {
				return $_REQUEST['jsonpcallback']."(".json_encode($data).")";
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
}

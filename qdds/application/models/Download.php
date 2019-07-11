<?php
/**
 * 下载
 * @version v0.01
 * @author lqt
 * @time 2018-07-20
 */

use Custom\YDLib;
use Core\Csv;
use Admin\AdminModel;
 
class DownloadModel extends \Common\CommonBase 
{
	
	const DOWNLOAD_ROWS = 1000;	
	
	/**
	 * 添加下载任务
     * @param obj  $model 获取数据的对象
     * @param array $search 搜索条件
     * @param array $fileds 下载字段
     * @param array $filename 文件名称
	 * */
	public static function posdownload($model,$search,$fileds,$filename)
	{
        set_time_limit(0);
		
		$user_id = AdminModel::getAdminID();
		$user_info = AdminModel::getAdminLoginInfo($user_id);
		$supplier_id = $user_info['supplier_id'] ?? '0';
		
		$genName = $filename.$user_id;
		
        $mem = YDLib::getMem('memcache');
        $mem->set('posdownload_'.$genName,'1');		

		list($filename, $handle) = Csv::genCSVFile($supplier_id,$genName);
		//表头
        Csv::putDataCSV(array_values($fileds), false, $handle);
        
        //总数量
        $total = $model::getList($search, 0, 0);
		
		//分页查询
        $page = floor($total['total'] / self::DOWNLOAD_ROWS);
        for ($i = 0; $i <= $page; $i++) {
        	$data = $model::getList($search, $i, self::DOWNLOAD_ROWS);
            Csv::putDataCSV($data['list'], array_keys($fileds), $handle);
        }
		
        fclose($handle);
		
		$mem->delete('posdownload_'.$genName);		
    }
			
	/**
	 * 是否生成成功
     * @param array $filename 文件名称
	 * */
	public static function progress($filename)
	{		
		$user_id = AdminModel::getAdminID();
		$genName = $filename.$user_id;
		
        $mem = YDLib::getMem('memcache');
        $res = $mem->get('posdownload_'.$genName);		
		if ($res) {
			return FALSE;
		} else {
			return TRUE;
		}
    }
	
	/**
	 * 下载文件
     * @param array $filename 文件名称
	 * */
	public static function download($filename)
	{
		set_time_limit(0);
		ini_set('memory_limit', '1024M');
		
		$user_id = AdminModel::getAdminID();
		$user_info = AdminModel::getAdminLoginInfo($user_id);
		$supplier_id = $user_info['supplier_id'] ?? '0';
		
		$genName = $filename.$user_id;
		$fpath = Csv::genCSVFileName($supplier_id,$genName);	
		
		if (file_exists($fpath)) {
			$file_pathinfo = pathinfo($fpath);  
			$file_name = $file_pathinfo['basename'];  
			$file_extension = $file_pathinfo['extension'];  
			$handle = fopen($fpath,"rb");  
			if (FALSE === $handle) {  
			    exit("Failed to open the file");  
			}
			$filesize = filesize($fpath); 
	 
		
		    header("Content-Type: application/force-download");
	        header("Content-Type: application/octet-stream");
	        header("Content-Type: application/download");
	      
	        header("Accept-Ranges:bytes");  
			header("Accept-Length:".$filesize);  
			header('Content-Disposition:inline;filename="'.$filename.'.csv"');
			  
			$contents = '';  
			while (!feof($handle)) {  
			    $contents = fread($handle, 8192);  
			    echo $contents;  
			    @ob_flush();  //把数据从PHP的缓冲中释放出来  
			    flush();      //把被释放出来的数据发送到浏览器  
			}  
			fclose($handle);  
			exit; 			 
		}
		exit;		
    }			
}
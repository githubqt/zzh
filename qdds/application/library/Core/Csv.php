<?php
/**
 * CSV
 * @package library
 * @subpackage Core
 * @author 赖清涛 <laiqingtao@zhahehe.com>
 */

namespace Core;

class Csv
{
	/**
	 * 生产csv文件
	 * @param $domain
	 * @param $genName
	 * @return string
	 */
	public static function genCSVFile($type, $genName) {
	    $dir = self::genCSVFilePath($type);
	    if (!file_exists($dir)) {
	        mkdir($dir, 0777, TRUE);
	    }
		
	    $filename = self::genCSVFileName($type,$genName);
	    $handle = fopen($filename, "w");
	    return [
	        $filename,
	        $handle
	    ];
	}
	
	/**
	 * 获取csv文件路径
	 * @param $domain
	 * @param $genName
	 * @return string
	 */
	public static function genCSVFilePath($type) {
	    return APPLICATION_PATH . "/../data/qdds/csv/" . $type . "/". date("Y") . "/". date("m") . "/";
	}
	
	/**
	 * 获取csv文件
	 * @param $domain
	 * @param $genName
	 * @return string
	 */
	public static function genCSVFileName($type,$genName) {
	    return self::genCSVFilePath($type) . $genName. ".csv";
	}
	
	public static function putDataCSV($content, $filters, $handle) {
	    $container = [];
	    $rowNum = 1;
	    foreach($content as $v) {
	        if ($filters) {
	            $container = [];
	            foreach($filters as $filter) {
	                list($fk, $rule) = explode('|', $filter);
	                switch ($rule) {
	                    case 'transScience':
	                        $container[] = iconv("utf-8", "gbk", strip_tags($v[$fk]) . "\t");
	                        break;
	                    case 'rowNum':
	                        $container[] = $rowNum;
	                        break;
	                    case 'blank':
	                        $container[] = '';
	                        break;
	                    default:
	                        if (strstr($rule, ':')) {
	                            list($tp, $default) = explode(':', $rule);
	                            switch ($tp) {
	                                case 'default':
	                                    $container[] = iconv("utf-8", "gbk", $default);
	                                    break;
	                                case 'concat':
	                                    $concatStr = '';
	                                    $fields = explode(',', $default);
	                                    array_unshift($fields, $fk);
	                                    foreach($fields as $f) {
	                                        $concatStr .= iconv('utf-8', 'gbk', $v[$f] . '/');
	                                    }
	                                    $container[] = rtrim($concatStr, '/');
	                                    break;
	                            }
	                            break;
	                        }
	                        $container[] = iconv("utf-8", "gbk", strip_tags($v[$fk]));
	                        break;
	                }
	            }
	            fputcsv($handle, $container);
	            unset($container);
	        } else {
	            list($fk, $rule) = explode('|', $v);
	            switch ($rule) {
	                case 'transScience':
	                    $container[] = iconv("utf-8", "gbk", strip_tags($fk) . "\t");
	                    break;
	                case 'rowNum':
	                    $container[] = $rowNum;
	                    break;
	                case 'blank':
	                    $container[] = '';
	                    break;
	                default:
	                    $container[] = iconv("utf-8", "gbk", strip_tags($fk) . "\t");
	                    break;
	            }
	        }
	        $rowNum++;
	    }
	    if ($container) {
	        fputcsv($handle, $container);
	        unset($container);
	    }
	}

    //读取csv文件制定行数（行区间）
    public static function getCSVLine($file_name, $line_star,  $line_end)
    {
        $n = 0;
        $handle = fopen($file_name,"r");
        if ($handle) {
            while (!feof($handle)) {
                ++$n;
                $out = fgets($handle);
                if($line_star <= $n){
                    $ling[] = $out;
                }
                if ($line_end == $n) break;
            }
            fclose($handle);
        }
        if( $line_end==$n) return $ling;
        return false;
    }

	
}	

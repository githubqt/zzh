<?php
/**
 * 上海金价接口
 *
 * @package library
 * @subpackage Core
 * @author 赖清涛 <laiqingtao@zhahehe.com>
 *
 * 黄金数据调用示例代码 － 聚合数据
 * 在线接口文档：http://www.juhe.cn/docs/29
 */

namespace Core;

use Custom\YDLib;

class GoldPrice
{
    //************1.上海黄金交易所************
    static $_shgold_url = "http://web.juhe.cn:8080/finance/gold/shgold";
    //************2.上海期货交易所************
    static $_shfuture_url = "http://web.juhe.cn:8080/finance/gold/shfuture";
    //************3.银行账户黄金************
    static $_bankgold_url = "http://web.juhe.cn:8080/finance/gold/bankgold";

    //************配置您申请的appkey************
    static $_appkey = GOLDPRICE_APPKEY;

    /**
     * 获取上海金价接口
     * @return integer
     */
    public static function getGoldPrice()
    {
        $redis = YDLib::getRedis('redis','w');
        $result = $redis->get('GoldPrice_'.date('Y-m-d-H'));
        if (!$result) {
            $result = self::searchGoldPrice();
        } else {
            $result = json_decode($result,true);
        }

        if (!$result) {
            $result_list = $redis->keys('GoldPrice_*');
            if ($result_list) {
                $keys = $result_list[0];
                $result = $redis->get($keys);
            }
        }
        if ($result) {
            $redis->setex('GoldPrice_'.date('Y-m-d-H'), 180000, json_encode($result));//2天+2小时
        }

        if (isset($result['result'][0]['Au99.99']['latestpri'])) {
            return $result['result'][0]['Au99.99']['latestpri'];
        }
        return 273;
    }

    /**
     * 查询上海金价接口
     * @return integer
     */
    public static function searchGoldPrice()
    {
        $params = array(
            "key" => self::$_appkey,//APP Key
            "v" => "1",//JSON格式版本(0或1)默认为0
        );
        $url = self::$_shgold_url;

        $paramstring = http_build_query($params);
        $content = self::juhecurl($url,$paramstring);
        $result = json_decode($content,true);
        !$result and YDLib::testLog($result,'GoldPrice');

        if($result && $result['error_code']=='0'){
            return $result;
        }

        return false;
    }

    /**
     * 请求接口返回内容
     * @param  string $url [请求的URL地址]
     * @param  string $params [请求的参数]
     * @param  int $ipost [是否采用POST形式]
     * @return  string
     */
    public static function juhecurl($url,$params=false,$ispost=0)
    {
        $httpInfo = array();
        $ch = curl_init();

        curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
        curl_setopt( $ch, CURLOPT_USERAGENT , 'JuheData' );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 60 );
        curl_setopt( $ch, CURLOPT_TIMEOUT , 60);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if( $ispost ) {
            curl_setopt( $ch , CURLOPT_POST , true );
            curl_setopt( $ch , CURLOPT_POSTFIELDS , $params );
            curl_setopt( $ch , CURLOPT_URL , $url );
        } else {
            if($params){
                curl_setopt( $ch , CURLOPT_URL , $url.'?'.$params );
            }else{
                curl_setopt( $ch , CURLOPT_URL , $url);
            }
        }
        $response = curl_exec( $ch );
        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
        $httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
        curl_close( $ch );
        return $response;
    }

}
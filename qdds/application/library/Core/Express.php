<?php
/**
 * 快递接口
 *
 * @package library
 * @subpackage Core
 * @author 赖清涛 <laiqingtao@zhahehe.com>
 */

namespace Core;

class Express
{
    const STATES = [
        0 => '在途中',
        1 => '已揽收',
        2 => '疑难',
        3 => '已签收',
        4 => '退签',
        5 => '派件中',
        6 => '退回',
    ];
    /** 快递查询地址 */
    protected $url = 'http://poll.kuaidi100.com/poll/query.do';

    /**
     * 封装查询数据
     * @param array $param
     * @return string
     */
    public function paramUrl($param)
    {
        $post_data = array();
        $post_data["customer"] = CUSTOMER_ID_100;
        $key = KEY_100;
        $post_data["param"] = $param;

        $post_data["sign"] = md5($post_data["param"] . $key . $post_data["customer"]);
        $post_data["sign"] = strtoupper($post_data["sign"]);
        $o = "";
        foreach ($post_data as $k => $v) {
            $o .= "$k=" . urlencode($v) . "&";        //默认UTF-8编码格式
        }
        $post_data = substr($o, 0, -1);
        return $post_data;
    }

    /**
     * 查询对应的快递公司信息
     * @param string $com 对应的快递标识
     * @param string $num 快递号
     * @return array
     */
    function searchExpress($com, $num)
    {
        $param = array();
        $param['com'] = $com; //'shunfeng';
        $param['num'] = $num; //'957485536518';
        $postData = $this->paramUrl(json_encode($param));
        return $this->searchForHttp($postData);
    }

    /**
     * 查询快递100接口
     * @param string $post_data
     * @return array
     */
    public function searchForHttp($post_data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        if (strlen($this->url) > 5 && strtolower(substr($this->url, 0, 5)) == "https") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        //$headerparm = array('Content-type: application/json','charset=utf-8');
        //curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headerparm );
        //curl_setopt($ch, CURLOPT_USERAGENT, _USERAGENT_);
        //curl_setopt($ch, CURLOPT_REFERER,_REFERER_);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $r = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch), 0);
        } else {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode) {
                throw new \Exception($r, $httpStatusCode);
            }
        }
        curl_close($ch);

        $data = json_decode($r, TRUE);
        return $data;
    }

}	

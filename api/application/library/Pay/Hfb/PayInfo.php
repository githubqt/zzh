<?php

namespace Pay\Mini;

use Pay\PayFactory;

/**
 * 微信支付基类
 * 
 * @version v0.1
 * @author zhaoyu
 *         @time 2018-5-23
 */
class PayInfo extends PayAbstract {
	protected $_pay = NULL;
	function __construct() {
		parent::__construct ();
		$this->_loader->loaderClass ( "Payment" );
		if ($this->_pay == NULL) {
			$this->_pay = Payment::factory ( strtolower ( str_replace ( "Service", "", str_replace ( "Payment", "", __CLASS__ ) ) ) );
		}
	}
	
	/**
	 * 异步消息返回
	 * 
	 * @param array $data
	 *        	表字段名作为key的数组
	 * @return bool
	 */
	public function notifyPay() {
		curlLog ( $this->_pay );
		$this->_pay->notify ();
	}
	
	/**
	 * 同步消息返回
	 * 
	 * @param int $id
	 *        	表自增ID
	 */
	public function callbackPay() {
	}
	
	/**
	 * 退款操作
	 * 
	 * @return boolean 更新结果
	 */
	public function returnPay() {
	}
	
	/**
	 * 创建支付操作
	 * 
	 * @param array $payinfo
	 *        	['title' => '微信支付','body' => '某某某微信支付','money' =>0.01]
	 * @return
	 *
	 */
	public function createPay($payinfo) {
		return $this->_pay->create ( $payinfo );
	}
	
	/**
	 * 获得对应的openId
	 * 
	 * @return array
	 */
	public function getOpenId() {
		return $this->_pay->getOpenId ();
	}
}
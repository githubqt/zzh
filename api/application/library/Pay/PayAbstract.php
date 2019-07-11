<?php

namespace Pay;

/**
 * 支付 Abstract
 * 
 * @version v0.1
 * @author zhaoyu
 *         @time 2018-5-23
 */
interface PayAbstract {
	
	/**
	 * 异步消息返回
	 * 
	 * @param array $data
	 *        	表字段名作为key的数组
	 * @return bool
	 */
	public function notifyPay();
	
	/**
	 * 同步消息返回
	 * 
	 * @param int $id
	 *        	表自增ID
	 */
	public function callbackPay();
	
	/**
	 * 退款操作
	 * 
	 * @return boolean 更新结果
	 */
	public function returnPay($payinfo);
	
	/**
	 * 创建支付操作
	 * 
	 * @param array $payinfo
	 *        	['title' => '微信支付','body' => '某某某微信支付','money' =>0.01]
	 * @return
	 *
	 */
	public function createPay($payinfo);
}
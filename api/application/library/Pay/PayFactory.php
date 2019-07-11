<?php

namespace Pay;

/**
 * @
 * 
 * @version v0.1
 * @author zhaoyu
 *         @time 2018-5-23
 */
class PayFactory {
	protected static $_factory = NULL;
	
	/**
	 * 工厂加载方法
	 *
	 * @param string $section        	
	 * @return object
	 */
	public static function factory($section) {
		if ($section == 'all') {
			return self::$_factory;
		}
		$class = "\\Pay\\" . ucfirst ( $section ) . "\\PayInfo";
		if (! class_exists ( $class )) {
			throw new InvalidArgumentException ( 'mq worker [' . $section . '] not found', 101 );
		}
		
		if (! isset ( self::$_factory [$section] )) {
			self::$_factory [$section] = new $class ();
		}
		
		return self::$_factory [$section];
	}
}
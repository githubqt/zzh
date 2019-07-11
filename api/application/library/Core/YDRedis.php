<?php

/**
 * PDO数据库连接
 *
 * @package lib
 * @subpackage plugins.pdo
 * @author 苏宁 <snsnky@126.com>
 *
 * $Id: PDOQuery.class.php 2 2014-02-19 09:52:07Z suning $
 */
namespace Core;

use \Redis;
use Custom\YDLib;

final class YDRedis extends Redis {
	/**
	 * host
	 * 
	 * @var string
	 */
	private $_host;
	
	/**
	 * 端口
	 * 
	 * @var string
	 */
	private $_port;
	
	/**
	 *
	 * @var string
	 */
	private $_auth;
	
	/**
	 *
	 * Connection
	 * 
	 * @todo
	 *
	 */
	
	/**
	 * 构造函数
	 *
	 * @param array $params
	 *        	配置信息
	 * @param array $options
	 *        	选项, @see self::$_opt
	 */
	public function __construct($host, $port, $auth = '') {
		$this->_host = $host ? $host : __IP__;
		$this->_port = $port ? $port : 6379;
		$this->_auth = $auth ? $auth : '';
		parent::connect ( $this->_host, $this->_port );
	}
	
	/**
	 * Description: Authenticate the connection using a password.
	 * Warning: The password is sent in plain-text over the network.
	 *
	 * @param string $auth
	 *        	password
	 *        	
	 * @return bool BOOL: TRUE if the connection is authenticated, FALSE otherwise.
	 */
	public function authYD($auth = '') {
		if ((isset ( $this->auth ) && $this->auth) || ! empty ( $auth )) {
			$this->auth = $auth;
			parent::auth ( $this->auth );
		}
	}
	
	/**
	 * Description: Set client option.
	 *
	 * @param array $param        	
	 *
	 * @return boolean BOOL: TRUE on success, FALSE on error.
	 */
	public function setOptionYD($param) {
		if (! YDLib::checkData ( $param ))
			return false;
		foreach ( $param as $k => $v ) {
			parent::setOption ( $k, $v );
		}
	}
	
	/**
	 *
	 * Server
	 * 
	 * @todo
	 *
	 */
	
	/**
	 *
	 * Keys and Strings
	 * 
	 * @todo
	 *
	 */
	
	/**
	 *
	 * Hashes
	 * 
	 * @todo
	 *
	 */
	
	/**
	 * Description: Adds a value to the hash stored at key.
	 * If this value is already in the hash, FALSE is returned.
	 *
	 * @param string $hkey        	
	 * @param string $memKey        	
	 * @param string $memVal        	
	 *
	 * @return boolean LONG 1 if value didn't exist and was added successfully, 0 if the value was already present and was replaced, FALSE if there was an error.
	 */
	public function hSetYD($hkey, $memKey, $memVal) {
		if (! YDLib::checkData ( [ 
				$hkey,
				$memKey,
				$memVal 
		] ))
			return false;
		
		return parent::hSet ( $hkey, $memKey, $memVal );
	}
	
	/**
	 * Description: Adds a value to the hash stored at key only if this field isn't already in the hash.
	 * 
	 * @param unknown $hkey        	
	 * @param unknown $memKey        	
	 * @param unknown $memVal        	
	 *
	 * @return bool BOOL TRUE if the field was set, FALSE if it was already present.
	 */
	public function hSetNxYD($hkey, $memKey, $memVal) {
		if (! YDLib::checkData ( [ 
				$hkey,
				$memKey,
				$memVal 
		] ))
			return false;
		
		return parent::hSetNx ( $hkey, $memKey, $memVal );
	}
	
	/**
	 * Description: Gets a value from the hash stored at key.
	 * If the hash table doesn't exist, or the key doesn't exist, FALSE is returned.
	 * 
	 * @param unknown $hkey        	
	 * @param unknown $memKey        	
	 * @return boolean|string STRING The value, if the command executed successfully BOOL FALSE in case of failure
	 */
	public function hGetYD($hkey, $memKey) {
		if (! YDLib::checkData ( [ 
				$hkey,
				$memKey 
		] ))
			return false;
		
		return parent::hGet ( $hkey, $memKey );
	}
	
	/**
	 * Description: Returns the length of a hash, in number of items
	 * 
	 * @param unknown $hkey        	
	 *
	 * @return boolean|long LONG the number of items in a hash, FALSE if the key doesn't exist or isn't a hash.
	 */
	public function hLenYD($hkey) {
		if (! YDLib::checkData ( $hkey ))
			return false;
		
		return parent::hLen ( $hkey );
	}
	
	/**
	 * Description: Removes a value from the hash stored at key.
	 * If the hash table doesn't exist, or the key doesn't exist, FALSE is returned.
	 *
	 * @param string $key        	
	 * @param string $hashKey        	
	 *
	 * @return boolean BOOL TRUE in case of success, FALSE in case of failure
	 */
	public function hDelYD($hkey, $memKey) {
		if (! YDLib::checkData ( [ 
				$hkey,
				$memKey 
		] ))
			return false;
		return parent::hDel ( $hkey, $memKey );
	}
	
	/**
	 * Description: Returns the keys in a hash, as an array of strings.
	 * 
	 * @param string $hkey        	
	 *
	 * @return array|boolean An array of elements, the keys of the hash. This works like PHP's array_keys().
	 */
	public function hKeysYD($hkey) {
		if (! YDLib::checkData ( $hkey ))
			return false;
		
		$keys = parent::hKeys ( $hkey );
		return empty ( $keys ) ? false : $keys;
	}
	
	/**
	 * Description: Returns the values in a hash, as an array of strings.
	 * 
	 * @param string $hkey        	
	 *
	 * @return array|boolean
	 */
	public function hValsYD($hkey) {
		if (! YDLib::checkData ( $hkey ))
			return false;
		
		$vals = parent::hVals ( $hkey );
		return empty ( $vals ) ? false : $vals;
	}
	
	/**
	 * Description: Returns the whole hash, as an array of strings indexed by strings.
	 *
	 * @param unknown $hkey        	
	 *
	 * @return array|boolean An array of elements, the contents of the hash.
	 */
	public function hGetAllYD($hkey) {
		if (! YDLib::checkData ( $hkey ))
			return false;
		
		$kv = parent::hGetAll ( $hkey );
		return empty ( $kv ) ? false : $kv;
	}
	
	/**
	 * Description: Verify if the specified member exists in a key.
	 *
	 * @param string $hkey        	
	 * @param string $memkey        	
	 *
	 * @return boolean BOOL: If the member exists in the hash table, return TRUE, otherwise return FALSE.
	 */
	public function hExistsYD($hkey, $memkey) {
		if (! YDLib::checkData ( [ 
				$hkey,
				$memkey 
		] ))
			return false;
		
		return parent::hExists ( $hkey, $memkey );
	}
	
	/**
	 * Description: Increments the value of a member from a hash by a given amount.
	 *
	 * @param string $hkey        	
	 * @param string $memKey        	
	 * @param int $val        	
	 *
	 * @return long LONG the new value
	 */
	public function hIncrByYD($hkey, $memKey, $val) {
		if (! YDLib::checkData ( [ 
				$hkey,
				$memKey,
				$val 
		] ))
			return false;
		
		return parent::hIncrBy ( $hkey, $memKey, $val );
	}
	
	/**
	 * Description: Increments the value of a hash member by the provided float value
	 *
	 * @param string $hkey        	
	 * @param string $memKey        	
	 * @param float $val        	
	 *
	 * @return float LONG the new value
	 */
	public function hIncrByFloatYD($hkey, $memKey, $val) {
		if (! YDLib::checkData ( [ 
				$hkey,
				$memKey,
				$val 
		] ))
			return false;
		
		return parent::hIncrByFloat ( $hkey, $memKey, $val );
	}
	
	/**
	 * Description: Fills in a whole hash.
	 * Non-string values are converted to string, using the standard (string) cast. NULL values are stored as empty strings.
	 *
	 * @param string $hkey        	
	 * @param array $memKeys
	 *        	array(k=>v)
	 *        	
	 * @return bool
	 */
	public function hMsetYD($hkey, $memKeys) {
		if (! YDLib::checkData ( [ 
				$hkey,
				$memKeys 
		] ))
			return false;
		
		return parent::hMset ( $hkey, $memKeys );
	}
	
	/**
	 * Description: Retrieve the values associated to the specified fields in the hash.
	 *
	 * @param string $hkey        	
	 * @param array $memKeys
	 *        	array(k1,k2)
	 *        	
	 * @return array|bool Array An array of elements, the values of the specified fields in the hash, with the hash keys as array keys.
	 */
	public function hMgetYD($hkey, $memKeys) {
		if (! YDLib::checkData ( [ 
				$hkey,
				$memKeys 
		] ))
			return false;
		
		$ret = parent::hMget ( $hkey, $memKeys );
		return empty ( $ret ) ? false : $ret;
	}
	
	/**
	 * Description: Scan a HASH value for members, with an optional pattern and count
	 *
	 * @param unknown $str_key        	
	 * @param unknown $i_iterator        	
	 *
	 * @return Array An array of members that match our pattern
	 */
	public function hscanYD($str_key, $i_iterator) {
		$it = NULL;
		/* Don't ever return an empty array until we're done iterating */
		parent::setOption ( Redis::OPT_SCAN, Redis::SCAN_RETRY );
		while ( $arr_keys = $redis->hscan ( $str_key, $i_iterator ) ) {
			foreach ( $arr_keys as $str_field => $str_value ) {
				echo "$str_field => $str_value\n"; /* Print the hash member and value */
			}
		}
	}

/**
 *
 * Lists
 * 
 * @todo
 *
 */

/**
 *
 * Sets
 * 
 * @todo
 *
 */

/**
 *
 * Sorted sets
 * 
 * @todo
 *
 */

/**
 *
 * Pub/sub
 * 
 * @todo
 *
 */

/**
 *
 * Transactions
 * 
 * @todo
 *
 */

/**
 *
 * Scripting
 * 
 * @todo
 *
 */

/**
 *
 * Introspection
 * 
 * @todo
 *
 */
}

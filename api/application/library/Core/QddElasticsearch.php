<?php
/**
 * Created by PhpStorm.
 * User: zhaoyu
 * Date: 2018/9/10
 * Time: 12:26
 */

namespace Core;

require APPLICATION_PATH . '/vendor/autoload.php';

use EasyWeChat\Core\Exception;
use Elasticsearch\ClientBuilder;


class QddElasticsearch
{
    private static $_instance; // Elasticsearch 对象
    private $errorInfo  = null;
    protected $_chained = [
        'index' => null,
        'type'  => null,
        'limit' => [],
        'sort'  => [],
        'query' => [],
    ];



    /**
     *  初始化memcache
     * QddElasticsearch constructor.
     * @param $hosts
     */
    public function __construct($hosts)
    {
        if (!self::$_instance) {
            self::$_instance = ClientBuilder::create()// 实例化 ClientBuilder
            ->setHosts($hosts)// 设置主机信息
            ->build();              // 构建客户端对象
        }
    }

    /**
     * 集群统计
     * @return array
     */
    public function clusterStats()
    {
        return self::$_instance->cluster()->stats();
    }

    /**
     * 节点统计
     * @return array
     */
    public function nodesStats()
    {
        return self::$_instance->nodes()->stats();
    }

    /**
     * 索引统计
     * @param $params
     * @return array
     */
    public function indexStats($params = [])
    {
        return self::$_instance->indices()->stats($params);
    }


    /**
     *
     * @param $params
     * @return mixed
     */
    public function get($params)
    {
        return self::$_instance->get($params);
    }

    /**
     *
     * @param $params
     * @return mixed
     */
    public function exists($params)
    {
        return self::$_instance->exists($params);
    }



    /**
     * 创建index索引
     * @param $params
     */
    public function createIndex($params)
    {
        self::$_instance->indices()->create($params);
    }

    /**
     * 删除index索引
     * @param $indexName
     */
    public function deleteIndex($indexName = null)
    {
        $params = $indexName == null ? ['index' => $indexName] : $this->prepare();
        self::$_instance->indices()->delete($params);
    }

    public function addIndex($params)
    {

        self::$_instance->index($params);
    }


    /**
     * @param $params
     * @return array
     */
    public function search($params)
    {
        return self::$_instance->search($params);
    }

    /**
     * 按照相关度进行查询
     * @param $field
     * @param $value
     * @return $this
     */
    public function term($field,$value)
    {
        $this->_chained ['query'] = array_merge($this->_chained['query'] , [ 'term' => [ $field => $value ] ]);
        return $this;
    }

    /**
     * 分组按照相关度进行查询
     *
     * @param $field
     * @param $values
     * @return $this
     */
    public function terms($field,$values)
    {
        $this->_chained ['query'] = array_merge($this->_chained['query'] , [ 'term' => [ $field => $values ] ]);
        return $this;
    }

    /**
     * 按照全文查询进行查询
     *
     * @param $name
     * @param $value
     * @return $this
     */
    public function match($name,$value)
    {
        if (isset($this->_chained ['query']['match_all'])) unset($this->_chained ['query']['match_all']);

        if (isset($this->_chained ['query']['match']) || isset($this->_chained ['query']['bool'] )) {
            $must = [];
            if (isset($this->_chained ['query']['match'])) {
                $must['match'] = $this->_chained ['query']['match'];
                unset($this->_chained ['query']['match']);
            }
            //合并
            $whereMust['match'] = [ $name => $value ];
            $boolMust = [];
            if (count($must) > 0) {
                $boolMust[] = $must;
            }
            $boolMust[] = $whereMust;

            if (!isset( $this->_chained ['query']['bool'])) {
                $this->_chained ['query']['bool']['must'] = [];
            }

            $this->_chained ['query']['bool']['must'] = array_merge( $this->_chained ['query']['bool']['must'],$boolMust);
        } else {
            $this->_chained ['query']['match'] = [ $name => $value ];
        }

        return $this;
    }

    /**
     * 短语近似匹配
     * @param $field
     * @param $value
     * @return $this
     */
    public function match_phrase($field,$value)
    {
        $this->_chained ['query']['match_phrase'] = [ $field => $value ];
        return $this;
    }

    /**
     * 多个字段模糊查询
     * @param $value
     * @param array $fields
     * @return $this
     */
    public function multi_match($value,array $fields)
    {
        $this->_chained ['query']['multi_match'] = [ "query" => $value ,"fields" => $fields ];
        return $this;
    }

    /**
     * 语法查询
     * @param $value
     * @param array $fields
     * @return $this
     */
    public function query_string($value,$fields = [])
    {
        $this->_chained ['query']['query_string'] = [ "query" => $value];
        if (count($fields) > 0) {
            $this->_chained ['query']['query_string']  = array_merge( $this->_chained ['query']['query_string'] ,["fields" => $fields]);
        }

        return $this;
    }

    /**
     * 范围选择
     * @param $field 字段名称
     * @param $start 开始范围
     * @param $end 结束范围
     * @param string $gt 大于可以填写 gte 大于等于 gt 大于
     * @param string $lt 小于可以填写 lte 小于等于 le 小于
     * @return $this
     */
    public function range($field ,$start,$end = NULL,$gt = 'gte',$lt = 'lte')
    {
        $this->_chained ['query']['range'] = [ $field => [ $gt => $start]];
        if ($end !== NULL) {
            $this->_chained ['query']['range'][$field] = array_merge($this->_chained ['query']['range'][$field],[ $lt => $end]);
        }
        return $this;
    }



    /**
     * 查询所有记录
     * @return $this
     */
    public function matchAll()
    {
        $this->_chained ['query']['match_all'] = new \stdClass();
        return $this;
    }

    /**
     * 设置读取条数
     * @param int $offset
     * @param int $size
     * @return $this
     */
    public function limit($offset = 0,$size = 10)
    {
        $this->_chained ['limit']['from']  = $offset;
        $this->_chained ['limit']['size']  = $size;
        return $this;
    }

    /**
     * 排序
     * @param $name
     * @param $order
     * @return $this
     */
    public function sort($name,$order)
    {
        $params = [];
        $params[][$name] = ["order" => $order];
        $this->_chained['sort']  = array_merge($this->_chained['sort'],$params);

        return $this;
    }

    /**
     *  查询对应的索引
     * @param $indexName
     * @return $this
     */
    public function index($indexName)
    {
        $this->_chained ['index'] = $indexName;
        return $this;
    }

    /**
     *  查询对应的索引
     * @param $indexType
     * @return $this
     */
    public function type($indexType)
    {
        $this->_chained ['type'] = $indexType;
        return $this;
    }


    /**
     * 获得搜索条件
     * @return array
     */
    private function prepare()
    {
        $params = array();

        if (isset($this->_chained ['index']) && !empty($this->_chained ['index'])) {
            $params['index'] = $this->_chained ['index'];
        }

        if (isset($this->_chained ['type']) && !empty($this->_chained ['type'])) {
            $params['type'] = $this->_chained ['type'];
        }


        $params['body'] = [];
        if (is_array($this->_chained ['limit']) && count($this->_chained ['limit']) > 0)
        {
            $params['body'] = array_merge($params['body'],$this->_chained ['limit']);
        }

        if (is_array($this->_chained ['query']) && count($this->_chained ['query']) > 0)
        {
            $params['body']['query'] = $this->_chained ['query'];
        }

        if (is_array($this->_chained ['sort']) && count($this->_chained ['sort']) > 0)
        {
            $params['body']['sort'] = $this->_chained ['sort'];
        }

        return $params;
    }



    /**
     * 清空参数
     */
    private function clearParam()
    {
        $this->_chained = [
            'index' => null,
            'type'  => null,
            'limit' => [],
            'sort'  => [],
            'query' => [],
        ];
    }

    /**
     * 返回搜索条件
     * @return array|bool
     */
    public function query()
    {
        $params = $this->prepare();
        echo"<pre>";
        print_r($params);
        try {
            $result = self::$_instance->search($params);
            $this->clearParam();
            return $result;
        } catch (\Exception $e) {
            $errInfo =  $e->getMessage();

            $this->errorInfo = json_decode($errInfo,TRUE);
            print_r($this->errorInfo);
            $this->clearParam();
            return FALSE;
        }
    }



}
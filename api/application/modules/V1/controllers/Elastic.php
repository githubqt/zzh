<?php
/** 
 * Elastic
 * @version v0.01
 * @author lqt
 * @time 2018-12-21
 */

require APPLICATION_PATH . '/vendor/autoload.php';

use Elasticsearch\ClientBuilder;
use Custom\YDLib;

class ElasticController extends BaseController {

    /**
     * Elastic基本语法
     * <pre>
     * 正式： http://api.qudiandang.com/v1/Elastic/search1?identif=test
     * 测试： http://testapi.qudiandang.com/v1/Elastic/search1?identif=test
     * </pre>
     */
	public function search1Action()
    {
        //实例化
        $client = ClientBuilder::create()->build();

        //索引一个文档
//        $params = [
//            'index' => 'my_index',
//            'type' => 'my_type',
//            'id' => 'my_id',
//            'body' => ['testField' => 'abc']
//        ];
//
//        $response = $client->index($params);
//        print_r($response);

        //获取一个文档
//        $params = [
//            'index' => 'my_index',
//            'type' => 'my_type',
//            'id' => 'my_id'
//        ];
//
//        $response = $client->get($params);
//        print_r($response);


        //搜索一个文档
//        $params = [
//            'index' => 'my_index',
//            'type' => 'my_type',
//            'body' => [
//                'query' => [
//                    'match' => [
//                        'testField' => 'abc'
//                    ]
//                ]
//            ]
//        ];
//
//        $response = $client->search($params);
//        print_r($response);

        //删除一个文档
//        $params = [
//            'index' => 'my_index',
//            'type' => 'my_type',
//            'id' => 'my_id'
//        ];
//
//        $response = $client->delete($params);
//        print_r($response);

        //删除一个索引
//        $deleteParams = [
//            'index' => 'my_index'
//        ];
//        $response = $client->indices()->delete($deleteParams);
//        print_r($response);

        //创建一个索引
        $params = [
            'index' => 'my_index',
            'body' => [
                'settings' => [
                    'number_of_shards' => 2,
                    'number_of_replicas' => 0
                ]
            ]
        ];

        $response = $client->indices()->create($params);
        print_r($response);

        //索引管理和集群管理分别在 $client->indices() 和 $client->cluster()

    }

    public function contentAction()
    {
//        $hosts = [
//            'api.qudiandang.com:9200',
//        ];
//        $client = ClientBuilder::create()           // Instantiate a new ClientBuilder
//        ->setHosts($hosts)      // Set the hosts
//        ->build();              // Build the client object


//        $hosts = [
////            // This is effectively equal to: "https://username:password!#$?*abc@foo.com:9200/"
////            [
////                'host' => 'api.qudiandang.com',
////                'port' => '9200',
////                //'scheme' => 'https',
////                //'user' => 'username',
////                //'pass' => 'password!#$?*abc'
////            ],
////
////            // This is equal to "http://localhost:9200/"
//////            [
//////                'host' => 'localhost',    // Only host is required
//////            ]
////        ];
////        $client = ClientBuilder::create()           // Instantiate a new ClientBuilder
////        ->setHosts($hosts)      // Set the hosts
////        ->build();              // Build the client object
//
//        $params = [
//            'index' => 'my_index',
//            'type' => 'my_type',
//            'id' => 'my_id',
//            'body' => ['testField' => 'abc']
//        ];
//
//        $response = $client->index($params);
//        print_r($response);

        $client = ClientBuilder::create()
            ->setHosts(["http://api.qudiandang.com:9200"])
//            ->setHosts(["http://47.92.215.79:9200"])
            ->setRetries(0)
            ->build();

//        try {
//            $params = [
//                'index' => 'my_index',
//                'type' => 'my_type',
//                'id' => 'my_id',
//                'body' => ['testField' => 'abc']
//            ];
//
//            $response = $client->index($params);
//        } catch (\Elasticsearch\Common\Exceptions\Curl\CouldNotConnectToHost $e) {
//            $previous = $e->getPrevious();
//            print_r('11111111111');
////            if ($previous instanceof 'Elasticsearch\Common\Exceptions\MaxRetriesException') {
////                echo "Max retries!";
////            }
//        }
//        print_r($response);

//        $params = [
//            'index'  => 'test_missing',
//            'type'   => 'test',
//            'id'     => 1,
//            'client' => [ 'ignore' => [400, 404]  ]
//        ];
//        $response = $client->get($params);
//        print_r($response);

//        $params = [
//            'index' => 'my_index',
//            'type' => 'my_type',
//            'id' => 'my_id',
//            'client' => [
//                'ignore' => 404,
//                'verbose' => true,
//                'timeout' => 10,        // ten second timeout
//                'connect_timeout' => 10
//            ]
//        ];
//
//        $response = $client->get($params);
//        print_r($response);

        //$client = ClientBuilder::create()->build();
//        $futures = [];
//        for ($i = 0; $i < 1; $i++) {
//            $params = [
//                'index' => 'test',
//                'type' => 'test',
//                'id' => $i,
//                'client' => [
//                    'future' => 'lazy',
//                    'ignore' => 404,
//                ]
//            ];
//            $futures[] = $client->get($params);     //queue up the request
//        }
//        foreach ($futures as $future) {
//            // access future's values, causing resolution if necessary
//            echo $future['_source'];
//        }

        //$client = ClientBuilder::create()->build();

//        $params = [
//            'index' => 'test',
//            'type' => 'test',
//            'id' => 1,
//            'client' => [
//                'future' => 'lazy',
//                'ignore' => 404,
//            ]
//        ];
//        $future = $client->get($params);
//        $results = $future->wait();       // resolve the future
//        print_r($results);
        //创建一个索引
//        $params = [
//            'index' => 'my_index2',
//            'body' => [
//                'settings' => [
//                    'number_of_shards' => 2,
//                    'number_of_replicas' => 0
//                ]
//            ]
//        ];
//
//        $response = $client->indices()->create($params);
//        print_r($response);

        $params = [
            'index' => 'my_index',
            'body' => [
                'settings' => [
                    'number_of_replicas' => 0,
                    'refresh_interval' => -1
                ]
            ]
        ];

        $response = $client->indices()->putSettings($params);
        print_r($response);
        // Get settings for one index
        $params = ['index' => 'my_index'];
        $response = $client->indices()->getSettings($params);
        print_r($response);
        // Get settings for several indices
        $params = [
            'index' => [ 'my_index', 'my_index2' ]
        ];
        $response = $client->indices()->getSettings($params);
        print_r($response);
    }

    public function demoAction()
    {
        //$es = YDLib::getES('elasticsearch');
        \Product\ProductModel::createIndex();
        exit;
        $params = [
            'index' =>  'my_index',
            'type' => 'my_type',
            'body' => [
                'from' => '0',
                'size' => '16'
            ]

        ];

        //模糊匹配 单个查询
        $list = $es -> match("name","金")
            -> limit(0,10)
            -> index('my_index')
            -> type('my_product')
            -> query();


        //模糊匹配 “金” OR  “大” 查询
        $list = $es -> match("name","金 大")
            -> limit(0,10)
            -> index('my_index')
            -> type('my_product')
            -> query();

        // 模糊匹配“金” AND “大” 查询
        $list = $es -> match("name","金")
            -> match("name","大")
            -> limit(0,10)
            -> index('my_index')
            -> type('my_product')
            -> query();

        // 查询全部数据
        $list = $es -> matchAll()
            -> limit(0,10)
            -> index('my_index')
            -> type('my_product')
            -> query();

        //获取索引信息
        $list = $es->indexStats();

        //获取节点信息
        $list = $es->nodesStats();

        //获得集群信息
        $list = $es->clusterStats();


        //模糊匹配 查询金的类型并对 id 进行排序
        $list = $es -> match("title","北京")
            -> limit(0,10)
            -> sort("id" , "asc")  // desc asc
            -> index('weather')
            -> type('person')
            -> query();

        //完整匹配
        $list = $es -> match_phrase("category_name","金 条")
            -> limit(0,10)
            -> index('my_index')
            -> query();

        //多个字段模糊查询，查询name和category_name字段
        $list = $es -> multi_match("金条",['name','category_name'])
            -> limit(0,10)
            -> index('my_index')
            -> query();


        //语法查询 相当于 sql语句 select * from table where name in("金","条") or  category_name in("金","条")
        $list = $es -> query_string("金 OR 条",['name','category_name'])
            -> limit(0,10)
            -> index('my_index')
            -> query();

        //语法查询 相当于 sql语句 select * from table where (name = "金" and name = "条") or  (category_name  = "金" and category_name "条")
        $list = $es -> query_string("金 AND 条",['name','category_name'])
            -> limit(0,10)
            -> index('my_index')
            -> query();

        //语法查询
        $list = $es -> query_string("（ElasticSearch AND 大法） OR Python")
            -> limit(0,10)
            -> index('my_index')
            -> query();

        //查询 id >= 1
        $list = $es -> range("id",1)
            -> limit(0,10)
            -> index('weather')
            -> query();

        //查询 id 3 -  5 之间
        $list = $es -> range("id",3,5)
            -> limit(0,10)
            -> index('weather')
            -> query();

        //查询 id 4 -  5 之间
        $list = $es -> range("id",3,5,'gt')
            -> limit(0,10)
            -> index('weather')
            -> query();

        //查询 id 3 -  4 之间
        $list = $es -> range("id",3,5,'gte','lt')
            -> limit(0,10)
            -> index('weather')
            -> query();

        echo "<pre>";
        print_r($list);
    }

}

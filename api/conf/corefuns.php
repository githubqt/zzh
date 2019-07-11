<?php

/**
 * @param string $serviceName
 *
 * @return mixed|null
 */
function DI($serviceName)
{
    static $container;
    if (!$container) {
        $container = new \GlobalLib\Vendor\Pimple\Container();

        $config = include __DIR__ . '/base.inc.php';

        $dbnames = [
            'act', 'admin', 'center', 'comment', 'favorite',
            'mall', 'mobile', 'passport', 'tag', 'user',
            'notice', 'social', 'broadcast', 'circle', 'article', 'topic',
            'dynamic'
        ];
        $mysqlConfig = [];
        if (__ENV__ == 'ONLINE') {
            $dbPrefix = 'tete_';
        } else {
            $dbPrefix = strtolower(__ENV__) . '_tete_';
        }
        foreach ($dbnames as $dbname) {
            $mysqlConfig[$dbname] = [
                'host' => $config['mysql']['main']['host'],
                'port' => 3306,
                'dbname' => $dbPrefix . $dbname,
                'user' => $config['mysql']['main']['user'],
                'password' => $config['mysql']['main']['password'],
            ];
        }

        $container['config'] = [
            'memcache' => $config['memcache'],
            'redis' => $config['redis'],
            'httpsqs' => $config['httpsqs'],
            'mysql' => $mysqlConfig,
        ];

        $diConfig = include __DIR__ . '/di.php';
        foreach ($diConfig as $k => $v) {
            if (is_array($v)) {
                call_user_func_array([$container, $v['call']], $v['param']);
            } else {
                $container[$k] = $v;
            }
        }
    }

    return isset($container[$serviceName]) ? $container[$serviceName] : NULL;
}

/**
 * @param string $modelName model名称, 由'.'分隔数据库和表名, 如: mall.category or Mall.Category
 *
 * @return GlobalLib\Model\Base
 */
function M($modelName)
{
    $modelDIName = 'model.' . $modelName;
    if (DI($modelDIName) === null) {
        /** @var callable $factory */
        $factory = DI('model.factory');

        DI('container')[$modelDIName] = $factory($modelName);
    }

    return DI($modelDIName);
}


function DB($dbName)
{
    $dbDIName = 'db.' . $dbName;
    if (DI($dbDIName) === null) {
        /** @var callable $factory */
        $factory = DI('db.factory');

        DI('container')[$dbDIName] = $factory($dbName);
    }
    return DI($dbDIName);
}


if (!function_exists('dd')) {
    /**
     * 输出传递的变量并终止脚本
     *
     * @param  mixed $args
     * @return void
     */
    function dd(...$args)
    {
        http_response_code(500);

        foreach ($args as $x) {
            var_dump($x);
        }

        die(1);
    }
}
if (!function_exists('dc')) {
    /**
     * 输出传递的变量
     *
     * @param  mixed $args
     * @return void
     */
    function dc(...$args)
    {
        http_response_code(500);

        foreach ($args as $x) {
            var_dump($x);
        }
    }
}

if (!function_exists('value')) {
    /**
     * 返回给定值的默认值.
     *
     * @param  mixed $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (!function_exists('sqlDump')) {
    function sqlDump(... $args)
    {
        foreach ($args as $x) {
            $key = date('Y-m-d H:i:s') . " OUTPUT";
            $content = preg_replace('#\s+#', ' ', $x);
            $log = new \Core\Logger('SQL/sql', [
                $key => $content,
            ], PHP_EOL);
            $log->write();
        }
    }
}

/**
 * 采购日志记录
 */
if (!function_exists('purchaseTrackingLog')) {
    function purchaseTrackingLog($purchase_id, $child_order_no, $oper_type, $content,$status = '1')
    {
        return \Purchase\PurchaseTrackingLogModel::log($purchase_id, $child_order_no, $oper_type, $content,$status);
    }
}

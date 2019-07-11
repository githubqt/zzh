<?php
// +----------------------------------------------------------------------
// | PhpStorm
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://zhahehe.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：昌少 
// +----------------------------------------------------------------------
// | Author: 昌少  Date:2018/8/15 Time:19:34
// +----------------------------------------------------------------------

namespace Assemble\Support\Traits\Model;

use Assemble\Support\Traits\Macroable;

trait HasColumns
{
    /**
     * 给列取别名
     * @param string $alias
     * @param bool $aliasColumn 是否修改列的名称
     * @param array $columns
     * @return array
     */
    public static function aliasColumn(string $alias, bool $aliasColumn = false, array $columns = [])
    {
        if (empty($columns)) {
            $columns = static::$showColumns;
        }

        array_walk($columns, function (&$item) use ($alias, $aliasColumn) {
            if ($aliasColumn) {
                return $item = "{$alias}.{$item} as {$alias}_{$item}";
            }else{
                return $item = "{$alias}.{$item}";
            }

        });

        return $columns;
    }

    use Macroable;
}
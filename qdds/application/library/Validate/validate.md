独立验证
===============

任何时候，都可以使用Validate类进行独立的验证操作:

```php
<?php

$validate = new Validate([
    'name'  => 'require|max:25',
    'email' => 'email'
]);
$data = [
    'name'  => '啦啦啦',
    'email' => 'lalala@qq.com'
];
if (!$validate->check($data)) {
    dump($validate->getError());
}

```
验证器
===============

推荐的验证方式，为具体的验证场景或者数据表定义好验证器类，直接调用验证类的check方法即可完成验证，下面是一个例子：:

我们定义一个\application\library\Validate\Product 验证器类用于ProductModel的验证。

```php
<?php

namespace Validate;

class Product
{
    /**
     * 定义数据验证规则
     * @var array
     */
    static $rule = [
        'name' => 'require|max:100|unique:product',
        'self_code' => 'require|max:20',
    ];
    /**
     * 定义数据验证错误提示
     * @var array
     */
    static $message = [
        'name.require' => '商品名称必须',
        'name.max' => '商品名称最多100字符',
        'name.unique' => '商品名称最多100字符',
        'self_code.require' => '自定义编码必填',
        'self_code.max' => '自定义编码最多20字符',
    ];
}

```
在需要进行ProductModel验证的地方，添加如下代码即可：

```php
<?php
use Assemble\Support\Validate;

$data = [
    'name' => 'zhahehe',
    'email' => 'zhahehe@qq.com',
];
$validate = Validate::validation("product");
if (!$validate->check($data)) {
    dd($validate->getError());
}

```
设置规则
===============

可以在实例化Validate类的时候传入验证规则，例如：

```php
<?php
use Assemble\Support\Validate;

$rule = [
    'name' => 'require|max:100|unique:product',
    'self_code' => 'require|max:20',
];
$validate = new Validate($rules);


```

规则定义
===============

规则定义支持下面两种方式：

```php
<?php
use Assemble\Support\Validate;

$rule = [
    'name' => 'require|max:100|unique:product',
    'self_code' => 'require|max:20',
];
$validate = new Validate($rules);


```


对于一个字段可以设置多个验证规则，使用|分割。

或者采用数组方式定义多个规则（适用于你的验证规则中有|的情况）

```php
<?php
use Assemble\Support\Validate;

$rule = [
    'name' => ['require','max'=>100,'unique'=>'product'],
    'self_code' => ['require','max:20']
];
$validate = new Validate($rules);


```
属性定义
===============

通常情况下，我们实际在定义验证类的时候，可以通过属性的方式直接定义验证规则等信息，例如：

```php
<?php

namespace Validate;


class Product
{
    /**
     * 定义数据验证规则
     * @var array
     */
    static $rule = [
        'name' => 'require|max:100|unique:product',
        'self_code' => 'require|max:20',
    ];
    /**
     * 定义数据验证错误提示
     * @var array
     */
    static $message = [
        'name.require' => '商品名称必须',
        'name.max' => '商品名称最多100字符',
        'name.unique' => '商品名称最多100字符',
        'self_code.require' => '自定义编码必填',
        'self_code.max' => '自定义编码最多20字符',
    ];
}

```

验证数据
===============

下面是一个典型的验证数据的例子：

```php
<?php

use Assemble\Support\Validate;

$rule = [
    'name' => 'require|max:100|unique:product',
    'self_code' => 'require|max:20',
];

$message = [
    'name.require' => '商品名称必须',
    'name.max' => '商品名称最多100字符',
    'name.unique' => '商品名称最多100字符',
    'self_code.require' => '自定义编码必填',
    'self_code.max' => '自定义编码最多20字符',
];

$data = [
    'name'  => '啦啦啦',
    'self_code' => 'lalala@qq.com'
];

$validate = new Validate($rule, $message);

if (!$validate->check($data)) {
    dump($validate->getError());
}

```


验证场景
===============

可以在定义验证规则的时候定义场景，并且验证不同场景的数据，例如：

```php
<?php

use Assemble\Support\Validate;

$rule = [
    'name' => 'require|max:100|unique:product',
    'self_code' => 'require|max:20',
];

$message = [
    'name.require' => '商品名称必须',
    'name.max' => '商品名称最多100字符',
    'name.unique' => '商品名称最多100字符',
    'self_code.require' => '自定义编码必填',
    'self_code.max' => '自定义编码最多20字符',
];

$data = [
    'name'  => '啦啦啦',
    'self_code' => 'lalala@qq.com'
];

$validate = new Validate($rule);
$validate->scene('edit', ['name', 'self_code']);
$result = $validate->scene('edit')->check($data);

//表示验证edit场景（该场景定义只需要验证name和self_code字段）。


```
如果使用了验证器，可以直接在类里面定义场景，例如：

```php
<?php
namespace Validate;

class Product
{
    /**
     * 定义数据验证规则
     * @var array
     */
    static $rule = [
        'name' => 'require|max:100|unique:product',
        'self_code' => 'require|max:20',
    ];
    /**
     * 定义数据验证错误提示
     * @var array
     */
    static $message = [
        'name.require' => '商品名称必须',
        'name.max' => '商品名称最多100字符',
        'name.unique' => '商品名称最多100字符',
        'self_code.require' => '自定义编码必填',
        'self_code.max' => '自定义编码最多20字符',
    ];
    /**
     * 定义数据验证场景
     * @var array
     */
    static $scene = [
        'add' => ['name', 'self_code'],
        'edit' => ['self_code'],
    ];
}    

```

然后再需要验证的地方直接使用 scene 方法验证
```php
<?php
use Product\ProductModel;

$data = [
    'name'  => '啦啦啦',
    'self_code' => 'lalala@qq.com'
];

$Product = new ProductModel();
$result = $Product->validate('Product.edit')->check($data);
```

可以在定义场景的时候对某些字段的规则重新设置，例如：

```php
<?php
namespace Validate;

class Product
{
    /**
     * 定义数据验证规则
     * @var array
     */
    static $rule = [
        'name' => 'require|max:100|unique:product',
        'self_code' => 'require|max:20',
    ];
    /**
     * 定义数据验证错误提示
     * @var array
     */
    static $message = [
        'name.require' => '商品名称必须',
        'name.max' => '商品名称最多100字符',
        'name.unique' => '商品名称最多100字符',
        'self_code.require' => '自定义编码必填',
        'self_code.max' => '自定义编码最多20字符',
    ];
    /**
     * 定义数据验证场景
     * @var array
     */
    static $scene = [
        'add' => ['name', 'self_code'=>'require|number|between:15,20'],
        'edit' => ['self_code'],
    ];
}    

```

##模型验证
在模型中的验证方式如下：

```php
<?php
use Product\ProductModel;

//第一种方式
$Product = new ProductModel();
$Product->name = '啦啦';
$result = $Product->validate('Product.edit')->save();

//第二种方式

$data = [
    'name'  => '啦啦啦',
    'self_code' => 'lalala@qq.com'
];

$Product = new ProductModel();
$result = $Product->validate('Product.edit')->save($data);

```



# 内置验证规则
系统内置的验证规则如下：

## 格式验证类
> require 

验证某个字段必须，例如：
```
'name'=>'require'
``` 

> number 或者 integer 

验证某个字段的值是否为数字（采用filter_var验证），例如：
```
'num'=>'number'
``` 

> float 

验证某个字段的值是否为浮点数字（采用filter_var验证），例如：
```
'num'=>'float'
```

> boolean 

验证某个字段的值是否为布尔值（采用filter_var验证），例如：
```
'num'=>'boolean'
```

> email 

验证某个字段的值是否为email地址（采用filter_var验证），例如：
```
'email'=>'email'
```

> array 

验证某个字段的值是否为数组，例如：
```
'info'=>'array'
```

> accepted 

验证某个字段是否为为 yes, on, 或是 1。这在确认"服务条款"是否同意时很有用，例如：
```
'accept'=>'accepted'
```

> date 

验证值是否为有效的日期，例如：
```
'date'=>'date'
``` 

会对日期值进行strtotime后进行判断。

> alpha 

验证某个字段的值是否为字母，例如：
```
'name'=>'alpha'
```

> alphaNum 

验证某个字段的值是否为字母和数字，例如：

```
'name'=>'alphaNum'
```

> alphaDash 

验证某个字段的值是否为字母和数字，下划线_及破折号-，例如：
```
'name'=>'alphaDash'
```

> chs 

验证某个字段的值只能是汉字，例如：
```
 'name'=>'chs'
```

> chsAlpha 

验证某个字段的值只能是汉字、字母，例如： 
```
'name'=>'chsAlpha'
```

> chsAlphaNum 

验证某个字段的值只能是汉字、字母和数字，例如：
```
'name'=>'chsAlphaNum'
```

> chsDash 

验证某个字段的值只能是汉字、字母、数字和下划线_及破折号-，例如： 
```
'name'=>'chsDash'
```

> activeUrl 

验证某个字段的值是否为有效的域名或者IP，例如：
```
'host'=>'activeUrl' 
```

> url 

验证某个字段的值是否为有效的URL地址（采用filter_var验证），例如：
```
'url'=>'url'
```

> ip 

验证某个字段的值是否为有效的IP地址（采用filter_var验证），例如：
```
'ip'=>'ip'
``` 
支持验证ipv4和ipv6格式的IP地址。

> dateFormat:format 

验证某个字段的值是否为指定格式的日期，例如：
```
'create_time'=>'dateFormat:y-m-d'
```

## 长度和区间验证类
  
> in 
验证某个字段的值是否在某个范围，例如：
```
'num'=>'in:1,2,3'
```

> notIn 

验证某个字段的值不在某个范围，例如：
```
'num'=>'notIn:1,2,3'
```

> between 

验证某个字段的值是否在某个区间，例如：
```
'num'=>'between:1,10'
```

> notBetween 

验证某个字段的值不在某个范围，例如：
```
'num'=>'notBetween:1,10'
```

> length:num1,num2 

验证某个字段的值的长度是否在某个范围，例如：
```
'name'=>'length:4,25'
```

或者指定长度
```
'name'=>'length:4' 
```
如果验证的数据是数组，则判断数组的长度。
如果验证的数据是File对象，则判断文件的大小。

> max:number 

验证某个字段的值的最大长度，例如：
```
'name'=>'max:25'
``` 

如果验证的数据是数组，则判断数组的长度。 
如果验证的数据是File对象，则判断文件的大小。

> min:number 

验证某个字段的值的最小长度，例如：
```
'name'=>'min:5'
``` 
如果验证的数据是数组，则判断数组的长度。
如果验证的数据是File对象，则判断文件的大小。

> after:日期 

验证某个字段的值是否在某个日期之后，例如：
```
'begin_time' => 'after:2016-3-18'
```

> before:日期 

验证某个字段的值是否在某个日期之前，例如：
```
'end_time'   => 'before:2016-10-01'
```
> expire:开始时间,结束时间

验证当前操作（注意不是某个值）是否在某个有效日期之内，例如：
```
'expire_time'   => 'expire:2016-2-1,2016-10-01',
```

> allowIp:allow1,allow2,... 

验证当前请求的IP是否在某个范围，例如：
```
'name'   => 'allowIp:114.45.4.55'
```

该规则可以用于某个后台的访问权限

> denyIp:allow1,allow2,...

验证当前请求的IP是否禁止访问，例如：
```
'name'   => 'denyIp:114.45.4.55',
```

## 字段比较类

> confirm

验证某个字段是否和另外一个字段的值一致，例如：
```
'repassword'=>'require|confirm:password' 如password和password_confirm是自动相互验证的，只需要使用
'password'=>'require|confirm' 会自动验证和password_confirm进行字段比较是否一致，反之亦然。
```

> different

验证某个字段是否和另外一个字段的值不一致，例如：
```
'name'=>'require|different:account'
```
> eq 或者 = 或者 same 

验证是否等于某个值，例如：
```
'score'=>'eq:100'
'num'=>'=:100'
'num'=>'same:100'
```
> egt 或者 >=

验证是否大于等于某个值，例如：

```
'score'=>'egt:60'
'num'=>'>=:100'
```

> gt 或者 >

验证是否大于某个值，例如：
```
'score'=>'gt:60'
'num'=>'>:100'
```
> elt 或者 <=

验证是否小于等于某个值，例如：
```
'score'=>'elt:100'
'num'=>'<=:100'
```

lt 或者 < 验证是否小于某个值，例如：
```
'score'=>'lt:100'
'num'=>'<:100'
 ```
验证对比其他字段大小（数值大小对比），例如：
```
'price'=>'lt:market_price'
'price'=>'<:market_price'
```

## filter验证

支持使用filter_var进行验证，例如：
> 'ip'=>'filter:validate_ip'
正则验证
支持直接使用正则验证，例如：
```
'zip'=>'\d{6}',
// 或者
'zip'=>'regex:\d{6}',
```
如果你的正则表达式中包含有|符号的话，必须使用数组方式定义。
```
'accepted'=>['regex'=>'/^(yes|on|1)$/i'],
```
也可以实现预定义正则表达式后直接调用，例如在验证器类中定义regex属性
```
protected $regex = [ 'zip' => '\d{6}'];
```
然后就可以使用
```
'zip'=>'regex:zip',
```
## 上传验证

> file  验证是否是一个上传文件

> image:width,height,type
验证是否是一个图像文件，width height和type都是可选，width和height必须同时定义。

> fileExt:允许的文件后缀
验证上传文件后缀

> fileMime:允许的文件类型
验证上传文件类型

> fileSize:允许的文件字节大小
验证上传文件大小

## 其它验证

```
unique:table,field,except,pk
```

验证当前请求的字段值是否为唯一的，例如：
```
// 表示验证name字段的值是否在product表（不包含前缀）中唯一
'name'   => 'unique:product',
// 验证其他字段
'name'   => 'unique:product,account',
// 排除某个主键值
'name'   => 'unique:product,account,10',
// 指定某个主键值排除
'name'   => 'unique:product,account,10,product_id',
```
如果需要对复杂的条件验证唯一，可以使用下面的方式：
```
// 多个字段验证唯一验证条件
'name'   => 'unique:product,status^account',
// 复杂验证条件
'name'   => 'unique:product,status=1&account='.$data['account'],
```
> requireIf:field,value
验证某个字段的值等于某个值的时候必须，例如：
```
// 当account的值等于1的时候 password必须
'password'=>'requireIf:account,1'
```
> requireWith:field
验证某个字段有值的时候必须，例如：
```
// 当account有值的时候password字段必须
'password'=>'requireWith:account'
```




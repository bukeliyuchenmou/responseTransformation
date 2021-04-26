# responseTransformation
Conversion response of user defined model based on laravel   
基于laravel的自定义模型转换响应

## 开始
发布配置文件
```shell
    php artisan wdnmd:publish
```

## 定义模型转换层
- modelName 是允许访问的关联项
- modelTransForm 是子关联的模型转换层映射类
- handle 是返回的模型数据
- installType 更多的 install是根据配置文件里的query_key来的
```php
namespace App\Transformation;


use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Wdnmd\Transform;

class UserTransform extends Transform
{
    protected $modelName = ['type'];

    protected $modelTransForm = ['type' => TypeTransform::class];

    public function handle(Model $user) {
        return [
            'name' => $user->name
        ];
    }

    public function installType($user)
    {
        return $this->item($user->getType, new TypeTransform());
    }
}
```

## 关联
    支持以传参的方式来访问关联，使用符号.可以找下一级关联，使用符号,可以查找当前不同的关联

## 约定
    模型中的关联以get开头后面的驼峰命名的方式，然后在使用query查询时候，可以使用以下划线小写分割的形式
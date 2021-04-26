<?php

declare(strict_types = 1);
namespace Wdnmd;

use Exception;
use Illuminate\Database\Eloquent\Model;

/**
 * 转换成基类
 * Class Transform
 * @package Wdnmd\src
 */
abstract class Transform
{
    /**
     * 需要使用关联的声明
     * @var array
     */
    protected $modelName = [];

    protected $modelTransForm = [];

    /**
     * 时间格式
     * @author yansong
     * @param int $time
     * @return string
     */
    public function timeToDataString(int $time) : string
    {
        return date('Y-m-d H:i:s', $time);
    }

    /**
     * 获取使用关联的声明
     * @author yansong
     * @return array
     */
    public function getModelName() : array
    {
        return $this->modelName;
    }

    /**
     * @return array
     */
    public function getModelTransForm(): array
    {
        return $this->modelTransForm;
    }

    /**
     * 通过容器替代Response
     * @return \Illuminate\Contracts\Foundation\Application|mixed
     * @author: chenyansong
     * @Time: 2021/4/26 23:03
     */
    public function getResponse()
    {
        return app(Response::class);
    }

    abstract function handle(Model $model);

    /**
     * 读取response中的基类
     * @param $name
     * @param $arguments
     * @return false|mixed
     * @throws Exception
     * @author: chenyansong
     * @Time: 2021/4/26 23:38
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->getResponse(), $name) || $name == 'array'){
            return call_user_func_array([$this->getResponse(), $name], $arguments);
        }

        throw new Exception('找不到这个方法');
    }
}

<?php


namespace Wdnmd\services;


use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;

class BaseService
{
    protected $servers = [
        Collection::class => CollectionService::class,
        AbstractPaginator::class => PaginatorService::class,
        ElseService::class => ElseService::class
    ];

    protected $key ;

    /**
     * @param $item
     * @return AbstractService
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @author: chenyansong
     * @Time: 2021/7/6 10:31
     */
    private function getService($item) :AbstractService
    {
        if (!($this->servers[$item] instanceof AbstractService)) {
            return $this->servers[$item] = $this->isWdnmdService($this->servers[$item]);
        } else {
            return $this->servers[$item];
        }
    }

    /**
     * @param $v
     * @return false|mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @author: chenyansong
     * @Time: 2021/7/6 10:31
     */
    private function isWdnmdService($v)
    {
        return app()->make($v, ['key' => $this->key]);
    }

    public function handle($data, $transform, int $code = 200, bool $is_type = false)
    {
        if ($data instanceof Collection) {
            dd($this->getService($data)->handle($data, $transform, $code, $is_type));
        } elseif ($data instanceof AbstractPaginator) {

        } else {

        }
    }
}
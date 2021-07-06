<?php

declare(strict_types=1);

namespace Wdnmd;

use Illuminate\Http\Response as HttpResponse;
use Exception;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;

/**
 * 响应基类
 * Class Response
 * @package Wdnmd\src
 */
class Response extends HttpResponse
{
    /**
     * 关联方法别名s
     * @var string
     */
    protected $key = 'install';

    public function __construct($content = '', $status = 200, array $headers = [])
    {
        parent::__construct($content, $status, $headers);
        $this->key = config('wdnmd.query_key');
    }

    /**
     * 数组方式
     * @param array $arr
     * @return Response
     * @author yansong
     */
    public function array(array $arr): Response
    {
        $arr = json_encode($arr);
        return $this->setContent($arr);
    }


    /**
     * 基本关联
     * @param $data
     * @param $transform
     * @param int $code
     * @param false $is_type
     * @return Response
     * @throws \ReflectionException
     * @author: chenyansong
     * @Time: 2021/4/26 23:37
     */
    public function item($data, $transform, int $code = 200, bool $is_type = false): Response
    {
        $res = $this->items($data, $transform, $code, $is_type);
        if ($res instanceof HttpResponse) {
            $res = $res->getContent();
        }

        if ($is_type) {
            return $this->setContent(json_encode($res));
        } else {
            return $this->setContent(json_encode(['data' => $res]));
        }
    }

    /**
     * 转换
     * @param $data
     * @param $transform
     * @param int $code
     * @param false $is_type
     * @return array|false|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|HttpResponse|mixed
     * @throws \ReflectionException
     * @author: chenyansong
     * @Time: 2021/4/26 23:37
     */
    public function items($data, $transform, int $code = 200, bool $is_type = false)
    {
        $install = Request::only([$this->key]);
        $install[$this->key] = $install[$this->key] ?? '';
        $installArr = explode(',', $install[$this->key]);
        if ($data instanceof Collection) {
            $transformData = [];
            $load_arr = $this->existHasFunc($data);

            $data->load($load_arr);
            foreach ($data->toArray() as $k => $v) {
                $arr = $this->items($data[$k], $transform, $code, $is_type);
                if (method_exists($arr, 'getData')) {
                    $transformData[$k] = $arr->getData();
                } else {
                    $transformData[$k] = $arr;
                }
            }
            return $transformData;
        } else if ($data instanceof AbstractPaginator) {
            $transformData = [];
            $load_arr = $this->existHasFunc($data);
            $data->load($load_arr);
            foreach ($data->toArray()['data'] as $k => $v) {
                $arr = $this->items($data[$k], $transform, $code, $is_type);
                if (is_array($arr)) {
                    $transformData[$k] = $arr;
                } else {
                    if (is_string($arr->getContent())) {
                        $transformData[$k] = json_decode($arr->getContent());
                    } else {
                        $transformData[$k] = $arr->getContent();
                    }
                }
            }
            return $transformData;
        } else {
            if (is_null($data)) {
                return response([], $code);
            }
            $transformData = call_user_func_array([$transform, 'handle'], [$data]);
            $model = $transform->getModelName();
            if (count($model) <= 0) {
                return $transformData;
            }


            foreach ($installArr as $v) {
                if (strpos($v, '.') && method_exists($data, $this->getHasName(explode('.', $v)[0]))) {
                    $installChild = explode('.', $v);
                    $transformData['mate'] = $this->installChild($installChild, $data, $transform);
                } else {
                    $transformData = $this->install($v, $model, $transform, $data, $transformData);
                }
            }
            return $transformData;
        }
    }

    /**
     * 二级到N级关联
     * @param array $arr
     * @param $data
     * @param $transform
     * @return array
     * @throws Exception
     * @throws \ReflectionException
     * @author yansong
     */
    protected function installChild(array $arr, $data, $transform): array
    {
        $new_data = [];
        $model = $transform->getModelName();
        for ($i = 0; $i < count($arr); $i++) {
            $str = $this->getHasName($arr[$i]);
            if ($transform instanceof Transform) {
                if (method_exists($transform, $this->key . Str::title($arr[$i]))) {
                    if (!($data instanceof Collection)) {
                        $child_transform_data = call_user_func_array([$transform, $this->key . Str::title($arr[$i])], [$data]);
                    } else {
                        break;
                    }

                    if (method_exists($child_transform_data, 'getContent')) {
                        $child_transform_data = $child_transform_data->getContent();
                    }

                    if (is_string($child_transform_data)) {
                        $child_transform_data = json_decode($child_transform_data, true);
                    }

                    $new_data[$arr[$i]] = $child_transform_data['data'] ?? [];
                    if (count($child_transform_data) > 0) {
                        $child_transform = $transform->getModelTransForm();
                        if (isset($child_transform[$arr[$i]])) {
                            $transformation = new \ReflectionClass($child_transform[$arr[$i]]);
                        } else {
                            break;
                        }
                        if ($data instanceof Collection) {
                            $transform = $transformation->newInstance();
                            $datap = $data;
                            $collections = [];
                            foreach ($datap->toArray() as $ke => $va) {
                                $child_data = $datap[$ke]->$str;
                                $child = $this->items($child_data, $transform, 200, true);
                                if (method_exists($child, 'getContent')) {
                                    $collections[$ke] = $child->getContent();
                                } else {
                                    $collections[$ke] = $child;
                                }
                            }
                            $new_data[$arr[$i]] = $collections;
                        } else {
                            $data = $data->$str;
                            $transform = $transformation->newInstance();
                        }
                    }
                }
            } else {
                throw new Exception('您的转换层有问题');
            }
        }
        return $new_data;
    }

    protected function modelHas($data, $item): bool
    {
        if (empty($data[$item])) {
            return true;
        }
        return false;
    }

    /**
     * 获取关联名
     * @param $item
     * @return string
     * @author: chenyansong
     * @Time: 2021/7/6 10:18
     */
    protected function getHasName($item): string
    {
        $item = Str::title($item);
        return "get" . str_replace("_", "", $item);
    }

    /**
     * 一级关联
     * @param $item
     * @param array $model
     * @param $transform
     * @param $data
     * @param $transformData
     * @return mixed
     * @author yansong
     */
    protected function install($item, array $model, $transform, $data, $transformData)
    {
        if (in_array($item, $model)) {
            $mate = call_user_func_array([$transform, $this->key . Str::title($item)], [$data]);

            if (method_exists($mate, 'getContent')) {
                $mate = $mate->getContent();
            }

            $keys = array_keys($transformData);

            if ($keys !== array_keys($keys)) {
                if (is_string($mate)) {
                    $mate = json_decode($mate);
                }
                $transformData['mate'][$item] = $mate->data ?? [];
            }
        }
        return $transformData;
    }

    /**
     * 分页转换
     * @param $data
     * @param $transform
     * @param int $code
     * @return Response
     * @throws Exception
     * @throws \ReflectionException
     * @author yansong
     */
    public function paginate($data, $transform, int $code = 200): Response
    {
        $arr = $this->items($data, $transform, $code);
        if ($arr instanceof HttpResponse) {
            $arr = $arr->getData();
        }

        return $this->array(['data' => $arr])->setMate([
            'total' => $data->total(),
            'per_page' => $data->perPage(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'code' => $code
        ]);
    }

    protected function existHasFunc($data): array
    {
        $install = Request::only([$this->key]);
        $install[$this->key] = $install[$this->key] ?? '';
        $installArr = explode(',', $install[$this->key]);
        $load_arr = [];
        foreach ($installArr as $v) {
            $load_str = "";
            $installChildArr = explode('.', $v);
            if (!method_exists($data[0], $this->getHasName($installChildArr[0]))) {
                break;
            }
            foreach ($installChildArr as $va) {
                $load_str .= $this->getHasName($va) . ".";
            }
            if (!empty($load_str)) {
                array_push($load_arr, trim($load_str, "."));
            }
        }
        return $load_arr;
    }

    /**
     * @param $item
     * @param int $code
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|HttpResponse
     * @author: chenyansong
     * @Time: 2021/4/26 23:34
     */
    public function collection($item, int $code = 200)
    {
        return response($item, $code);
    }

    /**
     * 设置自定义参数
     * @param array $arr
     * @return Response
     * @author yansong
     */
    public function setMate(array $arr): Response
    {
        $data = $this->getContent();
        $data = (array)json_decode($data, true);
        if (!empty($data['mate'])) {
            foreach ($arr as $k => $v) {
                $data['mate'][$k] = $v;
            }
        } else {
            $data['mate'] = $arr;
        }

        $data = json_encode($data);
        return $this->setContent($data);
    }


}

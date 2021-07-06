<?php


namespace Wdnmd\services;


class CollectionService extends AbstractService
{
    public function handle($data, $transform, int $code = 200, bool $is_type = false)
    {
        $transformData = [];
        dump($data);
        dd($data);
        $load_arr = $this->existHasFunc($data);
        if (!method_exists($data, "load")) {
            dd($data);
        }
        $data->load($load_arr);
        foreach ($data->toArray() as $k => $v) {
            $arr = $this->handle($data[$k], $transform, $code, $is_type);
            if (method_exists($arr, 'getData')) {
                $transformData[$k] = $arr->getData();
            } else {
                $transformData[$k] = $arr;
            }
        }
        return $transformData;
    }
}
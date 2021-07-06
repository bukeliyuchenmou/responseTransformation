<?php


namespace Wdnmd\services;


use Illuminate\Support\Facades\Request;
use Wdnmd\traits\Common;

abstract class AbstractService
{
    use Common;

    protected $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    abstract public function handle($data, $transform, int $code = 200, bool $is_type = false);


}
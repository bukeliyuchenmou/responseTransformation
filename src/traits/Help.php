<?php

namespace Wdnmd\src\traits;

use Wdnmd\src\Response;
use Exception;

/**
 * 帮助
 * Trait Help
 * @package Wdnmd\src\traits
 */
trait Help
{
    public $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * 通过函数的方式访问response对象
     * @author yansong
     * @param $name
     * @param $arguments
     * @return Response
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        if ($name == 'response') {
            return $this->response;
        }  else {
            throw new Exception('找不到该方法');
        }
    }
}

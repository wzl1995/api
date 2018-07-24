<?php
namespace Api\Controller;

/**
 * Api 首页信息
 */
use Common\Controller\IController;


/**
 * User登录
 */
class IndexController extends IController {

    /**
     *接口首页
     */
    public function index()
    {
        $welcome = '欢迎来到小的来了！！';
        die($welcome);
    }


}
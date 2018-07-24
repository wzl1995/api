<?php
namespace Api\Controller;

/**
 * 用户相关接口
 */
use Common\Controller\BaseController;
use Common\Controller\IController;
use Common\Lib\DateUtils;
use Think\Controller;


/**
 * User登录
 */
class PublicController extends BaseController {

    /**
     *用户登录接口
     */
    public function login()
    {
        $user_name=IV('user_name','require');
        $password=IV('password','require');

        /** 获取商户登录信息  */
        $user = D('Shop')->getUserByNameAndPass($user_name,$password);
        if(!$user)
        {
           IE('账号或密码错误!','');
        }
        $this->iSuccess($user,'shop_info');
    }

}
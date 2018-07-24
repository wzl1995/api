<?php
namespace Api\Controller;

/**
 * 店铺优惠券接口
 */
use Common\Controller\IController;
use Common\Lib\DateUtils;


class ShopCouponController extends IController {

    /**
     *  获取优惠券列表
     */
    public function list()
    {
        $filter['a.shop_id'] = $this->shop_id;
        $filter['status'] = IV('status','require');       // 过滤优惠券状态
        $page = initPage('a.create_time desc');  // 初始化分页

        /** 获取优惠券列表  */
        $coupon_list = D('Coupon')->getCoupons($filter,$page);

        $this->iSuccess($coupon_list,'coupon_list');
    }


    /**
     * 获取单个优惠券信息
     */
    public function get()
    {
        $filter['a.shop_id'] = $this->shop_id;
        $filter['a.id'] = IV('coupon_id','require');
        $coupon = D('Coupon')->getCoupon($filter);
        $this->iSuccess($coupon,'coupon');
    }


    /**
     * 保存优惠券
     */
    public function  save()
    {
        $data['shop_id'] = IV('token','require|token');
        $data['coupon_id'] = IV('coupon_id');
        $data['route'] = IV('route','require');
        $data['start_time'] = strtotime(IV('start_time','require'));
        $data['end_time'] = strtotime(IV('end_time','require'));
        $data['type'] = 1;
        D('Coupon')->saveCoupon($data);
        $this->iSuccess('','info');
    }


    /**
     * 下架优惠券
     */
    public function  offline()
    {
        $data['shop_id'] = IV('token','require|token');
        $data['coupon_id'] = IV('coupon_id');
        $data['status'] = 0;
        D('Coupon')->saveCoupon($data);
        $this->iSuccess('','info');
    }



    /**
     * 上架优惠券
     */
    public function  online()
    {
        $data['shop_id'] = IV('token','require|token');
        $data['coupon_id'] = IV('coupon_id');
        $data['status'] = 1;
        D('Coupon')->saveCoupon($data);
        $this->iSuccess('','info');
    }
}
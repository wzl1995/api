<?php
namespace Api\Model;

/**
 * 店铺优惠券
 */
use Common\Lib\ArrayUtils;
use Common\Lib\CDNUtils;
use Rest3\Model\LookValueModel;

class CouponModel extends IModel {

    /**
     * @param array $filter 搜索条件
     * @param array $page   页数条件
     * @return array        优惠券列表
     */

        public function getCoupons($filter=array(),$page=array()){

            if(!$page['order'])
                $page['a.order'] = 'create_time desc';
            $list = M('coupon')
                ->alias('a')
                ->join('LEFT JOIN hm_shop_info b ON b.id = a.shop_id')
                ->field('
                    a.id,                     -- 优惠券ID
                    b.store_name,             -- 优惠券标题
                    a.route,                  -- 优惠信息：满减、折扣
                    a.start_time,             -- 优惠券开始时间
                    a.end_time,               -- 优惠券下架时间
                    a.status                                  
                ')
                ->where($filter)
                ->order($page['order'])
                ->limit($page['start'],$page['limit'])
                ->select();
            return $list;
        }


    /**
     * 获取单个优惠券
     * @param $data  优惠券数据
     */

    public function getCoupon($filter)
    {
        if(!$filter)
            return false;
        return M('coupon')
            ->alias('a')
            ->field('
                a.id,                     -- 优惠券ID
                a.route,                  -- 优惠信息：满减、折扣  
                FROM_UNIXTIME(a.start_time,\'%Y-%m-%d\')  start_time,  -- 优惠券开始时间  -- 优惠券结束时间
                FROM_UNIXTIME(a.end_time,\'%Y-%m-%d\')    end_time        
                ')
            ->where($filter)
            ->find();
    }


    /**
     * 保存优惠券
     * @param $data  优惠券数据
     */

        public function saveCoupon($data)
        {
            $coupon_id = $data['coupon_id'];

            $co = array();
            if($coupon_id)
            {
                $co['id'] = $coupon_id;
                $co['shop_id'] = $data['shop_id'];
                M('coupon')->where($co)->save($data);
            }
            else
            {
                M('coupon')->add($data);
            }

        }


}
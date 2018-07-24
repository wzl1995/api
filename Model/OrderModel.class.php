<?php
namespace Api\Model;

/**
 * 店铺订单
*/

class OrderModel extends IModel {

    /**
     * @param array $filter 搜索条件  新订单，进行中，已完成
     * @param array $page   页数条件
     * @return array        订单列表
     */

        public $shop_order_type_id = '13';

        public function getOrders($filter=array(),$page=array()){
            if(!$page['order'])
                $page['a.order'] = 'create_time desc';
            $list = M('orders')
                ->alias('a')
                ->join(' LEFT JOIN hm_orderdata b ON b.order_id = a.order_id')
                ->join(' LEFT JOIN hm_look_value c ON c.key = a.order_status AND c.group = "o.order_status" AND c.shop_id = "0"')
                ->join(' LEFT JOIN hm_look_value d ON d.key = a.order_status AND d.group = "o.errands_status" AND d.shop_id = "0"')
                ->join(' LEFT JOIN hm_look_value e ON d.key = a.pay_way AND e.group = "o.pay_way" AND e.shop_id = "0"')
                ->field('
                    a.order_id,             -- 订单ID
                    a.pay_money,            -- 实际支付金额
                    a.product_money,        -- 顶多产品总额，喊快递费
                    a.order_status,         -- 订单状态
                    a.errands_status,       -- 跑腿状态
                    c.value    status_info, -- 状态描述
                    d.value    errands_info,-- 跑腿描述
                    a.errands_time,         -- 期望送达时间
                    a.create_time,          -- 订单创建时间
                    a.user_name,            -- 用户名
                    a.mobile,               -- 用户手机号
                    a.user_addr,            -- 用户地址
                    e.value     pay_method, -- 支付方式
                    COUNT(b.order_id) good_count, -- 商品数量         -- 商品列表
                    CONCAT(\'[\',GROUP_CONCAT(\'{"id":"\',b.order_id,\'","name":"\',b.goods_name,\'","qty":"\',b.goods_num,\'"}\' order by b.goods_name),\']\') product_list        
                ')
                ->where($filter,array($filter['shop_id'],$this->shop_order_type_id))
                ->group('a.order_id')
                ->order($page['order'])
                ->limit($page['start'],$page['limit'])
                ->select();
            //echo M('orders')->getLastSql();
            return $list;
        }




    /**
     * 保存订单
     * @param $data  订单数据
     */

        public function saveOrder($data)
        {
            $id = $data['id'];

            $co = array();
            if($id)
            {
                $co['id'] = $id;
                $co['shop_id'] = $data['shop_id'];
                M('order')->where($co)->save($data);
            }
            else
            {
                M('order')->add($data);
            }

        }


        /**
         * 自动搜索提示,用户名和手机都选5个满足条件的记录，合并在一起
         */

    public function autocomplete($filter=array()){
        $search = $filter['search'];
        $limit = $filter['limit'];
        $filter['a.user_name'] = array('like','%'.$search."%");
        return  M('orders')
            ->alias('a')
            ->union('SELECT b.mobile name FROM hm_orders b WHERE b.sid = '.$filter['a.sid']. ' AND b.mobile like "%'.$search.'%" GROUP BY b.mobile LIMIT '.$limit)
            ->field('
                    a.user_name     name       
                ')
            ->where($filter)
            ->group('a.user_name')
            ->limit($limit)
            ->select();
    }


    /**
     * 获取订单详情页面
     * @param $filter   过滤条件
     */

    public function getInfo($filter=array()){
        $info = M('orders')
            ->alias('a')
            ->join(' LEFT JOIN hm_orderdata b ON b.order_id = a.order_id')
            ->join(' LEFT JOIN hm_look_value c ON c.key = a.order_status AND c.group = "o.order_status" AND c.shop_id = "0"')
            ->join(' LEFT JOIN hm_look_value d ON d.key = a.order_status AND d.group = "o.errands_status" AND d.shop_id = "0"')
            ->join(' LEFT JOIN hm_look_value e ON d.key = a.pay_way AND e.group = "o.pay_way" AND e.shop_id = "0"')
            ->field('
                    a.order_id,             -- 订单ID
                    a.pay_money,            -- 实际支付金额
                    a.product_money,        -- 顶多产品总额，喊快递费
                    a.order_status,         -- 订单状态
                    a.errands_status,       -- 跑腿状态
                    c.value    status_info, -- 状态描述
                    d.value    errands_info,-- 跑腿描述
                    a.errands_time,         -- 期望送达时间
                    FROM_UNIXTIME(a.create_time,\'%Y-%m-%d\')    create_time,          -- 订单创建时间
                    a.express_money,        -- 配送费用
                    a.packing_fee,          -- 包装费用
                    a.coupon_fee,           -- 红包费用 
                    a.user_name,            -- 用户名
                    a.mobile,               -- 用户手机号
                    a.user_addr,            -- 用户地址
                    e.value     pay_method, -- 支付方式
                    COUNT(b.order_id) good_count, -- 商品数量         -- 商品列表
                    CONCAT(\'[\',GROUP_CONCAT(\'{"id":"\',b.order_id,\'","name":"\',b.goods_name,\'","qty":"\',b.goods_num,\'"}\' order by b.goods_name),\']\') product_list        
                ')
            ->where($filter,array($filter['shop_id'],$this->shop_order_type_id))
            ->group('a.order_id')
            ->find();
        $info['pay_way_info'] = D('lookValue')->getValue('o.pay_way',$info['pay_way'],'');
        return $info;
    }






}
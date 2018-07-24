<?php
namespace Api\Controller;

/**
 * 店铺订单接口
 */
use Common\Controller\IController;
use Common\Lib\Date;
use Common\Lib\DateUtils;


class ShopOrderController extends IController {




    /**
     *  获取订单列表
     */
    public function list()
    {

        $filter['a.sid'] = IV('token','require|token');
        $filter['a.order_status'] = IV('status','require');
        $filter['FROM_UNIXTIME(a.create_time,\'%Y-%m-%d\')'] = IV('create_time','require');
        $page = initPage('a.create_time desc');  // 初始化分页
        $search = IV('search');

        $cost = 10;           // 预计接单10分钟 TODO：// 改成配置
        if($search) // 如果有搜索内容，搜索电话号码或者用户昵称
        {
            $filter['_string'] = 'a.mobile = "'.$search.'" OR a.user_name LIKE "'.$search.'"';
        }
        
        /** 获取订单列表  */
        $order_list = D('Order')->getOrders($filter,$page);

        $date = new Date();
        foreach ($order_list as &$item)
        {
            $item['time_desc'] = $date->timeDiff(date("Y-m-d H:i:s",$item['create_time']));  // 处理时间描述
            $errands_status = $item['errands_status'];   // 跑腿状态
            if($errands_status==0 || $errands_status == 2)    // 如果是还未接单，或者是已到店状态，那么需要带接单时间
            {
                $endtime = DateUtils::AfterTimeFrom($item['create_time'],$cost,'n');
                $item['left_secs'] = DateUtils::diffTimeStampSecs(strtotime($endtime),$item['create_time']);
            }
        }

        $this->iSuccess($order_list,'order_list');
    }



    /**
     *  获取异常订单列表
     */
    public function errlist()
    {
        $filter['a.sid'] = IV('token','require|token');
        $filter['a.order_status'] = IV('status','require');
        $page = initPage('a.create_time desc');  // 初始化分页

        /** 获取订单列表  */
        $order_list = D('Order')->getOrders($filter,$page);

        $date = new Date();
        foreach ($order_list as &$item)
        {

            $item['time_desc'] = $date->timeDiff(date("Y-m-d H:i:s",$item['create_time']));  // 处理时间描述
        }

        $this->iSuccess($order_list,'order_list');
    }


    /**
     * 确定退款
     */
    public function refund()
    {
        $filter['a.sid'] = IV('token','require|token');
        $order_id = IV('order_id','require');

        $url = MOBILE_SERVER.'/refund';
        $map = [
            'type' => '1',
            'status' => '1'
        ];
        $key = M('config')->where($map)->getField('key');
        $str = "key=$key&orderId=$order_id";
        echo D('Shop/order')->curlPost($url, $str);
    }


    /**
     * 自动补全搜索    // 在用户名和手机里面分别搜索5条匹配数据，合并起来展示给app
     */
    public function autocomplete()
    {
        $filter['a.sid'] = $this->shop_id;
        $filter['search'] = IV('search','require');
        $filter['limit'] = 5;
        /** 自动补全搜素  */
        $list = D('Order')->autocomplete($filter);
        $this->iSuccess($list,'list');
    }


    /**
     * 订单详情页
     */
    public function info()
    {
        $filter['a.sid'] = $this->shop_id;
        $filter['a.order_id'] = IV('order_id','require');
        /** 自动补全搜素  */
        $order = D('Order')->getInfo($filter);
        $this->iSuccess($order,'order');
    }



    /**
     * 删除订单（软删除，标记删除)
     */
    public function del()
    {
        $data['a.sid'] = $this->shop_id;
        $data['id'] = IV('id','require');
        $data['is_delete'] = 1;
        /** 自动补全搜素  */
        D('Order')->saveOrder($filter);
        $this->iSuccess('','info');
    }




}
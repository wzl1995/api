<?php
namespace Api\Model;

/**
 * 店铺model
 */
use Common\Lib\ArrayUtils;
use Common\Lib\CDNUtils;
use Common\Lib\DateUtils;
use Rest3\Model\LookValueModel;

class ShopModel extends IModel {

    /**
     * 获取店铺详情
     * @param $shop_id      店铺ID
     * @return array        店铺信息
     */
        public function getInfo($shop_id){

            $cond=array(
                'a.id'=>$shop_id
            );


            $info = M('shop')
                ->alias("a")
                ->join('LEFT JOIN hm_shop_info b ON b.store_id  = a.id')
                ->join('LEFT JOIN hm_shop_type c ON c.type = b.category AND c.id = store_type')
                ->field('
                    b.store_id,             -- 店铺ID
                    b.store_name,           -- 店铺名称
                    b.category,             -- 店铺类型
                    b.store_type,           -- 店铺类型ID
                    c.type_name,            -- 经营类别
                    b.packing_fee,          -- 包装费
                    CONCAT(\''.C('HTTP_SERVER').'\',b.logo)  logo,                 -- 店铺logo
                    b.store_status,         -- 店铺状态
                    b.store_notice,         -- 门店公告
                    b.tel,                  -- 店铺电话
                    b.prov,                 -- 省份
                    b.city,                 -- 城市
                    b.prov_id,              -- 省份id
                    b.city_id,              -- 城市id
                    b.store_address,        -- 店铺地址
                    b.open_time,            -- 营业开始时间
                    b.close_time,           -- 营业结束时间
                    IF(b.close_time > unix_timestamp(current_time),1,0) open_status,  -- 是否还在营业
                    b.mode
                ')
                ->where($cond)
                ->find();
            /**-----------------------获取跑腿选项-------------------*/
            $info['pick_model'] = D('LookValue')->getGroup('sp.pick_model');
            return $info;
        }


    /**
     * 保存店铺设置
     */
        public function saveShopInfo($filter,$item)
        {
            return D('ShopInfo')->where($filter)->save($item);
        }


    /**
     * 通过用户名和密码获取店铺账号
     * @param $user_name
     * @param $password
     * @return array|false|mixed|\PDOStatement|string|\think\Model
     */
        public function getUserByNameAndPass($user_name,$password){
            $rsapass =  pwd($user_name,$password);	//密码
            //$rsapass = password($user_name,$password);
            $cond=array(
                'a.store_login_name'=>$user_name,
                'a.store_cipher'=>$rsapass
            );

            $shop = M('shop')
                ->alias("a")
                ->join('LEFT JOIN hm_shop_info b ON b.store_id  = a.id')
                ->join('LEFT JOIN hm_agent_co c ON c.id  = b.co_id')
                ->join('LEFT JOIN hm_shop_messages d ON d.shop_id  = a.id  AND d.read = 0')
                ->field('
                    a.store_login_name,     -- 当前登录账号
                    a.store_token,          -- token
                    CONCAT(\''.C('HTTP_SERVER').'\',b.logo)  logo,                 -- 店铺logo
                    b.store_name ,          -- 店铺名称
                    b.store_status ,        -- 经营状态门店状态 0:禁用 1:启用 
                    b.store_point,          -- 店铺评分
                    c.real_name  co_name,   -- 职业经理人姓名
                    c.tel        co_tel,    -- 职业经理人电话
                    COUNT(d.id)  msg_c      
                ')
                ->where($cond)
                ->group('a.id')
                ->find();
            return $shop;
        }


    /**
     * 保存商户的意见和反馈
     * @param $data   建议反馈
     */
        public function saveAdvice($data){

            if(!$data) return;
            if(!$data['id'])
            {
                $data['create_time'] = DateUtils::NowTimeStamp();
                M('shop_advice')->add($data);
            }
            else
            {
                $co['id'] = $data['id'];
                M('shop_advice')->where($co)->save($data);
            }

        }



    /** TODO 错误码
     * 修改密码
     * @param array $data
     * @param $password
     * @return array|false|mixed|\PDOStatement|string|\think\Model
     */
    public function modifyPassword($data)
    {
        $shop_id = $data['shop_id'];
        $user_name = $data['user_name'];
        $old_password = $data['old_password'];
        $password = $data['password'];
        $exist = $this->countUserByNameAndPass($shop_id,$user_name,$old_password);
        if($exist) // 如果用户名和密码存在
        {
            $co['id'] = $shop_id;
            $item['store_cipher'] = pwd($user_name,$password);
            $this->where($co)->save($item);
            return true;
        }
        else
        {
            return ERR_OLDPASSWORD_CODE;         // 原始密码错误
        }

    }



    /**
     * 通过用户名和密码获取店铺账号数量，又来判断用户是否存在
     * @param $user_name
     * @param $password
     * @return array|false|mixed|\PDOStatement|string|\think\Model
     */
    public function countUserByNameAndPass($shop_id,$user_name,$password){
        $rsapass = pwd($user_name,$password);
        $cond=array(
            'a.id' => $shop_id,
            'a.store_login_name'=>$user_name,
            'a.store_cipher'=>$rsapass
        );

       return  M('shop')
            ->alias("a")
            ->where($cond)
            ->count();
    }



    /**
     * 获取店铺问题列表
     * @return array $list 问题列表
     */
    public function getShopHelps($filter,$page)
    {
        if(!$page['order'])
            $page['a.order'] = 'a.sort_num desc';
        $list = M('goods')
            ->alias('a')
            ->field('
                    a.id,                   -- 产品ID
                    a.title,                -- 问题title
                    a.body,                 -- 问题内容
                    a.useful,               -- 是否有用
                    a.usseless,             -- 没用数量
                    a.sort_num              
                ')
            ->where($filter)
            ->order($page['order']);

        if(isset($page['start']))
        {
            $list->limit($page['start'],$page['limit']);
        }
        $list = $list->select();

        return $list;
    }

    /**
     * 统计面板
     */
    public function dashboard($filter)
    {
        $static = M('shop')
            ->alias("a")
            ->join('LEFT JOIN hm_shop_info b ON b.store_id  = a.id')
            ->join('
LEFT JOIN
(
SELECT
        CONCAT(\'[\',GROUP_CONCAT(\'{"day":"\',a.daytime,\'","total":"\',a.total,\'"}\' order by a.daytime),\']\') order_static
FROM 
        (
            SELECT
                FROM_UNIXTIME(a.create_time, \'%Y%m%d\')     daytime,
                SUM(a.pay_money)  total
            FROM hm_orders a 
            WHERE a.create_time  BETWEEN  "'.$filter['start_date'].'" AND "'.$filter['end_date'].'" AND a.order_status  = "3" AND a.sid = "'.$filter['shop_id'].'"
            GROUP BY FROM_UNIXTIME(a.create_time, \'%Y%m%d\')
        ) a 
) c ON 1 = 1
                ')
            ->join('
LEFT JOIN 
(
SELECT
            COUNT(1)  total_qty,
            SUM(IF(a.point>=4.0,1,0))   high_qty,
            SUM(IF(a.point<4.0 AND a.point>2.0,1,0))   middle_qty,
            SUM(IF(a.point<=2,1,0))   low_qty
FROM
hm_comment a 
WHERE a.create_time  BETWEEN  "'.$filter['start_date'].'" AND "'.$filter['end_date'].'"  AND a.store_id = "'.$filter['shop_id'].'"
) d ON 1 = 1            
                ')
            ->field('
                c.order_static,         -- 订单统计数据
                d.total_qty,            -- 总数量
                d.high_qty,             -- 好评数量 -- 中评数量 
                d.middle_qty          
            ')
            ->where(['a.id'=>$filter['shop_id']])
            ->find();
        //echo M('shop')->getLastSql();
        return $static;
    }


    /**
     * 保存店铺设置
     * @param $filter
     * @param $data
     *
     */
    public function saveSetting($filter,$data)
    {
        $filter['group'] = 'setting';
        $result = M('look_value')->where($filter)->save($data);
        if($result===0)    // 只是影响行数,不能确定数据是否存在，如果result为false，可能是sql 出的问题，不能作为添加行的依据
        {
            $cc = M('look_value')->where($filter)->count();
            if($cc==0)  // 如果数据不存在
            {
                $filter['value'] = $data['value'];
                M('look_value')->add($filter);
            }
        }

    }


    /**
     * 获取门店分类
     */
    public function getShopTypes()
    {
        $filter['type'] = 2;
        return M('shop_type')
            ->alias('a')
            ->field('
                a.id,         -- 分类ID
                a.type_name,  -- 分类名
                a.img,                          -- 分类图片 -- 是否显示
                a.is_show    
                ')
            ->where($filter)
            ->order('a.weight')
            ->select();

    }


    /**
     * 获得店铺消息
     * @param $filter 过滤条件
     * @param $page   分页
     */

    public function getShopMessages($filter=array(),$page = array())
    {

        if(!$page['order'])
            $page['a.order'] = 'a.create_time desc';
        $list = M('shop_messages')
            ->alias('a')
            ->field('
                    a.id,                   -- 消息ID
                    a.title,                -- 消息title
                    a.content,              -- 消息内容
                    a.read,                 -- 是否已读     消息创建时间
                    FROM_UNIXTIME(a.create_time)    create_time           

                ')
            ->where($filter)
            ->order($page['order']);

        if(isset($page['start']))
        {
            $list->limit($page['start'],$page['limit']);
        }
        $list = $list->select();

        return $list;

    }


    /**
     * 保存消息
     * @param $filter   过滤条件
     * @param $data     数据
     */
public function saveMessage($filter,$data)
{
    if(!$filter)    return;
    M('shop_messages')->save($filter)->save($data);
}







}
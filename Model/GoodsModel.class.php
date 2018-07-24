<?php
namespace Api\Model;

/**
 * 店铺产品
 */
use Common\Lib\ArrayUtils;
use Common\Lib\CDNUtils;
use Rest3\Model\LookValueModel;

class GoodsModel extends IModel {

    /**
     * @param array $filter 搜索条件
     * @param array $page   页数条件
     * @return array        产品列表
     */

        public function getGoodss($filter=array(),$page=array()){

            if(!$page['order'])
                $page['a.order'] = 'a.sort_num desc';
            $list = M('goods')
                ->alias('a')
                ->field('
                    a.id,                   -- 产品ID
                    a.goods_name,           -- 产品名字
                    CONCAT(\''.C('HTTP_SERVER').'\',a.logo)  logo,  -- 产品图片
                    a.shop_price,           -- 产品单价
                    a.stock,                -- 库存数量，-1 表示无限
                    a.sales,                -- 销量    -- 排序序号 
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
     * 获取单个产品
     * @param $data  产品数据
     */

    public function getGoods($filter)
    {
        if(!$filter)
            return false;
        return M('goods')
            ->alias('a')
            ->join('LEFT JOIN hm_shop_label b ON b.id = a.label_id  AND b.shop_id = a.shop_id')
            ->field('
                a.id,     -- 产品ID
                a.goods_name,       -- 产品名字
                a.shop_price,       -- 产品价格
                CONCAT(\''.C('HTTP_SERVER').'\',a.logo)  logo,                 -- 产品图片
                CONCAT(\''.C('HTTP_SERVER').'\',a.sm_logo)  sm_logo,                 -- 产品小图
                a.seo_description,  -- 产品描述
                a.stock,            -- 产品库存
                a.label_id,         -- 分类ID     -- 分类名
                b.label_name        
                ')
            ->where($filter)
            ->find();
    }



    /**
     * 保存产品
     * @param $data  产品数据
     */

        public function saveGoods($data)
        {
            $id = $data['id'];

            $co = array();
            if($id)
            {
                $co['id'] = $id;
                $co['shop_id'] = $data['shop_id'];
                M('goods')->where($co)->save($data);
            }
            else
            {
                M('goods')->add($data);
            }

        }

    /**
     * 保存列表
     * @param $data   列表数据
     *
     */
        public function saveList($data)
        {
            if(!$data||$data['shop_id']) return;

            $items = $data['items'];

            foreach ($items as $item)
            {
                $item['shop_id'] = $data['shop_id'];
                $this->saveGoods($item);
            }
        }



    /**
     * 获取商品管理页面
     */
        public function getHome($data)
        {
            $shop_id = $data['shop_id'];
            $co['shop_id'] = $shop_id;
            $data =   M('shop')
                ->alias('a')
                ->join('LEFT JOIN hm_shop_info b ON b.store_id  = a.id')
                ->join("LEFT JOIN (SELECT CONCAT('[',GROUP_CONCAT('{\"id\":\"',id,'\",\"name\":\"',label_name,'\"}' order by sort_num),']') label_list FROM hm_shop_label WHERE shop_id = '$shop_id' GROUP BY shop_id) c ON 1 = 1")
                ->join("LEFT JOIN hm_goods d ON d.shop_id = a.id")
                ->field('
                    a.id,                                           -- 店铺ID
                    b.store_name,                                   -- 店铺名称
                    b.store_point,                                  -- 店铺评分
                    CONCAT(\''.C('HTTP_SERVER').'\',b.logo)  logo,  -- 店铺logo
                    c.label_list,                                   -- 店铺分类
                    COUNT(1)    total_qty,                          -- 全部商品
                    SUM(IF(d.stock = 0, 1, 0)) out_qty,             -- 已售空商品数量
	                SUM(IF(d.is_on_sale, 0, 1)) offsale_qty,        -- 已下架数量    -- 售卖中数量
	                SUM(IF(d.is_on_sale, 1, 0)) onsale_qty       
                ')
                ->where($co)
                ->find();

            return $data;
        }

}
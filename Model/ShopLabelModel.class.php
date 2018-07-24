<?php
namespace Api\Model;

/**
 * 店铺标签
 */
use Common\Lib\ArrayUtils;
use Common\Lib\CDNUtils;
use Rest3\Model\LookValueModel;

class ShopLabelModel extends IModel {

    /**
     * @param array $filter 搜索条件
     * @param array $page   页数条件
     * @return array        标签列表
     */

        public function getShopLabels($filter=array(),$page=array()){

            if(!$page['order'])
                $page['a.order'] = 'a.sort_num desc';
            $list = M('shop_label')
                ->alias('a')
                ->join('LEFT JOIN hm_shop_info b ON b.id = a.shop_id')
                ->field('
                    a.id,                   -- 标签ID
                    a.label_name,           -- 标签名字
                    a.is_show,              -- 是否显示( 备用，目前界面没展示)    -- 排序序号 
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
     * 获取单个标签
     * @param $data  标签数据
     */

    public function getShopLabel($filter)
    {
        if(!$filter)
            return false;
        return M('shop_label')
            ->field('
                id,                     -- 标签ID
                route,                  -- 优惠信息：满减、折扣
                start_time,             -- 标签开始时间
                end_time              
                ')
            ->where($filter)
            ->find();
    }


    /**
     * 保存标签
     * @param $data  标签数据
     */

        public function saveShopLabel($data)
        {
            $id = $data['id'];

            $co = array();
            if($id)
            {
                $co['id'] = $id;
                $co['shop_id'] = $data['shop_id'];
                M('shop_label')->where($co)->save($data);
            }
            else
            {
                M('shop_label')->add($data);
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
                $this->saveShopLabel($item);
            }
        }


    /**
     * 删除标签
     * @param $filter  过滤条件
     */
        public function delLable($filter)
        {
            if(!$filter) return false;  // 禁止没有过滤条件的删除
            return M('shop_label')->where($filter)->delete();
        }

}
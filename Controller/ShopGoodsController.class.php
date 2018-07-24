<?php
namespace Api\Controller;

/**
 * 店铺产品接口
 */
use Common\Controller\IController;
use Common\Lib\DateUtils;


class ShopGoodsController extends IController {

    /**
     *  获取产品列表
     */
    public function list()
    {
        $filter['a.shop_id'] = $this->shop_id;
        $filter['a.label_id'] = IV('label_id','require');
        $page = initPage('a.sort_num asc');  // 初始化分页
        $type = IV('type');
        switch ($type) {
            case 1:            // 已售空产品
                $filter['a.stock'] = 0;
                break;
            case 2:             // 已下架产品
                $filter['a.is_on_sale'] = 0;
                break;
            case 3:             // 售卖中产品
                $filter['a.is_on_sale'] = 1;
                break;

            default:                // 默认取全部评论
                break;
        }

        /** 获取产品列表  */
        $shopgoods_list = D('Goods')->getGoodss($filter,$page);

        $this->iSuccess($shopgoods_list,'shopgoods_list');
    }


    /**
     * 设置库存（是否无限）
     */

    public function saveStock()
    {
        $data['shop_id'] = $this->shop_id;
        $data['id'] = IV('id','require');
        $data['stock']  = IV('stock','require');
        D('Goods')->saveGoods($data);
        $this->iSuccess('','info');

    }


    /**
     * 商品产品排序
     */
    public function sort()
    {
        $data['shop_id'] = $this->shop_id;
        /**
         * 格式 array(
         *
                ['lable_id'=>'1','sort_num'=>'1'],
         *      ['lable_id'=>'1','sort_num'=>'1']
         * )
         */

        $data['list'] = IV('list','require|json');

        $data['list'] = json_decode($data['list'],true);

        D('Goods')->saveList($data);
    }
    /**
     * 获取单个产品信息
     */
    public function get()
    {

        $filter['a.shop_id'] = $this->shop_id;
        $filter['a.id'] = IV('id','require');
        $goods = D('Goods')->getGoods($filter);
        $this->iSuccess($goods,'goods');
    }


    /**
     * 保存产品
     */
    public function  save()
    {
        $data['shop_id'] = $this->shop_id;
        $data['id'] = IV('id');
        $data['goods_name'] = IV('goods_name','require');
        $data['logo'] = IV('file','require|file');
        $data['label_id'] = IV('label_id','require');
        $data['seo_description'] = IV('seo_description','require');
        $data['shop_price'] = IV('shop_price','require');
        $data['stock'] = IV('stock','require');
        D('Goods')->saveGoods($data);
        $this->iSuccess('','info');
    }


    /**
     * 下架产品
     */
    public function  offsale()
    {
        $data['shop_id'] = $this->shop_id;
        $data['id'] = IV('id');
        $data['is_on_sale'] = 0;
        D('Goods')->saveGoods($data);
        $this->iSuccess('','info');
    }



    /**
     * 上架产品
     */
    public function  onsale()
    {
        $data['shop_id'] = $this->shop_id;
        $data['id'] = IV('id');
        $data['is_on_sale'] = 1;
        D('Goods')->saveGoods($data);
        $this->iSuccess('','info');
    }


    /**
     * 获取商品管理页面数据
     */
    public function home()
    {
        $filter['shop_id'] = $this->shop_id;
        $data = D('Goods')->getHome($filter);
        $label_list = json_decode($data['label_list'],true);
        if($label_list)
        {
            $filter['label_id'] = $label_list[0]['id'];
            /** 获取默认的产品列表  */
            $data['goods_list'] = D('Goods')->getGoodss($filter);  //  获取默认产品列表
        }
        $this->iSuccess($data,'data');
    }
}
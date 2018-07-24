<?php
namespace Api\Controller;

/**
 * 店铺标签接口
 */
use Common\Controller\IController;
use Common\Lib\DateUtils;


class ShopLabelController extends IController {

    /**
     *  获取标签列表
     */
    public function list()
    {
        $filter['shop_id'] = $this->shop_id;
        $page = initPage('a.sort_num asc');  // 初始化分页
        /** 获取标签列表  */
        $shoplabel_list = D('ShopLabel')->getShopLabels($filter,$page);

        $this->iSuccess($shoplabel_list,'label_list');
    }


    /**
     * 商品标签排序
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

        D('ShopLabel')->saveList($data);
    }
    /**
     * 获取单个标签信息
     */
    public function get()
    {
        $shop_id = $this->shop_id;
        $id = IV('id','require');
        $filter['id'] = $id;
        $filter['shop_id'] = $shop_id;
        $shoplabel = D('ShopLabel')->getShopLabel($filter);
        $this->iSuccess($shoplabel,'shoplabel');
    }


    /**
     * 保存标签
     */
    public function  save()
    {
        $data['shop_id'] = $this->shop_id;
        $data['id'] = IV('id');
        $data['label_name'] = IV('label_name','require');
        D('ShopLabel')->saveShopLabel($data);
        $this->iSuccess('','info');
    }


    /**
     * 删除标签
     */
    public function del()
    {

        $filter['shop_id'] = $this->shop_id;
        $filter['id'] = IV('id','require');
        $result = D('ShopLabel')->delLable($filter);
        $this->iSuccess('','info');
    }

    /**
     * 下架标签
     */
    public function  offline()
    {
        $data['shop_id'] = $this->shop_id;
        $data['id'] = IV('id');
        $data['status'] = 0;
        D('ShopLabel')->saveShopLabel($data);
        $this->iSuccess('','info');
    }



    /**
     * 上架标签
     */
    public function  online()
    {
        $data['shop_id'] = $this->shop_id;
        $data['id'] = IV('id');
        $data['status'] = 1;
        D('ShopLabel')->saveShopLabel($data);
        $this->iSuccess('','info');
    }
}
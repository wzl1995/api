<?php
namespace Api\Controller;

/**
 * 用户相关接口
 */
use Common\Controller\IController;
use Common\Lib\DateUtils;


/**
 *
 */
class ShopController extends IController {

    /**
     *修改密码接口
     */
    public function modify_pass()
    {
        $data['shop_id'] = $this->shop_id;
        $data['user_name']=IV('user_name','require');
        $data['password']=IV('password','require');
        $data['old_password']=IV('old_password','require');
        /** 获取商户登录信息  */
        $user = D('Shop')->modifyPassword($data);

        $this->iSuccess($user,'shop_info');
    }




    /**
     * 获取店铺设置信息
     */
    public function get(){

        $info = D('Shop')->getInfo($this->shop_id);

        $this->iSuccess($info,'shop_info');
    }

    /**
     * 保存店铺名称
     */
    public function saveName(){
        $filter['store_id'] = $this->shop_id;         // 店铺ID
        $data ['store_name']=IV('store_name','require');   // 店铺公告
        D('Shop')->saveShopInfo($filter,$data);
        $this->iSuccess('','info');
    }


    /**
     * 保存店铺分类
     */
    public function saveCategory(){

        $filter['store_id'] = $this->shop_id;         // 店铺ID
        $data ['store_type']=IV('store_type','require');   // 店铺公告  TODO // 检测类型合法性
        D('Shop')->saveShopInfo($filter,$data);
        $this->iSuccess('','info');
    }


    /**
     * 保存店铺公告
     */
    public function saveNotice(){
        $filter['store_id'] = $this->shop_id;         // 店铺ID
        $data ['store_notice']=IV('store_notice','require');   // 店铺公告
        D('Shop')->saveShopInfo($filter,$data);
        $this->iSuccess('','info');
    }


    /**
     * 保存店铺logo
     */
    public function saveLogo(){
        $filter['store_id'] = $this->shop_id;         // 店铺ID
        $data ['logo']=IV('file','require|file');   // 店铺log
        D('Shop')->saveShopInfo($filter,$data);
        $this->iSuccess('','info');
    }


    /**
     * 保存店铺包装费
     */
    public function savePackfee(){

        $filter['store_id'] = $this->shop_id;         // 店铺ID
        $data ['packing_fee']=IV('packing_fee','require');   // 店铺包装费
        D('Shop')->saveShopInfo($filter,$data);
        $this->iSuccess('','info');
    }



    /**
     * 保存店铺营业状态
     */
    public function saveStatus(){

        $filter['store_id'] = $this->shop_id;         // 店铺ID
        $data ['store_status']=IV('store_status','require');   // 店铺经营状态
        D('Shop')->saveShopInfo($filter,$data);
        $this->iSuccess('','info');
    }


    /**
     * 保存店铺电话
     */
    public function saveTel(){

        $filter['store_id'] = $this->shop_id;         // 店铺ID
        $data ['tel']=IV('tel','require');   // 店铺电话
        D('Shop')->saveShopInfo($filter,$data);
        $this->iSuccess('','info');
    }


    /**
     * 保存店铺地址
     */
    public function saveAddress(){
        $filter['store_id'] = $this->shop_id;         // 店铺ID
        $data['prov'] = IV('prov','require');                       // 店铺所在省名
        $data['city'] = IV('city','require');                       // 店铺所在城市名
        $data['prov_id'] = IV('prov_id','require');                 // 店铺所在省ID
        $data['city_id'] = IV('city_id','require');                 // 店铺所在城市ID
        $data['store_address'] = IV('store_address','require');     // 店铺详细地址

        D('Shop')->saveShopInfo($filter,$data);
        $this->iSuccess('','info');
    }


    /**
     * 保存店铺营业时间
     */
    public function saveOpenTime(){
        $filter['store_id'] = $this->shop_id;         // 店铺ID
        $data['open_time'] = IV('open_time','require');                 // 店铺营业开始时间
        $data['close_time'] = IV('close_time','require');               // 店铺营业结束时间

        D('Shop')->saveShopInfo($filter,$data);
        $this->iSuccess('','info');
    }


    /**
     * 保存店铺配送方式
     */
    public function savePickModel(){

        $filter['store_id'] = $this->shop_id;         // 店铺ID
        $data['model'] = IV('model','require');                 // 配送方式
        D('Shop')->saveShopInfo($filter,$data);
        $this->iSuccess('','info');
    }


    /**
     * 用户反馈意见
     * @POST
     *
     */
    public function advice(){

        $data['shop_id'] = $this->shop_id;
        $data['title'] = IV('title','require');                    // 配送方式
        $data['content'] = IV('content','require');                    // 配送方式
        $data['image'] = IV('file','file');
        D('Shop')->saveAdvice($data);
        $this->iSuccess('','info');
    }


    /**
     * 获取店铺营销统计
     */
    public function dashboard()
    {
        $filter['shop_id'] = $this->shop_id;
        $filter['start_date'] = strtotime(IV('start_date','require'));                    // 统计开始时间
        $filter['end_date'] = strtotime(IV('end_date','require'));                    // 统计结束时间
        $data = D('Shop')->dashboard($filter);
        $this->iSuccess($data,'data');
    }


    /**
     * 店铺消息设置
     */
    public function setMessage()
    {
        $filter['store_id'] = $this->shop_id;         // 店铺ID
        $filter['key'] = 'message';
        $data['value'] = IV('message','require');
        D('Shop')->saveSetting($filter,$data);
        $this->iSuccess('','data');

    }


    /**
     * 店铺音量设置
     */
    public function setVolume()
    {
        $filter['store_id'] = $this->shop_id;         // 店铺ID
        $filter['key'] = 'volume';
        $data['value'] = IV('volume','require');
        D('Shop')->saveSetting($filter,$data);
        $this->iSuccess('','data');
    }


    /**
     * 获取店铺分类
     */
    public function getShopTypes()
    {
        $list = D('Shop')->getShopTypes();
        $this->iSuccess($list,'list');
    }


    /**
     * 获取系统消息
     */
    public function messages()
    {

        $filter['a.shop_id'] = $this->shop_id;
        $page = initPage('a.create_time desc');  // 初始化分页
        $list = D('Shop')->getShopMessages($filter,$page);
        $this->iSuccess($list,'list');
    }


    /**
     * 编辑消息已读
     */
    public function messageRead()
    {
        $filter['shop_id'] = $this->shop_id;
        $filter['id'] = IV('id','require');
        $data['read'] = 1;
        D('Shop')->saveMessage($filter,$data);
        $this->iSuccess('','info');
    }




    



    
}
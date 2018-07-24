<?php
namespace Api\Controller;

/**
 * 店铺评论接口
 */
use Common\Controller\IController;
use Common\Lib\DateUtils;


class ShopCommentController extends IController {


    /**
     * 获取评论首页数据
     */
    public function home()
    {
        $filter['a.id']  = $this->shop_id;
        $data = D('Comment')->getHome($filter);  // 取首页汇总数据
        $page = initPage('a.create_time desc');  // 初始化分页
        unset($filter['a.id']);
        $filter['a.store_id'] = $this->shop_id;
        $data['list'] = D('Comment')->getComments($filter,$page);  // 默认取10条全部评论出来,按时间倒叙
        $this->iSuccess($data,'data');
    }

    /**
     *  获取评论列表
     */
    public function list()
    {
        $filter['a.store_id'] = $this->shop_id;
        $type = IV('type');
        $has_content = IV('has_content');
        $page = initPage('a.create_time desc');  // 初始化分页
        switch ($type) {
            case 1:            // 获取好评评论
                $filter['a.point'] = array('gt',4.0);
                break;
            case 2:             // 获取中评数据
                $filter['a.point'] = array('between',3.0,4.0);
                break;
            case 3:             // 获取差评数据
                $filter['a.point'] = array('lt',3.0);
                break;
            case 4:             // 获取差评并且没有回复的数据
                $filter['a.point'] = array('lt',3.0);
                $filter['a.reply'] = '';
                break;
            case 5:             // 获取有图片的评论
                $filter['b.image_count'] = array('gt',0);
                break;
            default:                // 默认取全部评论
                break;
        }

        if($has_content) // 如果只看有内容的，要么有图片，要么有文本
        {
            $filter['_string'] = 'b.image_count>0 OR a.comment IS NOT NULL';
        }
        
        /** 获取评论列表  */
        $comment_list = D('Comment')->getComments($filter,$page);



        $this->iSuccess($comment_list,'comment_list');
    }


    /**
     * 店家回复某条评论
     */

    public function reply()
    {
        $data['store_id'] = $this->shop_id;
        $data['id'] = IV('id','require');
        $data['reply'] = IV('reply','require');
        D('Comment')->saveComment($data);
        $this->iSuccess('','info');
    }


    /**
     * 标记评论已读
     */

    public function markRead()
    {
        $data['store_id'] = $this->shop_id;
        $data['id'] = IV('id','require');
        $data['read'] = 1;
        D('Comment')->saveComment($data);
        $this->iSuccess('','info');
    }

}
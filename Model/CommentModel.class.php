<?php
namespace Api\Model;

/**
 * 店铺评论
 */

class CommentModel extends IModel {

    /**
     * @param array $filter 搜索条件  全部，好评，中评，差评，有图，差评未回
     * @param array $page   页数条件
     * @return array        评论列表
     */

        public $shop_comment_type_id = 13;      // 店铺评论图片
        public $comment_type = 1;   // 店铺评论

        public function getComments($filter=array(),$page=array()){

            $defaultAvatar =C('HTTP_SERVER').C('default_avatar');
            $filter['type'] = 1;  // 过滤是店铺的评论
            if(!$page['order'])
                $page['a.order'] = 'create_time desc';
            $list = M('comment')
                ->alias('a')
                ->join('
 LEFT JOIN 
 (              
            SELECT 
                    a.project_id,
                    count(1)   image_count,
                    GROUP_CONCAT(image_url) imagelist
            FROM hm_image  a 
            WHERE a.image_type = "'.$this->shop_comment_type_id.'"
            GROUP BY a.project_id
 ) b ON b.project_id = a.project_id '
                )
                ->field('
                    a.id,                   -- 评论ID
                    a.comment,              -- 评论内容
                    a.customer_id,          -- 用户ID
                    \'Huang\'     customer_name,      -- 名字
                    \''.$defaultAvatar.'\'     avatar,      -- 用户头像
                    a.fid,                  -- ??
                    a.point,                -- 评分
                    a.create_time,          -- 评论时间
                    a.thumbs_up,            -- 点赞数量
                    a.reply,                -- 店家回复
                    a.read,                 -- 是否已读    
                    \''.C('HTTP_SERVER').'\'    image_uri,      -- 图片地址
                    b.imagelist,            -- 评论的图片列表  -- 评论的图片数量  
                    b.image_count          
                ')
                ->where($filter)
                ->order($page['order'])
                ->limit($page['start'],$page['limit'])
                ->select();
            //echo M('comment')->getLastSql();
            return $list;
        }


    /**
     * 获取评论主页数据
     */
        public function getHome($filter)
        {

            $store_id  = $filter['a.id'];
            $data = M('shop')
                ->alias('a')
                ->join('LEFT JOIN hm_shop_info b ON b.store_id  = a.id')
                ->join('
 LEFT JOIN 
 (              
            SELECT 
                    a.project_id,
                    Count(1)    total_qty,
                    SUM(IF(a.point>=4.0,1,0))   high_qty,
                    SUM(IF(a.point<4.0 AND a.point>2.0,1,0))   middle_qty,
                    SUM(IF(a.point<=2,1,0))   low_qty,
                    SUM(IF(a.point<=2 && a.reply = "",1,0))   low_noreply_qty,
                    SUM(IF(b.image_count > 0,1,0))          image_qty        
            FROM hm_comment  a 
            LEFT JOIN 
                     (              
                                SELECT 
                                        a.project_id,
                                        count(1)   image_count
                                FROM hm_image  a 
                                WHERE a.image_type = "'.$this->shop_comment_type_id.'"
                                GROUP BY a.project_id
                     ) b ON b.project_id = a.project_id     
             WHERE a.type = "'.$this->comment_type.'"  AND a.store_id = "'.$store_id.'"
            ) c ON 1 = 1 '
                )
                ->field('
                    b.store_name ,          -- 店铺名称
                    b.logo,                 -- 店铺logo
                    b.store_status ,        -- 经营状态
                    b.store_point,          -- 店铺评分
                    c.total_qty,             -- 总共评论数量
                    c.high_qty,              -- 好评数量 
                    c.middle_qty,             -- 中评数量
                    c.low_qty,             -- 差评数量
                    c.low_noreply_qty,             -- 差评并且没有回复的数量       -- 有图片评论的数量
                    c.image_qty
                ')
                ->where($filter)
                ->find();
            //echo M('shop')->getLastSql();
            return $data;
        }

    /**
     * 保存评论
     * @param $data  评论数据
     */

        public function saveComment($data)
        {
            $id = $data['id'];

            $co = array();
            if($id)
            {
                $co['id'] = $id;
                $co['store_id'] = $data['store_id'];
                M('comment')->where($co)->save($data);
            }
            else
            {
                M('comment')->add($data);
            }

        }


}
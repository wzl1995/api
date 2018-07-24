<?php
namespace Api\Model;

use Think\Model;

/**
 * 存放系统的基本配置类
 */
class LookValueModel extends Model{
    /**
     * @param $group string  分组名称
     * @return array
     */
    public function getGroup($group,$default = array()){
       $co=array(
          'group'=>$group
       );
       $list =$this->where($co)->field('key,value')->order('sort_id')->select();

       return $list?$list:$default;
    }

    /**
     * @param $group string 分组名称
     * @param $key  key
     * @return string   value
     */
    public function getValue($group,$key,$default = ''){
        $co=array(
            'group'=>$group,
            'key'=>$key
        );
        $value =$this->where($co)->field('key,value')->find();
        return $value?$value:$default;
    }

}
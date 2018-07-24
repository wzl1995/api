<?php
namespace Rest3\Model;

use Think\Model;
use Common\Lib\String;
use Common\Lib\Date;

/**
 * 目前存放在mysql里面,后面迁移到memcached里面去。
 */
class UserTokenModel extends Model{

    #颁发token,只是在注册和登陆的时候颁发
    public function genToken($user_id){
        
       
       $co=array(
          'user_id'=>$user_id
       );

       $it =$this->where($co)->find();

       if(!$it){
            
            #不存在则写入
            $data=array(
                'actived'=>Date::Now(),
                'created'=>Date::Now(),
                'token'=>String::keyGen(),
                'user_id'=>$user_id
            );

            $this->add($data);

            return $data['token'];

       }else{

            #存在则更新活跃时间
            $data=array(
                'actived'=>Date::Now(),
            );

            $this->where($co)->save($data);

            return $it['token'];
       }
    }

    #token是否有效
    public function isValid($token){

        $co=array('token'=>$token);
        $n= $this->where($co)->count();

        if($n==0){
            return false;
        }else{
            return true;
        }
    }

    public function getUserID($token){
        return $this->where(array('token'=>$token))->cache(true)->getField('user_id');
    }
}
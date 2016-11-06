<?php
/**
 * 用户登录类
 * @since   2016-02-18
 * @author  zhaoxiang <zhaoxiang051405@outlook.com>
 */

namespace app\admin\controller;


use app\admin\model\UserData;

class User extends Base {

    /**
     * 用户登录函数
     * @return mixed|void
     */
    public function login(){
        if( $this->request->isPost() ){
            $username = $this->request->post('username');
            $password = $this->request->post('password');
            if( !$username || !$password ){
                $this->error('缺少关键数据！','');
            }
            $userModel = new \app\admin\model\User();
            $password = $userModel->getPwdHash($password);
            $userInfo = $userModel->where(['username' => $username, 'password' => $password])->find();
            if( empty($userInfo) ){
                $this->error('用户名或者密码错误！','');
            }else{
                if( $userInfo['status'] ){
                    //保存用户信息和登录凭证
                    session('uid', $userInfo[$this->primaryKey]);
                    cache($userInfo[$this->primaryKey], session_id(), config('online_time'));
                    //获取跳转链接，做到从哪来到哪去
                    if( $this->request->has('from', 'get') ){
                        $url = $this->request->get('from');
                    }else{
                        $url = url('Index/index');
                    }
                    //更新用户数据
                    $userData = UserData::get(['uid' => $userInfo[$this->primaryKey]]);
                    if( $userData ){
                        $userData->loginTimes += 1;
                        $userData->save();
                    }else{
                        $newUserData = new UserData();
                        $newUserData->loginTimes = 1;
                        $newUserData->uid = $userInfo[$this->primaryKey];
                        $newUserData->save();
                    }
                    $this->success('登录成功', $url);
                }else{
                    $this->error('用户已被封禁，请联系管理员','');
                }
            }
        }else{
            return $this->fetch();
        }
    }

    public function add(){

    }
}
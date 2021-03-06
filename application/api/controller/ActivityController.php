<?php

namespace app\api\controller;


use app\api\model\Activity;

use app\api\model\ActivityAttend;
use app\api\model\User;
use app\back\model\Base;
use think\Db;
use think\Request;


class ActivityController extends BaseController {
    /**
     *前进行的活动
     * zhuangxiu-zyg
     *
     * @return \think\Response
     */
    public function index(Request $request) {
        return json(Activity::getListNow());
    }

    /**
     *前台历史活动
     * zhuangxiu-zyg
     *
     * @return \think\Response
     */
    public function history_activity(Request $request) {
        return json(Activity::getListHistory());
    }

    /**
     *活动内页
     * zhuangxiu-zyg
     *
     * @return \think\Response
     */
    public function read(Request $request) {
        $data = $request->param();
        $rule = ['activity_id' => 'require|number'];
        $res = $this->validate($data, $rule);
        //dump( $res);exit;
        if ($res !== true) {
            return json(['code' => __LINE__, 'msg' => $res]);
        }
        return json(Activity::findOne($data['activity_id']));
    }

    /**
     * 添加报名
     * zhuangxiu-zyg
     *
     * @return \think\Response
     */
    public function save(Request $request) {
        $data = $request->param();
        $rule = ['activity_id' => 'require|number','username'=>'require', 'truename' => 'require', 'mobile' => 'require', 'xiaoqu' => 'require'];
        $res = $this->validate($data, $rule);
        //dump( $res);exit;
        if ($res !== true) {
            return json(['code' => __LINE__, 'msg' => $res]);
        }
        $user_id = User::getUserIdByName($data['username']);
        if(is_array($user_id)){
            return json($user_id);
        }

        $data['user_id'] = $user_id;unset($data['username']);
        //is add ?
        $row_attend = Db::table('activity_attend')->where(['user_id'=>$user_id,'activity_id'=>$data['activity_id']])->find();
        if($row_attend){
             $data['update_time'] = time();
            Db::table('activity_attend')->where(['user_id'=>$user_id,'activity_id'=>$data['activity_id']])->update($data);
            return json(['code'=>'0','msg'=>'update attend ok']);
        }
        $data['create_time'] = time();
        $data['update_time'] = time();
        if (!Db::table('activity_attend')->insert($data)) {
            return json(['code' => __LINE__, 'msg' => 'save attend error']);
        }
        //增加活动人数
        (new Activity())->where('id', $data['activity_id'])->setInc('pnum');
        return json(['code' => 0, 'msg' => 'save attend ok']);
    }

    /**
     * 取我的报名
     * zhuangxiu-zyg
     *
     * @return \think\Response
     */
    public function read_attend(Request $request){
        $data = $request->param();
        $rule = ['activity_id' => 'require|number','username'=>'require'];
        $res = $this->validate($data, $rule);
        //dump( $res);exit;
        if ($res !== true) {
            return json(['code' => __LINE__, 'msg' => $res]);
        }
        $user_id = User::getUserIdByName($data['username']);
        if(is_array($user_id)){
            return json($user_id);
        }
        $row_attend = Db::table('activity_attend')->where(['user_id'=>$user_id,'activity_id'=>$data['activity_id']])->find();
        if($row_attend){
            return json(['code' => 0, 'msg' => 'my attend ok','data'=>$row_attend]);
        }
        return json(['code' => __LINE__, 'msg' => 'my attend not']);
    }
    /*
     * 取我的在线报名
     * zhuangxiu-zyg
     * */

    public function my_attend(Request $request) {
        $data=$request->param();
        $rule = ['username'=>'require'];
        $res = $this->validate($data, $rule);
        //dump( $res);exit;
        if ($res !== true) {
            return json(['code' => __LINE__, 'msg' => $res]);
        }
        return json(ActivityAttend::getMyAttend($data['username']));
    }



}

<?php
    namespace app\api\model;
    use app\back\model\Base;
    use think\Model;

    class Tuangou extends Base{
        /**
         * 查询正在进行的团购活动
         */
        public static function getGoon(){
            $field = 'good.id good_id,good.img good_img,good.name good_name';
            $where = ['tuangou.st'=>['=',1]];
            $list_ = self::where($where)->join('good','good.id=tuangou.good_id')->field($field)->select();
            return $list_;
        }

        /**
         * 查询历史的团购活动
         */
        public static function getHistory(){
            $field = 'good.id good_id,good.img good_img,good.name good_name,price_group';
            $where = ['tuangou.st'=>['=',3]];
            $list_ = self::where($where)->join('good','good.id=tuangou.good_id')->field($field)->select();
            return $list_;
        }


    }
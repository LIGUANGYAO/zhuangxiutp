<?php

namespace app\api\model;

use think\Model;

class Cart extends model {

    public function getStAttr($value) {
        $status = [0 => 'deleted', 1 => '正常'];
        return $status[$value];
    }

    public function getTypeAttr($value) {
        $status = [1 => '行业', 2 => '百科'];
        return $status[$value];
    }

    /*
     * using
     * */
    public function addCart($data) {
        $user_id = User::getUserIdByName($data['username']);
        if (is_array($user_id)) {
            return $user_id;
        }

        $row_cart = self::where(['user_id' => $user_id, 'shop_id' => $data['shop_id']])->find();
        $row_good = Good::findOne($data['good_id']);
        if (is_array($row_good)) {
            return $row_good;
        }
        if (!$row_cart) {//没有此商家的购物车
            $data_cart['user_id'] = $user_id;
            $data_cart['shop_id'] = $data['shop_id'];
            $data_cart['sum_price'] = $row_good->price * $data['num'];

            $this->save($data_cart);
            $data_good = ['cart_id' => $this->id, 'good_id' => $row_good->id, 'shop_id' => $row_good->shop_id, 'num' => $data['num']];
            return (new CartGood)->addGood($data_good);

        }

        $row_cart->sum_price += $row_good->price * $data['num'];
        $row_cart->st = 1;
        $row_cart->save();
        $data_good = ['cart_id' => $row_cart->id, 'good_id' => $row_good->id, 'shop_id' => $row_good->shop_id, 'num' => $data['num']];

        return (new CartGood)->addGood($data_good);

    }
/*
 * using
 * */
    public static function getListByUser($username) {
        $user_id = User::getUserIdByName($username);
        if (is_array($user_id)) {
            return $user_id;
        }
        $list_cart = self::where(['user_id' => $user_id, 'cart.st' => 1])->join('shop','cart.shop_id=shop.id')->field('cart.id cart_id,cart.shop_id,sum_price,shop.name shop_name')->select();
        if ($list_cart->isEmpty()) {
            return ['code' => __LINE__, 'msg' => 'cart good not exsits'];
        }

        foreach ($list_cart as $k=>$cart) {
            $list_cart[$k]['shop_goods'] = CartGood::getGoodsByShop($cart->shop_id);
        }
        return ['code' => 0, 'msg' => 'get cart shop and goods ok', 'data' => $list_cart];

    }



}
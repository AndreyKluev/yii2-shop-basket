<?php

namespace andreykluev\shopbasket\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\InvalidParamException;
use yii\db\ActiveRecord;

use common\models\User;

class BasketProductBehavior extends Behavior
{
	public function getUser()
	{
		return $this->owner
			->hasMany('\common\models\User', ['id' => 'id_user'])
//			->hasMany($this->owner->productClass, ['id' => 'id_product'])
			->via('user_product')
			->pivot('count', 'price', 'inserted_at');
	}

    public function isInBasket($yiiComponent)
    {
        return $yiiComponent->isProductInBasket($this->owner->id);
    }
}
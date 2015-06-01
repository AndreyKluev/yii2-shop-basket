<?php

namespace andreykluev\shopbasket\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\InvalidParamException;
use yii\db\ActiveRecord;

use common\models\User;
use common\models\Product;
use andreykluev\shopbasket\models\UserBasket;

class BasketUserBehavior extends Behavior
{
	public function init()
	{
	}

	// Связывает пользователя с корзиной
    public function getUserBasket()
	{
		return $this->owner
			->hasMany(UserBasket::className(), ['id_user' => 'id']);
	}

	/**
	 * Возвращает количество наименований товара в корзине
	 * @return int
	 */
	public function getBasketCount()
	{
		return $this->owner
			->getUserBasket()
			->count();
	}

	/**
	 * Возвращает количество единиц товаров в корзине
	 * @return int
	 */
	public function getBasketTotal()
	{
        $total = $this->owner
            ->getUserBasket()
			->sum('count');

		return ($total) ? $total : 0;
	}

	/**
	 * Возвращает сумму товаров в корзине
	 * @return int
	 */
	public function getBasketCost()
	{
		$cost = $this->owner
            ->getUserBasket()
			->sum('price*count');

		return ($cost) ? $cost : 0;
	}

	/**
	 * Проверяет, присутствует ли товар в корзине пользователя
	 * @param $pid
	 * @return int|string
	 */
	public function isProductInBasket($hash)
	{
		return User::find()
			->joinWith('userBasket')
			->where([
				'id_user' => Yii::$app->user->identity->getId(),
				'hash_product' => $hash
			])
			->count();
	}
}
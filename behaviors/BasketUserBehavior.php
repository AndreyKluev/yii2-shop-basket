<?php

namespace andreykluev\shopbasket\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\InvalidParamException;
use yii\db\ActiveRecord;

use common\models\User;
use andreykluev\shopbasket\models\UserProduct;

class BasketUserBehavior extends Behavior
{
	public function init()
	{
	}

	public function getUserProducts()
	{
		return $this->owner
			->hasMany(UserProduct::className(), ['id_user' => 'id']);
	}

	public function getBasketProducts()
	{
		return $this->owner
			->hasMany('\common\models\Product', ['id' => 'id_product'])
//			->hasMany($this->owner->productClass, ['id' => 'id_product'])
			->via('userProducts')
		;
	}

	/**
	 * Возвращает количество наименований товара в корзине
	 * @return int
	 */
	public function getBasketCount()
	{
		return $this->owner
			->getBasketProducts()
			->count();
	}

	/**
	 * Возвращает количество единиц товаров в корзине
	 * @return int
	 */
	public function getBasketTotal()
	{
		$total = User::find()
			->joinWith('basketProducts')
			->where(['user.id' => Yii::$app->user->identity->getId()])
			->sum('count');

		return ($total) ? $total : 0;
	}

	/**
	 * Возвращает сумму товаров в корзине
	 * @return int
	 */
	public function getBasketCost()
	{
		$cost = User::find()
			->joinWith('basketProducts')
			->where(['user.id' => Yii::$app->user->identity->getId()])
			->sum('user_product.price*user_product.count');

		return ($cost) ? $cost : 0;
	}

	/**
	 * Проверяет, присутствует ли товар в корзине пользователя
	 * @param $pid
	 * @return int|string
	 */
	public function isProductInBasket($pid)
	{
		return User::find()
			->joinWith('basketProducts')
			->where([
				'user.id' => Yii::$app->user->identity->getId(),
				'product.id' => $pid
			])
			->count();
	}

	public function getProductInBasket($pid)
	{
		$x = User::findOne(Yii::$app->user->identity->getId())
			->getUserProducts()
			->with('product')
			->where(['id_product' => $pid])
			->one();
/*
		var_dump($x[0]->product);
		die();
*/
		return $x;
	}

}
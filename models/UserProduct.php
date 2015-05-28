<?php

namespace andreykluev\shopbasket\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Class UserProduct
 * @package andreykluev\shopbasket\models
 */
class UserProduct extends ActiveRecord
{
	/**
	 * Связь элемента корзины с товаром
	 * @return \yii\db\ActiveQuery
	 */
	public function getProduct()
	{
		return $this
			->hasOne('\common\models\Product', ['id' => 'id_product']);
	}

	/**
	 * Связь элемента корзины с пользователем
	 * @return \yii\db\ActiveQuery
	 */
	public function getUser()
	{
		return $this
			->hasOne('\common\models\User', ['id' => 'id_user']);
	}
}
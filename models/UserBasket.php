<?php

namespace andreykluev\shopbasket\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

use common\models\Product;

/**
 * Class UserProduct
 * @package andreykluev\shopbasket\models
 */
class UserBasket extends ActiveRecord
{
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

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

    /**
     * Возвращает массив id-шников товаров, добавленных в корзину
     * @param $storage
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getUserBasketIds($storage)
    {
        $x = static::find([
            'group' => $storage,
            'id_user' => Yii::$app->user->identity->getId(),
        ])
        ->select('id_product as id')
        ->asArray()
        ->all();

        return ArrayHelper::getColumn($x, 'id');
    }

    /**
     * Возвращает список всех товаров из корзины
     */
    public static function getAllProducts($ids)
    {
        $products = Product::find()
            ->where(['id' => $ids])
            ->all();

        return $products;
    }

}
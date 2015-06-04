<?php

namespace andreykluev\shopbasket\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\InvalidParamException;
use yii\db\ActiveRecord;

use common\models\User;

class BasketProductBehavior extends Behavior
{
    /**
     * Генерит уникальный Хэш-товара
     * @return string
     */
    public function getHash()
    {
        return md5($this->owner->id);
    }

    /**
     * Проверяет добавлен ли товар в корзину '$idComponent'
     * @param $idComponent
     * @return mixed
     */
    public function isInBasket($idComponent)
    {
        return Yii::$app->get($idComponent)->basket->isProductInBasket($this->getHash());
    }
}
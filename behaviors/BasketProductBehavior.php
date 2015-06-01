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
     * Проверяет добавлен ли товар в корзину
     * @param $yiiComponent
     * @return mixed
     */
    public function isInBasket($yiiComponent)
    {
        return $yiiComponent->isProductInBasket($this->getHash());
    }
}
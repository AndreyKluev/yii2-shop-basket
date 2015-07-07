<?php

namespace andreykluev\shopbasket\behaviors;

use Yii;
use yii\base\Behavior;

/**
 * Class BasketProductBehavior
 * @package andreykluev\shopbasket\behaviors
 */
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
     * @param string $idComponent
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function getBasketPrice($idComponent = 'basket')
    {
        $product = Yii::$app->get($idComponent)->basket->getProductById($this->getHash());
        return $product['price'];
    }

    /**
     * @param string $idComponent
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function getBasketCount($idComponent = 'basket')
    {
        $product = Yii::$app->get($idComponent)->basket->getProductById($this->getHash());
        return $product['count'];
    }

    /**
     * Проверяет добавлен ли товар в корзину '$idComponent'
     * @param $idComponent
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function isInBasket($idComponent)
    {
        return Yii::$app->get($idComponent)->basket->isProductInBasket($this->getHash());
    }
}
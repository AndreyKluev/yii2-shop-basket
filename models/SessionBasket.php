<?php

namespace andreykluev\shopbasket\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

use andreykluev\shopbasket\BasketInterface;

/**
 * Class SessionBasket
 * @package andreykluev\shopbasket\models
 */
class SessionBasket extends Model implements BasketInterface
{
    public function isProductInBasket($hash)
    {}

    public function getBasketProducts()
    {}

    public function getBasketCount()
    {}

    public function getBasketTotal()
    {}

    public function getBasketCost()
    {}
}
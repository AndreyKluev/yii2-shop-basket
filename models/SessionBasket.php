<?php

namespace andreykluev\shopbasket\models;

use Yii;
use yii\base\Model;
use yii\helpers\Json;
use yii\web\HttpException;

use andreykluev\shopbasket\BasketInterface;

/**
 * Class UserProduct
 * @package andreykluev\shopbasket\models
 */
class SessionBasket extends Model implements BasketInterface
{
    /**
     * Товары корзины
     */
    public $basketProducts;

    /**
     * Компонент от которого обращаемся
     */
    public $owner;

    /**
     * Загружает товары из сессии
     */
    public function loadProducts()
    {
        $this->basketProducts = Yii::$app->session->get($this->owner->storageName, []);
    }

    /**
     * Проверяет, присутствует ли товар в корзине пользователя
     * @param $hash - уникальный Хэш товара
     * @return boolean
     */
    public function isProductInBasket($hash)
    {
        $this->loadProducts();
        return isset($this->basketProducts[$hash]);
    }

    /**
     * Добавляет товар в корзину
     * @param $hash - уникальный Хэш товара
     * @param $pid - id товара
     * @param $price - цена товара
     * @param $params - дополнительные параметры товара
     * @param $count - количество
     * @return array
     * @throws HttpException
     */
    public function insertProduct($hash, $pid, $price, $params=[], $count=1)
    {
        $this->loadProducts();

        if(!$this->isProductInBasket($hash)) {
            $this->basketProducts[$hash] = [
                'count' => $count,
                'id_product' => $pid,
                'price' => $price,
                'params' => Json::encode($params),
                'created_at' => time(),
                'updated_at' => time()
            ];
        } else {
            // Если кол-во == 0, то удаляем из корзины
            if(0<$count) {
                $this->basketProducts[$hash]['count'] = $count;
                $this->basketProducts[$hash]['updated_at'] = time();
            } else {
                unset($this->basketProducts[$hash]);
            }
        }

        Yii::$app->session->set($this->owner->storageName, $this->basketProducts);

        return [
            'total' => [
                'count' => Yii::$app->formatter->asInteger( $this->getBasketCount() ),
                'total' => Yii::$app->formatter->asInteger( $this->getBasketTotal() ),
                'cost'  => Yii::$app->formatter->asCurrency( $this->getBasketCost(), 'RUR' )
            ],
            'result' => $this->isProductInBasket($hash)
        ];
    }

    /**
     * Возвращает список товаров в корзине
     * @return array
     */
    public function getBasketProducts()
    {
        $this->loadProducts();
        return $this->basketProducts;
    }

    /**
     * Возвращает количество наименований товара в корзине
     * @return int
     */
    public function getBasketCount()
    {
        $this->loadProducts();
        return count($this->basketProducts);
    }

    /**
     * Возвращает количество единиц товаров в корзине
     * @return int
     */
    public function getBasketTotal()
    {
        $this->loadProducts();

        $total = 0;
        foreach($this->basketProducts as $bp) {
            $total += $bp['count'];
        }

        return $total;
    }

    /**
     * Возвращает сумму товаров в корзине
     * @return float
     */
    public function getBasketCost()
    {
        $this->loadProducts();

        $cost = 0;
        foreach($this->basketProducts as $bp) {
            $cost += $bp['count'] * $bp['price'];
        }

        return $cost;
    }
}
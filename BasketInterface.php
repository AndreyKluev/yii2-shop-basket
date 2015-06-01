<?php
namespace andreykluev\shopbasket;

use yii\web\HttpException;

/**
 * Интерфейс корзины
 * @package andreykluev\shopbasket
 */
interface BasketInterface
{
    /**
     * Проверяет, присутствует ли товар в корзине пользователя
     * @param $hash - уникальный Хэш товара
     * @return boolean
     */
    public function isProductInBasket($hash);

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
    public function insertProduct($hash, $pid, $price, $params, $count=1);

    /**
     * Возвращает список товаров в корзине
     * @return array
     */
    public function getBasketProducts();

    /**
     * Возвращает количество наименований товара в корзине
     * @return int
     */
    public function getBasketCount();

    /**
     * Возвращает количество единиц товаров в корзине
     * @return int
     */
    public function getBasketTotal();

    /**
     * Возвращает сумму товаров в корзине
     * @return float
     */
    public function getBasketCost();

}
<?php

namespace andreykluev\shopbasket;

use Yii;
use yii\base\Component;

use andreykluev\shopbasket\models\DbBasket;
use andreykluev\shopbasket\models\SessionBasket;

/**
 * Class BasketComponent
 * @package andreykluev\shopbasket
 */
class BasketComponent extends Component
{
    /**
     * Класс пользователя
     */
	public $userClass;

    /**
     * Класс товара
     */
	public $productClass;

    /**
     * Имя, под которым хранится корзина.
     * Должно быть уникально, если в системе используется несколько компонентов.
     * Если определен только 1 компонент, то имя можно не указывать в конфиге.
     */
    public $storageName = 'basket';

    /**
     * Объект самой корзины
     */
    public $basket;

    /**
     * Способ слияния корзин
     */
    public $mergeMethod = 'max';

    /**
     * Кеш товаров корзины
     */
    public $cache = [];

    /**
     * Инициализируем корзину
     */
    public function init()
    {
        // Если гость ...
        if (Yii::$app->getUser()->isGuest) {
            // ... то корзину храним в сессии
            $this->basket = new SessionBasket();
        } else {
            // ... иначе в БД
            $this->basket = new DbBasket();
            $this->basket->idUser = Yii::$app->user->identity->getId();
        }

        $this->basket->owner  = $this;

        // Если в сессии осталась корзина, а пользователь уже не гость => сливаем в БД
        if(!is_null(Yii::$app->session->get($this->storageName, null)) && !Yii::$app->getUser()->isGuest) {
            $this->basket->merge();
        }

        // Кешируем товары
        // Это нужно, чтобы для проверки в корзине товар или нет, не делать запросы в БД для каждого товара
        $this->cache = $this->basket->getBasketProducts();
    }
}

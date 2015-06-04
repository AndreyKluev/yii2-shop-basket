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
    }

    // **********************************************************************************************

	public $onLogin;

	/**
	 * Сливает корзины из сессии и из БД
	 * `sum` - корзина в сессии и корзина в БД (если такая была) будут объеденины, а кол-во одинаковых товаров просуммируются
	 */
	public function mergeBasket_sum()
	{
//		foreach($this->basketProducts as $id => $bp) {
//			$product = call_user_func([$this->productClass, 'findOne'], [$id]);
//
//			if ($this->isProductInBasket($product->id)) {
//				$oldParams = Yii::$app->user->identity->getProductInBasket($product->id);
//
//				var_dump($oldParams);
//				die();
//				Yii::$app->user->identity->unlink('basketProducts', $product, true);
//				$bp['count'] = $bp['count'] + $oldParams->count;
//				$bp['inserted_at'] = min($bp['inserted_at'], $oldParams->inserted_at);
//			}
//
//			Yii::$app->user->identity->link('basketProducts', $product, [
//				'count' => $bp['count'],
//				'price' => $bp['price'],
//				'inserted_at' => $bp['inserted_at']
//			]);
//		}
	}

	/**
	 * Сливает корзины из сессии и из БД
	 * `new` - корзина в БД будет полностью заменена новой
	 */
	public function mergeBasket_new()
	{
	}

	/**
	 * Сливает корзины из сессии и из БД
	 * `merge` - в БД будут добавлены только новые товары
	 */
	public function mergeBasket_merge()
	{
	}

	/**
	 * Сливает корзины из сессии и из БД
	 * `max` - в БД будут добавлены новые товары, а у совпадающих сохраниться наибольшее кол-во
	 */
	public function mergeBasket_max()
	{
	}
}

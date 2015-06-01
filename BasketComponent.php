<?php

namespace andreykluev\shopbasket;

use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;

use andreykluev\shopbasket\models\UserBasket;

/**
 * Class BasketComponent
 * @package andreykluev\shopbasket
 */
class BasketComponent extends Component
{
	public $userClass;
	public $productClass;
	public $isGuest;

	public $basketProducts;

	public $onLogin;

    public $storage;

	/**
	 *
	 */
	public function init()
	{
		$this->isGuest = Yii::$app->getUser()->isGuest;

		// Если не гость
		if (!$this->isGuest) {
			// Если в сессие есть товары
			if(0<$this->getBasketSessionCount()) {
				// Сливаем корзины из сессии и из БД в соответствии с выбранным методом
				switch($this->onLogin) {
					// корзина в сессии и корзина в БД (если такая была) будут объеденины, а кол-во одинаковых товаров просуммируются
					case 'sum':
						$this->mergeBasket_sum();
						break;

					// корзина в БД будет полностью заменена новой
					case 'new':
						$this->mergeBasket_new();
						break;

					// в БД будут добавлены новые товары, а у совпадающих сохраниться наибольшее кол-во
					case 'max':
						$this->mergeBasket_max();
						break;

					// в БД будут добавлены только новые товары
					default:
						$this->mergeBasket_merge();
				}

//				Yii::$app->session->set($this->storage, []);
			}
		}
	}

	/**
	 * Добавляет товар в корзину
	 * @param $id_product
	 * @param $count
	 * @return array
	 * @throws HttpException
	 */
	public function insertProduct($hash, $pid, $price, $params, $count=1)
	{
		// Если гость
		if($this->isGuest) {
			// Сохраняем в сессию
			$this->loadFromSession();

			if(!$this->isProductInBasket($hash)) {
				$this->basketProducts[$hash] = [
					'count' => $count,
					'id_product' => $pid,
					'price' => $price,
                    'params' => $params,
					'inserted_at' => time()
				];
			} else {
                // Если кол-во == 0, то удаляем из корзины
                if(0<$count) {
                    $this->basketProducts[$hash]['count'] = $count;
                } else {
                    unset($this->basketProducts[$hash]);
                }
            }

            Yii::$app->session->set($this->storage, $this->basketProducts);
		} else {
			// Или в БД, если пользователь авторизован
			// Если этот товар еще не в корзине
			if(!$this->isProductInBasket($hash)) {
                $basketProduct = new UserBasket();
                $basketProduct->id_user = Yii::$app->user->identity->getId();
                $basketProduct->group = $this->storage;
                $basketProduct->id_product = $pid;
                $basketProduct->hash_product = $hash;
                $basketProduct->count = $count;
                $basketProduct->price = $price;
                $basketProduct->params = $params;
                $basketProduct->save();
			} else {
                $basketProduct = UserBasket::findOne(['hash_product' => $hash]);

                // Если кол-во == 0, то удаляем из корзины
                if(0<$count) {
                    $basketProduct->count = $count;
                    $basketProduct->save();
                } else {
                    $basketProduct->delete();
                }
            }
		}

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
     *
     */
    public function getProducts()
    {
        if ($this->isGuest) {
            $this->loadFromSession();
            $ids = array_keys($this->basketProducts);
        } else {
            $ids = UserBasket::getUserBasketIds($this->storage);
        }

        return UserBasket::getAllProducts($ids);
    }

	/**
	 * Проверяет, присутствует ли товар в корзине пользователя
	 * @param $pid
	 * @return int|string
	 */
	public function isProductInBasket($hash) {
		return ($this->isGuest) ? $this->isProductInBasketSession($hash) : Yii::$app->user->identity->isProductInBasket($hash);
	}

	/**
	 * Возвращает список товаров в корзине
	 * @return array
	 */
	public function getBasketProducts()
	{
		return ($this->isGuest) ? $this->getFromSession() : Yii::$app->user->identity->getBasketProducts();
	}

	/**
	 * Возвращает количество наименований товара в корзине
	 * @return int
	 */
	public function getBasketCount()
	{
		return ($this->isGuest) ? $this->getBasketSessionCount() : Yii::$app->user->identity->getBasketCount();
	}

	/**
	 * Возвращает количество единиц товаров в корзине
	 * @return int
	 */
	public function getBasketTotal()
	{
		return ($this->isGuest) ? $this->getBasketSessionTotal() : Yii::$app->user->identity->getBasketTotal();
	}

	/**
	 * Возвращает сумму товаров в корзине
	 * @return float
	 */
	public function getBasketCost()
	{
		return ($this->isGuest) ? $this->getBasketSessionCost() : Yii::$app->user->identity->getBasketCost();
	}

	/**
	 * Загружает товары из сессии
	 */
	public function loadFromSession()
	{
		$this->basketProducts = Yii::$app->session->get($this->storage, []);
	}

	/**
	 * Возвращает список товаров в корзине, сохраненной в сессии
	 * @return mixed
	 */
	public function getFromSession()
	{
		$this->loadFromSession();
		return $this->basketProducts;
	}

	/**
	 * Проверяет, присутствует ли товар в корзине в сессии
	 * @param $pid
	 * @return int|string
	 */
	public function isProductInBasketSession($pid) {
		$this->loadFromSession();
		return isset($this->basketProducts[$pid]);
	}

	/**
	 * Возвращает количество наименований товара в корзине, сохраненной в сессии
	 * @return int
	 */
	public function getBasketSessionCount()
	{
		$this->loadFromSession();
		return count($this->basketProducts);
	}

	/**
	 * Возвращает количество единиц товаров в корзине, сохраненной в сессии
	 * @return int
	 */
	public function getBasketSessionTotal()
	{
		$this->loadFromSession();

		$total = 0;
		foreach($this->basketProducts as $bp) {
			$total += $bp['count'];
		}

		return $total;
	}

	/**
	 * Возвращает сумму товаров в корзине, сохраненной в сессии
	 * @return float
	 */
	public function getBasketSessionCost()
	{
		$this->loadFromSession();

		$cost = 0;
		foreach($this->basketProducts as $bp) {
			$cost += $bp['count'] * $bp['price'];
		}

		return $cost;
	}

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

# ShopBasket - actions & behaviors extension for Yii #

## Установка ##

В composer.json:

```
"require": {
	...
	"andreykluev/yii2-shop-basket":"dev-master"
},
```

## Использование ##

Для изменения корзины, нужно гетом передать `id` (id AR-модели) и `count` (количество).
Если `id == 0` - добавиться новый товар,
если `count == 0` - товар удалиться из корзины,
в противном случае измениться количество.


``` php
use andreykluev\shopbasket\BasketAction;

class BasketController extends Controller
{

	...

	public function actions()
	{
		return array(
			'update' => [
				'class' => BasketAction::className(),
				'onBeforeAction' =>  [$this, 'beforeUpdate'],
				'onAfterAction' =>  [$this, 'afterUpdate'],
			],

			...
		);
	}

	public function beforeUpdate()
	{
		// Ваш код
	}
	
	public function afterUpdate($isUpdate = false)
	{
		// Ваш код
	}
```

### Подключаем компонент ###

``` php
use andreykluev\shopbasket\behaviors\BasketUserBehavior;

class User extends ActiveRecord implements IdentityInterface
{
	...
	public function behaviors()
	{
		return [
			BasketUserBehavior::className(),
		];
	}
	...
```

``` php
use andreykluev\shopbasket\behaviors\BasketProductBehavior;

class Product extends ActiveRecord
{
	...
	public function behaviors()
	{
		return [
			BasketUserBehavior::className(),
		];
	}
	...
```

``` php
	'components' => [

		...

		'basket' => [
			'class' => 'andreykluev\shopbasket\BasketComponent',
			'userClass' => 'common\models\User',
			'productClass' => 'common\models\Product',
			'onLogin' => 'merge'
		]
```

В любом месте приложение можно обратиться к корзине следующим образом:

``` php
	Yii::$app->basket->getBasketProducts();
	Yii::$app->basket->getBasketCost();
	Yii::$app->basket->getBasketCount();
	Yii::$app->basket->getBasketTotal();
```

## Методы ##

`getBasketProducts()` - Возвращает список товаров в корзине

`getBasketCost()` - Возвращает сумму товаров в корзине

`getBasketCount()` - Возвращает количество наименований товара в корзине

`getBasketTotal()` - Возвращает количество единиц товаров в корзине

## Варианты `onLogin` при авторизации пользователя ##

`sum` - корзина в сессии и корзина в БД (если такая была) будут объеденины, а кол-во одинаковых товаров просуммируются

`new` - корзина в БД будет полностью заменена новой

`merge` - в БД будут добавлены только новые товары

`max` - в БД будут добавлены новые товары, а у совпадающих сохраниться наибольшее кол-во
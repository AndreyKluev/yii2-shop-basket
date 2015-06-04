<?php

namespace andreykluev\shopbasket\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\web\HttpException;

use andreykluev\shopbasket\BasketInterface;

/**
 * Class UserProduct
 * @package andreykluev\shopbasket\models
 *
 * @property string  $storage       id хранилища
 * @property integer $id_user       id пользователя
 * @property integer $id_product    id товара
 * @property string  $hash_product  Уникальный Хэш товара
 * @property float   $price         Цена товара
 * @property integer $count         Количество
 * @property array   $params        Дополнительные параметры
 */
class DbBasket extends ActiveRecord implements BasketInterface
{
    /**
     * id пользователя, с чьей корзиной работаем
     */
    public $idUser;

    /**
     * Компонент от которого обращаемся
     */
    public $owner;

    /**
     * Определяет название таблицы в БД
     */
    public static function tableName()
    {
        return '{{%user_basket}}';
    }

    /**
     * Проверяет, присутствует ли товар в корзине пользователя
     * @param $hash - уникальный Хэш товара
     * @return boolean
     */
    public function isProductInBasket($hash)
    {
        return (bool)$this->find()
            ->where([
                'id_user' => $this->idUser,
                'hash_product' => $hash
            ])
            ->count();
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
    public function insertProduct($hash, $pid, $price, $params, $count=1)
    {
        // Если этот товар еще не в корзине
        if(!$this->isProductInBasket($hash)) {
            $this->id_user = $this->idUser;
            $this->storage = $this->owner->storageName;
            $this->id_product = $pid;
            $this->hash_product = $hash;
            $this->count = $count;
            $this->price = $price;
            $this->params = Json::encode($params);
            $this->save();
        } else {
            $basketProduct = $this->findOne([
                'id_user' => $this->idUser,
                'hash_product' => $hash,
                'storage' => $this->owner->storageName
            ]);

            // Если модель не найдена, генерим Exception
            if(is_null($basketProduct)) {
                throw new HttpException(404, 'Model not found');
            }

            // Если кол-во == 0, то удаляем из корзины
            if(0<$count) {
                $basketProduct->count = $count;
                $basketProduct->save();
            } else {
                $basketProduct->delete();
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
     * Возвращает список товаров в корзине
     * @return yii\db\ActiveQuery[]
     */
    public function getBasketProducts()
    {
        return $this->find()
            ->where([
                'id_user' => $this->idUser,
                'storage' => $this->owner->storageName
            ]);
    }

    /**
     * Возвращает количество наименований товара в корзине
     * @return int
     */
    public function getBasketCount()
    {
        return $this->find()
            ->where([
                'id_user' => $this->idUser,
                'storage' => $this->owner->storageName
            ])
            ->count();
    }

    /**
     * Возвращает количество единиц товаров в корзине
     * @return int
     */
    public function getBasketTotal()
    {
        $total = $this->find()
            ->where([
                'id_user' => $this->idUser,
                'storage' => $this->owner->storageName
            ])
            ->sum('count');

        return ($total) ? $total : 0;
    }

    /**
     * Возвращает сумму товаров в корзине
     * @return float
     */
    public function getBasketCost()
    {
        $cost = $this->find()
            ->where([
                'id_user' => $this->idUser,
                'storage' => $this->owner->storageName
            ])
            ->sum('price*count');

        return ($cost) ? $cost : 0;
    }
}
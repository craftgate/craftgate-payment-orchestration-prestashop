<?php

namespace classes;

use Db;
use ObjectModel;

class CraftgatePayment extends ObjectModel
{
    public string $id_craftgate_payment;
    public $checkout_token;
    public $payment_id;
    public $meta_data;
    public $id_order;

    public static $definition = [
        'table' => 'craftgate_payments',
        'primary' => 'id_craftgate_payment',
        'fields' => [
            'payment_id' => ['type' => self::TYPE_INT, 'required' => true],
            'checkout_token' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 255],
            'meta_data' => ['type' => self::TYPE_STRING],
            'id_order' => ['type' => self::TYPE_INT, 'required' => true],
        ],
    ];

    public static function getByOrderId(int $orderId)
    {
        $sql = 'SELECT `id_craftgate_payment`
            FROM `' . _DB_PREFIX_ . 'craftgate_payments`
            WHERE `id_order` = ' . $orderId;

        $result = Db::getInstance()->getValue($sql);
        return !empty($result) ? new self($result) : false;
    }

    public static function getByCheckoutToken(string $checkoutToken): false|CraftgatePayment
    {
        $sql = 'SELECT `id_craftgate_payment`
        FROM `' . _DB_PREFIX_ . 'craftgate_payments`
        WHERE `checkout_token` = \'' . pSQL($checkoutToken) . '\'';

        $result = Db::getInstance()->getValue($sql);
        return !empty($result) ? new self($result) : false;
    }

    public function getIdOrder()
    {
        return $this->id_order;
    }

    public function setIdOrder($id_order): void
    {
        $this->id_order = $id_order;
    }

    public function getPaymentId()
    {
        return $this->payment_id;
    }

    public function setPaymentId($payment_id): void
    {
        $this->payment_id = $payment_id;
    }

    public function getCheckoutToken()
    {
        return $this->checkout_token;
    }

    public function setCheckoutToken($checkout_token): void
    {
        $this->checkout_token = $checkout_token;
    }

    public function getIdCraftgatePayment(): string
    {
        return $this->id_craftgate_payment;
    }

    public function setIdCraftgatePayment(string $id_craftgate_payment): void
    {
        $this->id_craftgate_payment = $id_craftgate_payment;
    }

    public function getMetaData()
    {
        return json_decode($this->meta_data);
    }

    public function setMetaData($data): void
    {
        $this->meta_data = json_encode($data);
    }
}

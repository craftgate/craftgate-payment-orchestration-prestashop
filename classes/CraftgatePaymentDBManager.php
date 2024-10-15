<?php

namespace classes;
use Db;

class CraftgatePaymentDBManager
{

    public static function install(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'craftgate_payments` (
            `id_craftgate_payment` INT AUTO_INCREMENT PRIMARY KEY,
            `checkout_token` VARCHAR(255) NULL,
            `payment_id` BIGINT NOT NULL,
            `meta_data` MEDIUMTEXT NULL,
            `id_order` INT UNSIGNED NOT NULL,
            FOREIGN KEY (`id_order`) REFERENCES `' . _DB_PREFIX_ . 'orders` (`id_order`) ON DELETE CASCADE
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return Db::getInstance()->execute($sql);
    }
}



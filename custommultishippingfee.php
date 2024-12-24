<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

declare(strict_types=1);

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

class CustomMultiShippingFee extends Module
{
    public function __construct()
    {
        $this->name = 'custommultishippingfee';
        $this->author = 'PrestaShop';
        $this->version = '1.1.0';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Custom Multi Shipping Fee', [], 'Modules.CustomMultiShippingFee.Admin');
        $this->description = $this->trans(
            'Module created for the purpose of showing existing form types within PrestaShop',
            [],
            'Modules.CustomMultiShippingFee.Admin'
        );

        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => '9.99.99'];
        


    }

    public function install()
    {
        return parent::install()
            && $this->createShippingRulesTable()
            && $this->createShippingRuleProductsTable();
    }
    
    public function uninstall()
    {
        return parent::uninstall()
            && $this->dropShippingRuleProductsTable()
            && $this->dropShippingRulesTable();
    }
    
    private function createShippingRulesTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'shipping_rules` (
            `id_shipping_rule` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_country` INT(11) NOT NULL,
            `shipping_start_rate` DECIMAL(10, 2) NOT NULL,
            `shipping_extra_rate` DECIMAL(10, 2) NOT NULL,
            `date_add` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `date_upd` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_shipping_rule`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';
    
        return Db::getInstance()->execute($sql);
    }
    
    private function createShippingRuleProductsTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'shipping_rule_products` (
            `id_shipping_rule_product` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_shipping_rule` INT(11) UNSIGNED NOT NULL,
            `id_product` INT(11) UNSIGNED NOT NULL,
            `date_add` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `date_upd` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_shipping_rule_product`),
            FOREIGN KEY (`id_shipping_rule`) REFERENCES `' . _DB_PREFIX_ . 'shipping_rules`(`id_shipping_rule`) ON DELETE CASCADE,
            FOREIGN KEY (`id_product`) REFERENCES `' . _DB_PREFIX_ . 'product`(`id_product`) ON DELETE CASCADE
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';
    
        return Db::getInstance()->execute($sql);
    }
    
    private function dropShippingRulesTable()
    {
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'shipping_rules`';
        return Db::getInstance()->execute($sql);
    }
    
    private function dropShippingRuleProductsTable()
    {
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'shipping_rule_products`';
        return Db::getInstance()->execute($sql);
    }

    public function getTabs()
    {
        return [
            [
                'class_name' => 'AdminCustomMultiShippingFeeMultipleForms',
                'visible' => true,
                'name' => 'Custom Shipping Rules',
                'parent_class_name' => 'CONFIGURE',
            ],
        ];
    }

    public function getContent()
    {
        $route = SymfonyContainer::getInstance()->get('router')->generate('demo_configuration_form');
        Tools::redirectAdmin($route);
    }
}

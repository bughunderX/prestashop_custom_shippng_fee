<?php

declare(strict_types=1);

namespace PrestaShop\Module\DemoSymfonyForm\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DemoConfigurationMultipleFormsController extends FrameworkBundleAdminController
{
    public function index(Request $request): Response
    {
        $otherFormDataHandler = $this->get('prestashop.module.demosymfonyform.form.demo_configuration_other_form_data_handler');
        $otherForm = $otherFormDataHandler->getForm();
        $countries = \Country::getCountries($this->getContext()->language->id);
        $products = \Product::getProducts($this->getContext()->language->id, 0, 0, 'id_product', 'ASC', false, true);

        // Fetch shipping rules and their associated products
        $shippingRules = \Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'shipping_rules');
        foreach ($shippingRules as &$rule) {
            $rule['products'] = \Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'shipping_rule_products WHERE id_shipping_rule = ' . (int)$rule['id_shipping_rule']);
        }

        return $this->render('@Modules/demosymfonyform/views/templates/admin/multipleForms.html.twig', [
            'demoConfigurationOtherForm' => $otherForm->createView(),
            'countries' => $countries,
            'products' => $products,
            'shippingRules' => $shippingRules,
        ]);
    }

    public function createShippingRule(Request $request): RedirectResponse
    {
        if ($request->isMethod('POST')) {
            $idCountry = (int)$request->request->get('id_country');
            $startRate = (float)$request->request->get('shipping_start_rate');
            $extraRate = (float)$request->request->get('shipping_extra_rate');
            $products = $request->request->get('products', []);

            \Db::getInstance()->insert('shipping_rules', [
                'id_country' => $idCountry,
                'shipping_start_rate' => $startRate,
                'shipping_extra_rate' => $extraRate,
            ]);

            $idShippingRule = \Db::getInstance()->Insert_ID();

            foreach ($products as $idProduct) {
                \Db::getInstance()->insert('shipping_rule_products', [
                    'id_shipping_rule' => $idShippingRule,
                    'id_product' => (int)$idProduct,
                ]);
            }
        }

        return $this->redirectToRoute('admin_demo_configuration_multiple_forms');
    }

    public function updateShippingRule($id, Request $request): RedirectResponse
    {
        if ($request->isMethod('POST')) {
            $idCountry = (int)$request->request->get('id_country');
            $startRate = (float)$request->request->get('shipping_start_rate');
            $extraRate = (float)$request->request->get('shipping_extra_rate');
            $products = $request->request->get('products', []);

            \Db::getInstance()->update('shipping_rules', [
                'id_country' => $idCountry,
                'shipping_start_rate' => $startRate,
                'shipping_extra_rate' => $extraRate,
            ], 'id_shipping_rule = ' . (int)$id);

            \Db::getInstance()->delete('shipping_rule_products', 'id_shipping_rule = ' . (int)$id);

            foreach ($products as $idProduct) {
                \Db::getInstance()->insert('shipping_rule_products', [
                    'id_shipping_rule' => $id,
                    'id_product' => (int)$idProduct,
                ]);
            }
        }

        return $this->redirectToRoute('admin_demo_configuration_multiple_forms');
    }

    public function deleteShippingRule($id): RedirectResponse
    {
        \Db::getInstance()->delete('shipping_rules', 'id_shipping_rule = ' . (int)$id);
        \Db::getInstance()->delete('shipping_rule_products', 'id_shipping_rule = ' . (int)$id);

        return $this->redirectToRoute('admin_demo_configuration_multiple_forms');
    }
}
<?php

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminShippingRulesController extends FrameworkBundleAdminController
{
    public function listAction()
    {
        // Fetch shipping rules and their associated products
        $shippingRules = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'shipping_rules');
        foreach ($shippingRules as &$rule) {
            $rule['products'] = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'shipping_rule_products WHERE id_shipping_rule = ' . (int)$rule['id_shipping_rule']);
        }

        return $this->render('@Modules/demosymfonyform/views/templates/admin/shipping_rules.html.twig', [
            'shippingRules' => $shippingRules,
        ]);
    }

    public function createAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $idCountry = (int)$request->request->get('id_country');
            $startRate = (float)$request->request->get('shipping_start_rate');
            $extraRate = (float)$request->request->get('shipping_extra_rate');
            $products = $request->request->get('products', []);

            Db::getInstance()->insert('shipping_rules', [
                'id_country' => $idCountry,
                'shipping_start_rate' => $startRate,
                'shipping_extra_rate' => $extraRate,
            ]);

            $idShippingRule = Db::getInstance()->Insert_ID();

            foreach ($products as $idProduct) {
                Db::getInstance()->insert('shipping_rule_products', [
                    'id_shipping_rule' => $idShippingRule,
                    'id_product' => (int)$idProduct,
                ]);
            }

            return $this->redirectToRoute('admin_shipping_rules_list');
        }

        return $this->render('@Modules/demosymfonyform/views/templates/admin/shipping_rule_form.html.twig');
    }

    public function updateAction($id, Request $request)
    {
        if ($request->isMethod('POST')) {
            $idCountry = (int)$request->request->get('id_country');
            $startRate = (float)$request->request->get('shipping_start_rate');
            $extraRate = (float)$request->request->get('shipping_extra_rate');
            $products = $request->request->get('products', []);

            Db::getInstance()->update('shipping_rules', [
                'id_country' => $idCountry,
                'shipping_start_rate' => $startRate,
                'shipping_extra_rate' => $extraRate,
            ], 'id_shipping_rule = ' . (int)$id);

            Db::getInstance()->delete('shipping_rule_products', 'id_shipping_rule = ' . (int)$id);

            foreach ($products as $idProduct) {
                Db::getInstance()->insert('shipping_rule_products', [
                    'id_shipping_rule' => $id,
                    'id_product' => (int)$idProduct,
                ]);
            }

            return $this->redirectToRoute('admin_shipping_rules_list');
        }

        $shippingRule = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'shipping_rules WHERE id_shipping_rule = ' . (int)$id);
        $shippingRule['products'] = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'shipping_rule_products WHERE id_shipping_rule = ' . (int)$id);

        return $this->render('@Modules/demosymfonyform/views/templates/admin/shipping_rule_form.html.twig', [
            'shippingRule' => $shippingRule,
        ]);
    }

    public function deleteAction($id)
    {
        Db::getInstance()->delete('shipping_rules', 'id_shipping_rule = ' . (int)$id);
        Db::getInstance()->delete('shipping_rule_products', 'id_shipping_rule = ' . (int)$id);

        return $this->redirectToRoute('admin_shipping_rules_list');
    }
}
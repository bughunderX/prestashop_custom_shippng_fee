<?php

declare(strict_types=1);

namespace PrestaShop\Module\DemoSymfonyForm\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class DemoConfigurationMultipleFormsController extends FrameworkBundleAdminController
{
    public function index(Request $request): Response
    {
        $otherFormDataHandler = $this->get('prestashop.module.demosymfonyform.form.demo_configuration_other_form_data_handler');
        $otherForm = $otherFormDataHandler->getForm();
        $countries = \Country::getCountries($this->getContext()->language->id);
        $products = \Product::getProducts($this->getContext()->language->id, 0, 0, 'id_product', 'ASC', false, true);

        // Fetch shipping rules and their associated products
        $shippingRules = \Db::getInstance()->executeS('
            SELECT 
                sr.id_shipping_rule,
                sr.id_country,
                sr.shipping_start_rate,
                sr.shipping_extra_rate,
                GROUP_CONCAT(srp.id_product ORDER BY srp.id_product ASC) AS product_ids
            FROM ' . _DB_PREFIX_ . 'shipping_rules sr
            LEFT JOIN ' . _DB_PREFIX_ . 'shipping_rule_products srp ON srp.id_shipping_rule = sr.id_shipping_rule
            GROUP BY sr.id_shipping_rule
        ');
        foreach ($shippingRules as &$rule) {
            $rule['product_ids'] = !empty($rule['product_ids']) ? array_map('intval', explode(',', $rule['product_ids'])) : [];
        }
        
        $addShippingRuleUrl = $this->generateUrl('create_shipping_rule');
        $updateShippingRuleUrl = $this->generateUrl('update_shipping_rule');
        $deleteShippingRuleUrl = $this->generateUrl('delete_shipping_rule');

        return $this->render('@Modules/demosymfonyform/views/templates/admin/multipleForms.html.twig', [
            'demoConfigurationOtherForm' => $otherForm->createView(),
            'countries' => $countries,
            'products' => $products,
            'shippingRules' => $shippingRules,
            'addShippingRuleUrl' => $addShippingRuleUrl,
            'updateShippingRuleUrl' => $updateShippingRuleUrl,
            'deleteShippingRuleUrl' => $deleteShippingRuleUrl,
        ]);
    }

    public function createShippingRule(Request $request): JsonResponse
    {
        if ($request->isMethod('POST')) {
            $req = json_decode($request->getContent());
            $idCountry = (int)$req -> id_country;
            $startRate = (float)$req -> shipping_start_rate;
            $extraRate = (float)$req -> shipping_extra_rate;
            $products = $req -> products;

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
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Shipping rule created successfully.',
                'id_shipping_rule' => $idShippingRule,
            ]);
        }
    }

    public function updateShippingRule(Request $request): JsonResponse
    {
        if ($request->isMethod('POST')) {
            $req = json_decode($request->getContent());
            $id = (int)$req -> id;
            $idCountry = (int)$req -> id_country;
            $startRate = (float)$req -> shipping_start_rate;
            $extraRate = (float)$req -> shipping_extra_rate;
            $products = $req -> products;

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
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Shipping rule updated successfully.',
                'id_shipping_rule' => $id,
            ]);
        }

        
    }

    public function deleteShippingRule(Request $request): JsonResponse
    {
        if ($request->isMethod('POST')) {
            $req = json_decode($request->getContent());
            $id = (int)$req -> id;
            \Db::getInstance()->delete('shipping_rules', 'id_shipping_rule = ' . (int)$id);
            \Db::getInstance()->delete('shipping_rule_products', 'id_shipping_rule = ' . (int)$id);

            
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Successfully deleted.',
            ], 200);
        }
        return new JsonResponse([
            'status' => 'error',
            'message' => 'Invalid request method.',
        ], 400);
    }
}
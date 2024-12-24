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

namespace PrestaShop\Module\CustomMultiShippingFee\Form;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;

class DemoConfigurationOtherFormDataProvider implements FormDataProviderInterface
{
    /**
     * @var DataConfigurationInterface
     */
    private $demoConfigurationOtherDataConfiguration;

    /**
     * @param DataConfigurationInterface $demoConfigurationOtherDataConfiguration
     */
    public function __construct(DataConfigurationInterface $demoConfigurationOtherDataConfiguration)
    {
        $this->demoConfigurationOtherDataConfiguration = $demoConfigurationOtherDataConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
       $data =  $this->demoConfigurationOtherDataConfiguration->getConfiguration();

       // If table data is not set, create a default data set
        if (!isset($data['table_data']) || empty($data['table_data'])) {
        }
        $data =  ['table_data' => [
            [
              'id' => 1,
                'country' => 21,
                'start_fee' => 10.99,
                'extra_fee' => 100,
                'products' => [1, 2, 3]
            ],
            [
                'id' => 2,
                'country' => 8,
                'start_fee' => 25.00,
                'extra_fee' => 50,
                'products' => [4, 5, 6]
            ]
      ]];
         return $data;

    }

    /**
     * {@inheritdoc}
     */
    public function setData(array $data): array
    {
        return $this->demoConfigurationOtherDataConfiguration->updateConfiguration($data);
    }
}
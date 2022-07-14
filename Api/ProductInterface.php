<?php

/**
 * @author Mygento Team
 * @copyright 2018-2020 Mygento (https://www.mygento.ru)
 * @package Mygento_ImportExport
 */

namespace Mygento\ImportExport\Api;

interface ProductInterface
{
    /**
     * @param array $data
     * @param array $settings
     * @return string
     */
    public function importProductData(array $data, array $settings = []): string;

    /**
     * Get all products SKU
     * @return array
     */
    public function getImportedProductsSku(): array;

    /**
     * Get all products entity_id sku pair
     * @return array
     */
    public function getImportedProductsIdSku(): array;

    /**
     * Mass Disable Products
     */
    public function massDisableProducts();

    /**
     * Invalidate Product Index
     */
    public function invalidateProductIndex();

    /**
     * Change product type
     * @param array $skus
     * @param string $type
     */
    public function changeProductType(array $skus, string $type);
}

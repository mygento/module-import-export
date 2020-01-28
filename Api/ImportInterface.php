<?php

/**
 * @author Mygento Team
 * @copyright 2018-2020 Mygento (https://www.mygento.ru)
 * @package Mygento_ImportExport
 */

namespace Mygento\ImportExport\Api;

interface ImportInterface
{
    /**
     * Import product data
     * @param array $data
     * @return string
     */
    public function importProductData(array $data): string;

    /**
     * Set Option based attribute list
     * @param string[] $list
     */
    public function setProductOptionAttributes(array $list);

    /**
     * @param array $data
     * @return array
     */
    public function importCategoryData(array $data): array;

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
     * @param array $data
     */
    public function deleteCategoryData(array $data);

    /**
     * @param array $data
     */
    public function renameCategoryData(array $data);

    /**
     * @param array $data
     */
    public function disableCategoryData(array $data);

    /**
     * @param array $data
     */
    public function disableProductData(array $data);

    /**
     * Mass Disable Products
     */
    public function massDisableProducts();

    /**
     * @param int $max
     * @return $this
     */
    public function setMaxRetry(int $max);

    /**
     * @param bool $flag
     * @return $this
     */
    public function setManualReindex(bool $flag);

    /**
     * Invalidate Product Index
     */
    public function invalidateProductIndex();

    /**
     * Update attribute values for entity list per store
     *
     * @param array $productIds
     * @param array $attrData ['attirbute_code' => 'value']
     * @param int $storeId
     */
    public function updateProductAttributes(array $productIds, array $attrData, int $storeId);
}

<?php

/**
 * @author Mygento Team
 * @copyright 2018 Mygento (https://www.mygento.ru)
 * @package Mygento_ImportExport
 */

namespace Mygento\ImportExport\Api;

interface ImportInterface
{

    /**
     * Import product data
     * @param array $data
     */
    public function importProductData(array $data): string;

    /**
     * Set Option based attribute list
     * @param string[] $list
     */
    public function setProductOptionAttributes(array $list);

    /**
     *
     * @param array $data
     */
    public function importCategoryData(array $data): array;

    /**
     * Get all products SKU
     */
    public function getImportedProductsSku(): array;

    /**
     *
     * @param array $data
     */
    public function deleteCategoryData(array $data);

    /**
     *
     * @param array $data
     */
    public function renameCategoryData(array $data);

    /**
     *
     * @param array $data
     */
    public function disableCategoryData(array $data);

    /**
     *
     * @param array $data
     */
    public function disableProductData(array $data);

    /**
     * Mass Disable Products
     */
    public function massDisableProducts();

    /**
     *
     * @param int $max
     * @return $this
     */
    public function setMaxRetry(int $max);
}

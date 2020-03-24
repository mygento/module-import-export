<?php

/**
 * @author Mygento Team
 * @copyright 2018-2020 Mygento (https://www.mygento.ru)
 * @package Mygento_ImportExport
 */

namespace Mygento\ImportExport\Api;

interface ImportInterface extends ProductInterface
{
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
     * Update attribute values for entity list per store
     *
     * @param array $productIds
     * @param array $attrData ['attirbute_code' => 'value']
     * @param int $storeId
     */
    public function updateProductAttributes(array $productIds, array $attrData, int $storeId);
}

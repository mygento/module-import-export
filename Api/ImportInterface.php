<?php

/**
 * @author Mygento Team
 * @copyright 2018-2022 Mygento (https://www.mygento.com)
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
     * @param array $importSettings
     * @return $this
     */
    public function setImportSettings(array $importSettings);

    /**
     * Update attribute values for entity list per store
     *
     * @param array $productIds
     * @param array $attrData ['attirbute_code' => 'value']
     * @param int $storeId
     */
    public function updateProductAttributes(array $productIds, array $attrData, int $storeId);

    /**
     * @return string
     */
    public function getLogTrace();

    /**
     * @param array $data
     * @return bool
     */
    public function validateData(array $data);
}

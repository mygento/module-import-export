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
}
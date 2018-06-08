<?php

/**
 * @author Mygento Team
 * @copyright 2018 Mygento (https://www.mygento.ru)
 * @package Mygento_ImportExport
 */

namespace Mygento\ImportExport\Model\Category;

class Import implements \Mygento\ImportExport\Api\CategoryInterface
{
    /** \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor */
    private $categoryProcessor;

    public function __construct(
        \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor $categoryProcessor
    ) {
        $this->categoryProcessor = $categoryProcessor;
    }

    /**
     *
     * @param string $name
     * @return integer|null;
     */
    public function createCategory(string $name)
    {
        $result = $this->categoryProcessor->upsertCategories($name, '|');
        if (is_array($result) && isset($result[0])) {
            return $result[0];
        }
        return null;
    }
}

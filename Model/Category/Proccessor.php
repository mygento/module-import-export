<?php

/**
 * @author Mygento Team
 * @copyright 2018 Mygento (https://www.mygento.ru)
 * @package Mygento_ImportExport
 */

namespace Mygento\ImportExport\Model\Category;

class Processor extends \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor
{
    /** @var \Magento\Catalog\Api\CategoryRepositoryInterface */
    private $categoryRepo;

    public function __construct(
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryColFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        parent::__construct($categoryColFactory, $categoryFactory);
        $this->categoryRepo = $categoryRepository;
    }

    /**
     *
     * @param string $categoryPath
     */
    public function deleteCategoryByPath(string $categoryPath)
    {
        /** @var string $index */
        $index = $this->standardizeString($categoryPath);
        if (!isset($this->categories[$index])) {
            return;
        }

        $categoryId = $this->categories[$index];
        $this->categoryRepo->deleteByIdentifier($categoryId);
    }
}

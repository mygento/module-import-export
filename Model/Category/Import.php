<?php

/**
 * @author Mygento Team
 * @copyright 2018-2020 Mygento (https://www.mygento.ru)
 * @package Mygento_ImportExport
 */

namespace Mygento\ImportExport\Model\Category;

class Import implements \Mygento\ImportExport\Api\CategoryInterface
{
    /** @var \Mygento\ImportExport\Model\Category\Processor */
    private $categoryProcessor;

    public function __construct(
        \Mygento\ImportExport\Model\Category\Processor $categoryProcessor
    ) {
        $this->categoryProcessor = $categoryProcessor;
    }

    /**
     * @param string $path
     * @return integer|null;
     */
    public function createCategory(string $path)
    {
        $result = $this->categoryProcessor->upsertCategories($path, '|');
        if (is_array($result) && isset($result[0])) {
            return $result[0];
        }

        return null;
    }

    /**
     * @param string $path
     */
    public function deleteCategory(string $path)
    {
        $this->categoryProcessor->deleteCategoryByPath($path);
    }

    /**
     * @param string $path
     */
    public function disableCategory(string $path)
    {
        $this->categoryProcessor->disableCategoryByPath($path);
    }

    /**
     * @param int $id
     * @param string $name
     */
    public function renameCategory(int $id, string $name)
    {
        $this->categoryProcessor->renameCategory($id, $name);
    }
}

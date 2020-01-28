<?php

/**
 * @author Mygento Team
 * @copyright 2018-2020 Mygento (https://www.mygento.ru)
 * @package Mygento_ImportExport
 */

namespace Mygento\ImportExport\Api;

interface CategoryInterface
{
    /**
     * @param string $path
     */
    public function createCategory(string $path);

    /**
     * @param string $path
     */
    public function deleteCategory(string $path);

    /**
     * @param string $path
     */
    public function disableCategory(string $path);

    /**
     * @param int $id
     * @param string $name
     */
    public function renameCategory(int $id, string $name);
}

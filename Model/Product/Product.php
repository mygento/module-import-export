<?php

/**
 * @author Mygento Team
 * @copyright 2018 Mygento (https://www.mygento.ru)
 * @package Mygento_ImportExport
 */

namespace Mygento\ImportExport\Model\Product;

class Product
{
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Get SKU through product identifiers
     * @return array
     */
    public function getProductsSku(): array
    {
        $connection = $this->resource->getConnection(
            \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION
        );
        $select = $this->resource->getConnection()->select()->from(
            $this->resource->getTableName(
                'catalog_product_entity',
                \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION
            ),
            ['sku']
        );
        return $connection->fetchCol($select);
    }
}

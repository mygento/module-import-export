<?php

/**
 * @author Mygento Team
 * @copyright 2018 Mygento (https://www.mygento.ru)
 * @package Mygento_ImportExport
 */

namespace Mygento\ImportExport\Model\Product;

use Magento\Catalog\Model\Product\Attribute\Source\Status;

class Product
{
    /** @var \Magento\Framework\App\ResourceConnection */
    private $resource;

    /** @var \Magento\Catalog\Api\ProductRepositoryInterface */
    private $productRepo;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepo,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->productRepo = $productRepo;
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

    public function disableProduct(string $sku)
    {
        try {
            $product = $this->productRepo->get($sku);
            $product->setStatus(Status::STATUS_DISABLED);
            $this->productRepo->save($product);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return;
        }
    }
}

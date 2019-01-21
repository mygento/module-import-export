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

    /** @var \Magento\Eav\Model\Config $eavConfig */
    private $eavConfig;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepo,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->productRepo = $productRepo;
        $this->eavConfig = $eavConfig;
        $this->resource = $resource;
    }

    /**
     * Get SKU through product identifiers
     * @return array
     */
    public function getProductsSku(): array
    {
        $connection = $this->resource->getConnection();
        $select = $this->resource->getConnection()->select()->from(
            $this->resource->getTableName('catalog_product_entity'),
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

    public function massDisableProducts()
    {
        $attribute = $this->eavConfig->getAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'status'
        );
        if (!$attribute || !$attribute->getId()) {
            return;
        }
        $connection = $this->resource->getConnection();

        $bind = ['value' => Status::STATUS_DISABLED];
        $where = 'attribute_id = ' . $attribute->getId();

        $connection->update(
            $attribute->getBackendTable(),
            $bind,
            $where
        );
    }
}

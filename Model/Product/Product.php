<?php

/**
 * @author Mygento Team
 * @copyright 2018-2020 Mygento (https://www.mygento.ru)
 * @package Mygento_ImportExport
 */

namespace Mygento\ImportExport\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

class Product
{
    /** @var \Magento\Framework\App\ResourceConnection */
    private $resource;

    /** @var \Magento\Catalog\Api\ProductRepositoryInterface */
    private $productRepo;

    /** @var \Magento\Eav\Model\Config */
    private $eavConfig;

    /** @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor */
    private $productFlatIndexerProcessor;

    /** @var \Magento\Catalog\Model\Indexer\Product\Price\Processor */
    private $productPriceIndexerProcessor;

    /** @var \Magento\Catalog\Model\Product\ActionFactory */
    private $actionFactory;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepo
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Catalog\Model\Product\ActionFactory $actionFactory
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepo,
        \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Model\Product\ActionFactory $actionFactory
    ) {
        $this->productRepo = $productRepo;
        $this->productFlatIndexerProcessor = $productFlatIndexerProcessor;
        $this->productPriceIndexerProcessor = $productPriceIndexerProcessor;
        $this->eavConfig = $eavConfig;
        $this->resource = $resource;
        $this->actionFactory = $actionFactory;
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

    /**
     * Get SKU through product identifiers
     * @return array
     */
    public function getProductsIdSkuPair(): array
    {
        $connection = $this->resource->getConnection();
        $select = $this->resource->getConnection()->select()->from(
            $this->resource->getTableName('catalog_product_entity'),
            ['entity_id', 'sku']
        );

        return $connection->fetchPairs($select, ['entity_id', 'sku']);
    }

    /**
     * @param string $sku
     */
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

    /**
     * Mass disable products
     */
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

    /**
     * Update attribute values for entity list per store
     *
     * @param array $productIds
     * @param array $attrData ['attirbute_code' => 'value']
     * @param int $storeId
     */
    public function updateProductAttributes(array $productIds, array $attrData, int $storeId)
    {
        $action = $this->actionFactory->create();
        $action->updateAttributes($productIds, $attrData, $storeId);
        $this->productFlatIndexerProcessor->reindexList($productIds);
        if (
            isset($attrData['price']) ||
            isset($attrData['special_price']) ||
            isset($attrData['special_from_date']) ||
            isset($attrData['special_to_date']) ||
            isset($attrData['cost'])
        ) {
            $this->productPriceIndexerProcessor->reindexList($productIds);
        }
    }

    /**
     * Change product type
     * @param string[] $skus
     * @param string $type
     */
    public function changeProductType(array $skus, string $type)
    {
        if (!$type || empty($skus)) {
            return;
        }
        $connection = $this->resource->getConnection();

        $bind = [ProductInterface::TYPE_ID => $type];
        $where = $connection->quoteInto('sku IN(?)', $skus);
        $connection->update(
            $this->resource->getTableName('catalog_product_entity'),
            $bind,
            $where
        );
    }
}

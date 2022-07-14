<?php

/**
 * @author Mygento Team
 * @copyright 2018-2020 Mygento (https://www.mygento.ru)
 * @package Mygento_ImportExport
 */

namespace Mygento\ImportExport\Model;

class Import implements \Mygento\ImportExport\Api\ImportInterface
{
    /** @var \Mygento\ImportExport\Model\Category\Import */
    private $categoryAdapter;

    /** @var \Mygento\ImportExport\Model\Product\Attribute */
    private $attributeAdapter;

    /** @var \Mygento\ImportExport\Model\Product\Product */
    private $productAdapter;

    /** @var \Magento\ImportExport\Model\ImportFactory */
    private $importModelFactory;

    /** @var \Mygento\ImportExport\Model\Adapter\ArrayAdapterFactory */
    private $adapterFactory;

    /** @var array */
    private $importSettings = [];

    /** @var array */
    private $defaultProductSettings = [
        'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
        'entity' => 'catalog_product',
        'validation_strategy' => 'validation-stop-on-errors',
        '_import_multiple_value_separator' => ',',
        \Magento\ImportExport\Model\Import::FIELD_NAME_IMG_FILE_DIR => 'var/import',
    ];

    /** @var array */
    private $defaultCustomerSettings = [
        'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
        'entity' => 'customer',
        'validation_strategy' => 'validation-stop-on-errors',
        '_import_multiple_value_separator' => ',',
    ];

    /** @var array */
    private $defaultCustomerAddressSettings = [
        'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE,
        'entity' => 'customer_address',
        'validation_strategy' => 'validation-stop-on-errors',
        '_import_multiple_value_separator' => ',',
    ];

    /** @var array */
    private $optionAttributes = [];

    /** @var string */
    private $logTrace = '';

    /** @var int */
    private $maxRetry = 5;

    /** @var bool */
    private $manualReindex = false;

    /**
     * @param \Mygento\ImportExport\Model\Category\Import $categoryAdapter
     * @param \Mygento\ImportExport\Model\Product\Attribute $attributeAdapter
     * @param \Mygento\ImportExport\Model\Product\Product $productAdapter
     * @param \Magento\ImportExport\Model\ImportFactory $importModelFactory
     * @param \Mygento\ImportExport\Model\Adapter\ArrayAdapterFactory $adapterFactory
     */
    public function __construct(
        \Mygento\ImportExport\Model\Category\Import $categoryAdapter,
        \Mygento\ImportExport\Model\Product\Attribute $attributeAdapter,
        \Mygento\ImportExport\Model\Product\Product $productAdapter,
        \Magento\ImportExport\Model\ImportFactory $importModelFactory,
        \Mygento\ImportExport\Model\Adapter\ArrayAdapterFactory $adapterFactory
    ) {
        $this->categoryAdapter = $categoryAdapter;
        $this->attributeAdapter = $attributeAdapter;
        $this->productAdapter = $productAdapter;
        $this->importModelFactory = $importModelFactory;
        $this->adapterFactory = $adapterFactory;
    }

    /**
     * @param array $data
     * @param array $settings
     * @return string
     */
    public function importProductData(array $data, array $settings = []): string
    {
        $this->importSettings = $this->defaultProductSettings;
        if (!empty($settings)) {
            $this->importSettings = array_merge($this->defaultProductSettings, $settings);
        }

        $source = $this->adapterFactory->create(['data' => $data]);
        $this->createAttrOptions($source);

        if (!$this->validateData($data)) {
            throw new \Magento\Framework\Exception\ValidatorException(__($this->logTrace));
        }

        for ($i = 0; $i < $this->maxRetry; $i++) {
            try {
                $this->importData();
                break;
            } catch (\Magento\Framework\DB\Adapter\DeadlockException $e) {
                unset($e);
                continue;
            } catch (\Magento\Framework\DB\Adapter\LockWaitException $e) {
                unset($e);
                continue;
            }
        }

        return $this->logTrace;
    }

    /**
     * @param array $data
     * @param array $settings
     * @return string
     */
    public function importCustomersData(array $data, $settings = []): string
    {
        $this->importSettings = $this->defaultCustomerSettings;
        if (!empty($settings)) {
            $this->importSettings = array_merge($this->defaultCustomerSettings, $settings);
        }

        if ($this->validateData($data)) {
            $this->importData();
        }

        return $this->logTrace;
    }

    /**
     * @param array $data
     * @param array $settings
     * @return string
     */
    public function importAddressesData(array $data, $settings = []): string
    {
        $this->importSettings = $this->defaultCustomerAddressSettings;
        if (!empty($settings)) {
            $this->importSettings = array_merge($this->defaultCustomerSettings, $settings);
        }

        if ($this->validateData($data)) {
            $this->importData();
        }

        return $this->logTrace;
    }

    /**
     * @param array $data
     */
    public function disableProductData(array $data)
    {
        foreach ($data as $product) {
            $this->productAdapter->disableProduct($product);
        }
    }

    /**
     * Set Option based attribute list
     * @param string[] $list
     */
    public function setProductOptionAttributes(array $list)
    {
        $this->optionAttributes = $list;
    }

    /**
     * @return \Magento\ImportExport\Model\Import
     */
    public function createImportModel()
    {
        $importModel = $this->importModelFactory->create();
        $importModel->setData($this->importSettings);

        return $importModel;
    }

    /**
     * @param array $data
     */
    public function importCategoryData(array $data): array
    {
        $result = [];
        foreach ($data as $cat) {
            $result[] = $this->categoryAdapter->createCategory($cat);
        }

        return $result;
    }

    /**
     * @param array $data
     */
    public function deleteCategoryData(array $data)
    {
        foreach ($data as $cat) {
            $this->categoryAdapter->deleteCategory($cat);
        }
    }

    /**
     * @param array $data
     */
    public function disableCategoryData(array $data)
    {
        foreach ($data as $cat) {
            $this->categoryAdapter->disableCategory($cat);
        }
    }

    /**
     * Array of [id] => name
     * @param array $data
     */
    public function renameCategoryData(array $data)
    {
        foreach ($data as $id => $name) {
            try {
                $this->categoryAdapter->renameCategory($id, $name);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                unset($e);
            }
        }
    }

    /**
     * @return array
     */
    public function getImportedProductsSku(): array
    {
        return $this->productAdapter->getProductsSku();
    }

    /**
     * Get all products entity_id sku pair
     * @return array
     */
    public function getImportedProductsIdSku(): array
    {
        return $this->productAdapter->getProductsIdSkuPair();
    }

    /**
     * Mass Disable Products
     */
    public function massDisableProducts()
    {
        return $this->productAdapter->massDisableProducts();
    }

    /**
     * @param int $max
     * @return $this
     */
    public function setMaxRetry(int $max)
    {
        $this->maxRetry = $max;

        return $this;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setManualReindex(bool $flag)
    {
        $this->manualReindex = $flag;

        return $this;
    }

    public function setImportSettings(array $importSettings): Import
    {
        $this->importSettings = $importSettings;

        return $this;
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
        return $this->productAdapter->updateProductAttributes($productIds, $attrData, $storeId);
    }

    /**
     * Invalidate Product Index
     */
    public function invalidateProductIndex()
    {
        $this->importSettings = $this->defaultProductSettings;
        $importModel = $this->createImportModel();
        $importModel->invalidateIndex();
    }

    /**
     * @param string[] $skus
     * @param string $type
     */
    public function changeProductType(array $skus, string $type)
    {
        $this->productAdapter->changeProductType($skus, $type);
        $this->invalidateProductIndex();
    }

    /**
     * @param array $data
     * @return bool
     */
    public function validateData(array $data): bool
    {
        if (empty($data)) {
            $this->logTrace = __('Empty import data');

            return false;
        }

        $importModel = $this->createImportModel();
        $source = $this->adapterFactory->create(['data' => $data]);
        $validationResult = $importModel->validateSource($source);
        $this->addToLogTrace($importModel);

        return $validationResult;
    }

    public function getLogTrace(): string
    {
        return $this->logTrace;
    }

    /**
     * @param \Magento\ImportExport\Model\Import $importModel
     */
    protected function handleImportResult($importModel)
    {
        if ($this->manualReindex) {
            return;
        }
        if (!$importModel->getErrorAggregator()->hasToBeTerminated()) {
            $importModel->invalidateIndex();
        }
    }

    /**
     * @param \Magento\ImportExport\Model\Import\AbstractSource $source
     */
    private function createAttrOptions(\Magento\ImportExport\Model\Import\AbstractSource $source)
    {
        $this->attributeAdapter->createAttributeOptions($source, $this->optionAttributes);
    }

    /**
     * Import Data
     */
    private function importData()
    {
        $importModel = $this->createImportModel();
        $importModel->importSource();
        $this->addToLogTrace($importModel);
        $this->handleImportResult($importModel);
    }

    /**
     * @param \Magento\ImportExport\Model\Import $importModel
     */
    private function addToLogTrace($importModel)
    {
        $this->logTrace = $importModel->getFormatedLogTrace();
    }
}

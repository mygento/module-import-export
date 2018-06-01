<?php

/**
 * @author Mygento Team
 * @copyright 2018 Mygento (https://www.mygento.ru)
 * @package Mygento_ImportExport
 */

namespace Mygento\ImportExport\Model;

class Import implements \Mygento\ImportExport\Api\ImportInterface
{
    /** @var \Mygento\ImportExport\Model\Product\Attribute */
    private $attributeAdapter;

    /** @var \Magento\ImportExport\Model\ImportFactory */
    private $importModelFactory;

    /** @var \Mygento\Cml\Model\Adapter\ArrayAdapterFactory */
    private $adapterFactory;

    /** @var array */
    private $importSettings = [];

    /** @var array */
    private $defaultProductSettings = [
        'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
        'entity' => 'catalog_product',
        'validation_strategy' => 'validation-stop-on-errors',
        '_import_multiple_value_separator' => ',',
    ];

    /** @var array */
    private $optionAttributes = [];

    /** @var string */
    private $logTrace = '';

    /**
     *
     * @param \Mygento\ImportExport\Model\Product\Attribute $attributeAdapter
     * @param \Magento\ImportExport\Model\ImportFactory $importModelFactory
     * @param \Mygento\ImportExport\Model\Adapter\ArrayAdapterFactory $adapterFactory
     */
    public function __construct(
        \Mygento\ImportExport\Model\Product\Attribute $attributeAdapter,
        \Magento\ImportExport\Model\ImportFactory $importModelFactory,
        \Mygento\ImportExport\Model\Adapter\ArrayAdapterFactory $adapterFactory
    ) {
        $this->attributeAdapter = $attributeAdapter;
        $this->importModelFactory = $importModelFactory;
        $this->adapterFactory = $adapterFactory;
    }

    /**
     *
     * @param array $data
     * @param mixed $settings
     * @return string
     */
    public function importProductData(array $data, $settings = []): string
    {
        $this->importSettings = $this->defaultProductSettings;
        if (!empty($settings)) {
            $this->importSettings = array_merge($this->defaultProductSettings, $settings);
        }

        if ($this->validateData($data)) {
            $this->importData();
        }
        return $this->logTrace;
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
     * @return bool
     */
    private function validateData(array $data): bool
    {
        if (empty($data)) {
            $this->logTrace = __('Empty import data');
            return false;
        }

        $importModel = $this->createImportModel();
        $source = $this->adapterFactory->create(['data' => $data]);
        $this->createAttrOptions($source);
        $validationResult = $importModel->validateSource($source);
        $this->addToLogTrace($importModel);
        return $validationResult;
    }

    /**
     *
     * @param \Magento\ImportExport\Model\Import\AbstractSource $source
     */
    private function createAttrOptions(\Magento\ImportExport\Model\Import\AbstractSource $source)
    {
        $this->attributeAdapter->createAttributeOptions($source, $this->optionAttributes);
    }

    /**
     * @param \Magento\ImportExport\Model\Import $importModel
     */
    protected function handleImportResult($importModel)
    {
        if (!$importModel->getErrorAggregator()->hasToBeTerminated()) {
            $importModel->invalidateIndex();
        }
    }

    private function importData()
    {
        $importModel = $this->createImportModel();
        $importModel->importSource();
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

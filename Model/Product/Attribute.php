<?php

/**
 * @author Mygento Team
 * @copyright 2018-2020 Mygento (https://www.mygento.ru)
 * @package Mygento_ImportExport
 */

namespace Mygento\ImportExport\Model\Product;

class Attribute implements \Mygento\ImportExport\Api\AttributeInterface
{
    /** @var array */
    private $options = [];

    /**
     * @var \Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory
     */
    private $optionLabelFactory;

    /**
     * @var \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory
     */
    private $optionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\OptionManagement
     */
    private $repository;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @param \Magento\Catalog\Model\Product\Attribute\OptionManagement $repository
     * @param \Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory $optionLabelFactory
     * @param \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Attribute\OptionManagement $repository,
        \Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory $optionLabelFactory,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository
    ) {
        $this->repository = $repository;
        $this->optionLabelFactory = $optionLabelFactory;
        $this->optionFactory = $optionFactory;
        $this->productAttributeRepository = $productAttributeRepository;
    }

    /**
     * @param \Magento\ImportExport\Model\Import\AbstractSource $source
     * @param array $attibutes
     */
    public function createAttributeOptions(
        \Magento\ImportExport\Model\Import\AbstractSource $source,
        array $attibutes
    ) {
        $this->createDropdownAttributeOptions($source, $attibutes);
        $this->createMultiselectAttributeOptions($source, $attibutes);
    }

    /**
     * @param \Magento\ImportExport\Model\Import\AbstractSource $source
     * @param array $attibutes
     */
    public function createDropdownAttributeOptions(
        \Magento\ImportExport\Model\Import\AbstractSource $source,
        array $attibutes
    ) {
        $source->rewind();
        while ($source->valid()) {
            $row = $source->current();
            foreach ($attibutes as $attributeCode) {
                if (!isset($row[$attributeCode]) || !strlen(trim($row[$attributeCode]))) {
                    continue;
                }

                $options = $this->getAttributeOptions($attributeCode);

                if (in_array($row[$attributeCode], $options)) {
                    continue;
                }
                $this->createAttributeOption($attributeCode, $row[$attributeCode]);
            }

            $source->next();
        }
    }

    /**
     * @param \Magento\ImportExport\Model\Import\AbstractSource $source
     * @param array $attibutes
     */
    public function createMultiselectAttributeOptions(
        \Magento\ImportExport\Model\Import\AbstractSource $source,
        array $attibutes
    ) {
        $source->rewind();

        while ($source->valid()) {
            $row = $source->current();

            foreach ($attibutes as $attributeCode) {
                $attribute = $this->productAttributeRepository->get($attributeCode);

                if ($attribute->getFrontendInput() == 'multiselect') {
                    $options = $this->getAttributeOptions($attributeCode);
                    $newOptions = explode(\Magento\ImportExport\Model\Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $row[$attributeCode]);

                    foreach ($newOptions as $newOption) {
                        if (!isset($newOption) || !strlen(trim($newOption))) {
                            continue;
                        }
                        if (in_array($newOption, $options)) {
                            continue;
                        }
                        $this->createAttributeOption($attributeCode, $row[$attributeCode]);
                    }

                }
            }

            $source->next();
        }

        unset($source);
        unset($attibutes);
    }

    /**
     * Retrieve list of attribute options
     *
     * @param string $code
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\InputException
     * @return array
     */
    public function getAttributeOptions(string $code)
    {
        if (!isset($this->options[$code])) {
            $options = $this->repository->getItems($code);
            $result = [];
            foreach ($options as $option) {
                if (!$option->getValue()) {
                    continue;
                }

                $result[$option->getValue()] = $option->getLabel();
            }
            $this->options[$code] = $result;
        }

        return $this->options[$code];
    }

    /**
     * @param string $code
     * @param string $label
     */
    public function createAttributeOption(string $code, $label)
    {
        $optionLabel = $this->optionLabelFactory->create();
        $optionLabel->setStoreId(0);
        $optionLabel->setLabel($label);

        $option = $this->optionFactory->create();
        $option->setLabel($label);
        $option->setStoreLabels([$optionLabel]);
        $option->setSortOrder(0);
        $option->setIsDefault(false);

        $this->repository->add($code, $option);
        $this->options[$code][] = $label;
    }

    /**
     * Reload Cached Attribute Option List
     * @param string $code
     * @return array
     */
    public function reloadAttributeOptionList(string $code)
    {
        unset($this->options[$code]);

        return $this->getAttributeOptions($code);
    }
}

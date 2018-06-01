<?php

/**
 * @author Mygento Team
 * @copyright 2018 Mygento (https://www.mygento.ru)
 * @package Mygento_ImportExport
 */

namespace Mygento\ImportExport\Model\Product;

class Attribute implements \Mygento\ImportExport\Api\AttributeInterface
{

    /** var array */
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
     *
     * @var \Magento\Catalog\Model\Product\Attribute\OptionManagement
     */
    private $repository;

    public function __construct(
        \Magento\Catalog\Model\Product\Attribute\OptionManagement $repository,
        \Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory $optionLabelFactory,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory
    ) {
        $this->repository = $repository;
        $this->optionLabelFactory = $optionLabelFactory;
        $this->optionFactory = $optionFactory;
    }

    public function createAttributeOptions(
        \Magento\ImportExport\Model\Import\AbstractSource $source,
        array $attibutes
    ) {
        $this->createDropdownAttributeOptions($source, $attibutes);
        $this->createMultiselectAttributeOptions($source, $attibutes);
    }

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

    public function createMultiselectAttributeOptions(
        \Magento\ImportExport\Model\Import\AbstractSource $source,
        array $attibutes
    ) {
    }

    /**
     * Retrieve list of attribute options
     *
     * @param string $code
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\InputException
     * @return \Magento\Eav\Api\Data\AttributeOptionInterface[]
     */
    private function getAttributeOptions(string $code)
    {
        if (!isset($this->options[$code])) {
            $options = $this->repository->getItems($code);
            $result = [];
            foreach ($options as $option) {
                if (!$option->getValue()) {
                    continue;
                }

                $result[] = $option->getValue();
            }
            $this->options[$code] = $result;
        }
        return $this->options[$code];
    }

    /**
     *
     * @param string $attributeCode
     * @param type $label
     */
    private function createAttributeOption(string $attributeCode, $label)
    {
        $optionLabel = $this->optionLabelFactory->create();
        $optionLabel->setStoreId(0);
        $optionLabel->setLabel($label);

        $option = $this->optionFactory->create();
        $option->setLabel($optionLabel);
        $option->setStoreLabels([$optionLabel]);
        $option->setSortOrder(0);
        $option->setIsDefault(false);

        $this->repository->add($attributeCode, $option);
        $this->options[$attributeCode][] = $label;
    }
}

<?php

/**
 * @author Mygento Team
 * @copyright 2018-2022 Mygento (https://www.mygento.com)
 * @package Mygento_ImportExport
 */

namespace Mygento\ImportExport\Model\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\Data\AttributeInterface as EavAttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Attribute implements \Mygento\ImportExport\Api\AttributeInterface
{
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    private $resourceModel;

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
     * @param \Magento\Catalog\Model\Product\Attribute\OptionManagement $repository
     * @param \Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory $optionLabelFactory
     * @param \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Attribute\OptionManagement $repository,
        \Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory $optionLabelFactory,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory,
        AttributeRepository $attributeRepository,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $resourceModel
    ) {
        $this->repository = $repository;
        $this->optionLabelFactory = $optionLabelFactory;
        $this->optionFactory = $optionFactory;
        $this->attributeRepository = $attributeRepository;
        $this->resourceModel = $resourceModel;
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
                $this->createAttributeOption($attributeCode, trim($row[$attributeCode]));
            }

            $source->next();
        }
    }

    /**
     * TODO
     * @param \Magento\ImportExport\Model\Import\AbstractSource $source
     * @param array $attibutes
     */
    public function createMultiselectAttributeOptions(
        \Magento\ImportExport\Model\Import\AbstractSource $source,
        array $attibutes
    ) {
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
        $attribute = $this->loadAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $code
        );

        $optionLabel = $this->optionLabelFactory->create();
        $optionLabel->setStoreId(0);
        $optionLabel->setLabel($label);

        $option = $this->optionFactory->create();
        $option->setLabel($label);
        $option->setStoreLabels([$optionLabel]);
        $option->setSortOrder(0);
        $option->setIsDefault(false);

        $optionId = $this->getNewOptionId($option);
        $this->saveOption($attribute, $option, $optionId);

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

    /**
     * Save attribute option
     *
     * @param EavAttributeInterface $attribute
     * @param AttributeOptionInterface $option
     * @param int|string $optionId
     * @throws StateException
     * @return AttributeOptionInterface
     */
    private function saveOption(
        EavAttributeInterface $attribute,
        AttributeOptionInterface $option,
        $optionId
    ): AttributeOptionInterface {
        $optionLabel = trim($option->getLabel());
        $options = [];
        $options['value'][$optionId][0] = $optionLabel;
        $options['order'][$optionId] = $option->getSortOrder();
        if (is_array($option->getStoreLabels())) {
            foreach ($option->getStoreLabels() as $label) {
                $options['value'][$optionId][$label->getStoreId()] = $label->getLabel();
            }
        }
        if ($option->getIsDefault()) {
            $attribute->setDefault([$optionId]);
        }

        $attribute->setOption($options);

        try {
            $this->resourceModel->save($attribute);
        } catch (\Exception $e) {
            throw new StateException(__('The "%1" attribute can\'t be saved.', $attribute->getAttributeCode()));
        }

        return $option;
    }

    /**
     * Get option id to create new option
     *
     * @param AttributeOptionInterface $option
     * @return string
     */
    private function getNewOptionId(AttributeOptionInterface $option): string
    {
        $optionId = trim($option->getValue() ?: '');
        if (empty($optionId)) {
            $optionId = 'new_option';
        }

        return 'id_' . $optionId;
    }

    /**
     * Load attribute
     *
     * @param int|string $entityType
     * @param string $attributeCode
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     * @return EavAttributeInterface
     */
    private function loadAttribute($entityType, string $attributeCode): EavAttributeInterface
    {
        if (empty($attributeCode)) {
            throw new InputException(__('The attribute code is empty. Enter the code and try again.'));
        }

        $attribute = $this->attributeRepository->get($entityType, $attributeCode);
        if (!$attribute->usesSource()) {
            throw new StateException(__('The "%1" attribute doesn\'t work with options.', $attributeCode));
        }

        $attribute->setStoreId(0);

        return $attribute;
    }
}

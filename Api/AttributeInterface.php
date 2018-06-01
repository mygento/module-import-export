<?php

/**
 * @author Mygento Team
 * @copyright 2018 Mygento (https://www.mygento.ru)
 * @package Mygento_ImportExport
 */

namespace Mygento\ImportExport\Api;

interface AttributeInterface
{

    /**
     *
     * @param \Magento\ImportExport\Model\Import\AbstractSource $source
     * @param array $attibutes
     */
    public function createAttributeOptions(
        \Magento\ImportExport\Model\Import\AbstractSource $source,
        array $attibutes
    );

    /**
     *
     * @param \Magento\ImportExport\Model\Import\AbstractSource $source
     * @param array $attibutes
     */
    public function createDropdownAttributeOptions(
        \Magento\ImportExport\Model\Import\AbstractSource $source,
        array $attibutes
    );

    /**
     *
     * @param \Magento\ImportExport\Model\Import\AbstractSource $source
     * @param array $attibutes
     */
    public function createMultiselectAttributeOptions(
        \Magento\ImportExport\Model\Import\AbstractSource $source,
        array $attibutes
    );
}

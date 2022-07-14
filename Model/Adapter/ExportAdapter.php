<?php

/**
 * @author Mygento Team
 * @copyright 2018-2020 Mygento (https://www.mygento.ru)
 * @package Mygento_ImportExport
 */

namespace Mygento\ImportExport\Model\Adapter;

class ExportAdapter extends \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter
{
    /**
     * @var array
     */
    private $exportData;

    public function __construct()
    {
        $this->_init();
    }

    /**
     * @param array $rowData
     */
    public function writeRow(array $rowData)
    {
        $this->exportData[] = $rowData;

        return $this;
    }

    /**
     * @return string
     */
    public function getContents(): string
    {
        return implode(PHP_EOL, $this->exportData);
    }

    protected function _init()
    {
        $this->exportData = [];

        return $this;
    }
}

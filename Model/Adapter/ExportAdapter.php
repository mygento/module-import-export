<?php

/**
 * @author Mygento Team
 * @copyright 2018 Mygento (https://www.mygento.ru)
 * @package Mygento_ImportExport
 */

namespace Mygento\ImportExport\Model\Adapter;

class ExportAdapter extends \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter
{
    /**
     *
     * @var array
     */
    private $exportData;

    public function __construct()
    {
        $this->_init();
    }

    protected function _init()
    {
        $this->exportData = [];
        return $this;
    }

    /**
     *
     * @param array $rowData
     */
    public function writeRow(array $rowData)
    {
        $this->exportData[] = $rowData;
    }

    /**
     *
     * @return array
     */
    public function getContents(): array
    {
        return $this->exportData;
    }
}

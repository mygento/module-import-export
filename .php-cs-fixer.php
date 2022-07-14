<?php
$header = <<<EOF
@author Mygento Team
@copyright 2018-2022 Mygento (https://www.mygento.com)
@package Mygento_ImportExport
EOF;

$finder = PhpCsFixer\Finder::create()->in('.')->name('*.phtml');
$config = new \Mygento\CS\Config\Module($header);
$config->setFinder($finder);
return $config;

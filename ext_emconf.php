<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'statica',
    'description' => 'Export tables to static sql dump to provider extension',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Nimmermaer\\Statica\\' => 'Classes/',
        ],
    ],
];

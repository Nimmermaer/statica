<?php

declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title' => 'statica',
    'description' => 'Export tables to static sql dump to provider extension',
    'constraints' => [
        'depends' => [
            'typo3' => '14.0.0-14.3.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Nimmermaer\\Statica\\' => 'Classes/',
        ],
    ],
];

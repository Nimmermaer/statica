<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Database\Schema\SqlReader;

defined('TYPO3') || die();

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][SqlReader::class] = [
    'className' => \Nimmermaer\Statica\XClass\Database\Schema\SqlReader::class,
];

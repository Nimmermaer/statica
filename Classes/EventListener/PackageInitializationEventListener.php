<?php

namespace Nimmermaer\Statica\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Database\Schema\SchemaMigrator;
use TYPO3\CMS\Core\Database\Schema\SqlReader;
use TYPO3\CMS\Core\Package\Event\PackageInitializationEvent;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\PathUtility;

final class PackageInitializationEventListener
{
    public function __construct(
        protected Registry $registry,
        protected SqlReader $sqlReader,
        protected SchemaMigrator $schemaMigrator
    )
    {
    }

    #[AsEventListener(
        identifier: 'static/static-data-import',
        after: 'typo3-core-install-static-data'
    )]
    public function __invoke(PackageInitializationEvent $event): void
    {
        $extTablesStaticSqlFile = $event->getPackage()->getPackagePath() . 'ext_tables_static+adt.sql';
        $extTablesStaticSqlRelFile = PathUtility::stripPathSitePrefix($extTablesStaticSqlFile);
        $currentFileHash = '';
        if (file_exists($extTablesStaticSqlFile)) {
                $extTablesStaticSqlContent = (string)file_get_contents($extTablesStaticSqlFile);
                $statements = $this->sqlReader->getStatementArray($extTablesStaticSqlContent);

                $this->schemaMigrator->importStaticData($statements, true);
                $this->registry->set('extensionDataImport', $extTablesStaticSqlRelFile, $currentFileHash);
                $event->addStorageEntry(self::class, $extTablesStaticSqlFile);
        }
    }
}
<?php

namespace Nimmermaer\Statica\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Adminpanel\Service\EventDispatcher;
use TYPO3\CMS\Core\Package\Event\PackageInitializationEvent;
use TYPO3\CMS\Core\Package\Exception\UnknownPackageException;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[AsCommand(
    name: 'statica:static-import',
    description: 'Import table dump',
    aliases: ['statica:static-import'],
)]
class ImportStaticDataCommand extends Command
{
    protected function configure(): void
    {
        $this->setDescription('Imports static SQL table data for my_extension');
        $this->addArgument(
            'extension',
            InputArgument::REQUIRED,
            'Extension key (e.g. my_ext)'
        );

    }

    /**
     * @throws UnknownPackageException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $extensionKey = $input->getArgument('extension');
        /** @var PackageManager $packageManager */
        $packageManager = GeneralUtility::makeInstance(PackageManager::class);
        $package = $packageManager->getPackage($extensionKey);
        /** @var EventDispatcher $dispatcher */
        $dispatcher = GeneralUtility::makeInstance(EventDispatcher::class);

        $event = new PackageInitializationEvent($extensionKey, $package);
        $dispatcher->dispatch($event);

        $output->writeln('Static data imported.');

        return Command::SUCCESS;
    }
}
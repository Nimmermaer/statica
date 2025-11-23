<?php

declare(strict_types=1);

namespace Nimmermaer\Statica\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

#[AsCommand(
    name: 'statica:static-export',
    description: 'Export table dump',
    aliases: ['statica:static-export'],
)]
class ExportStaticDataCommand extends Command
{

    protected function configure(): void
    {
        $this->setDescription('Export table dump');
        $this->addArgument(
            'extension',
            InputArgument::REQUIRED,
            'Extension key (e.g. my_ext)'
        );

        $this->addArgument(
            'tables',
            InputArgument::IS_ARRAY,
            'List of tables to dump (space-separated)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $extensionKey = $input->getArgument('extension');
        $tables = $input->getArgument('tables');

        if (empty($tables)) {
            $output->writeln('<error>No tables provided.</error>');
            return Command::FAILURE;
        }

        try {
            $extPath = ExtensionManagementUtility::extPath($extensionKey);
        } catch (\Exception) {
            $output->writeln("<error>Extension '{$extensionKey}' not found.</error>");
            return Command::FAILURE;
        }

        $outputFile = $extPath . 'ext_tables_static+adt.sql';

        if (!is_dir(dirname($outputFile))) {
            mkdir(dirname($outputFile), 0775, true);
        }

        file_put_contents($outputFile, '');

        $db = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default'];

        $user = $db['user'] ?? '';
        $password = $db['password'] ?? '';
        $dbname = $db['dbname'] ?? '';

        if (!$user || !$dbname) {
            $output->writeln('<error>Database configuration invalid.</error>');
            return Command::FAILURE;
        }

        foreach ($tables as $table) {
            $output->writeln("<info>Dumping table:</info> {$table}");

            $command = sprintf(
                'mysqldump --user=%s --password=%s %s %s',
                escapeshellarg((string) $user),
                escapeshellarg((string) $password),
                escapeshellarg((string) $dbname),
                escapeshellarg((string) $table)
            );

            file_put_contents(
                $outputFile,
                "\n-- ---------------------------------------\n" .
                "-- Dump for table {$table}\n" .
                "-- ---------------------------------------\n\n",
                FILE_APPEND
            );

            $process = proc_open(
                $command,
                [
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w'],
                ],
                $pipes
            );

            if (!\is_resource($process)) {
                $output->writeln("<error>Could not run mysqldump for table {$table}</error>");
                return Command::FAILURE;
            }

            $dumpOutput = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);

            fclose($pipes[1]);
            fclose($pipes[2]);

            $status = proc_close($process);

            if ($status !== 0) {
                $output->writeln("<error>Error dumping table {$table}:</error>");
                $output->writeln($stderr);
                return Command::FAILURE;
            }

            file_put_contents($outputFile, $dumpOutput . "\n", FILE_APPEND);
        }

        $output->writeln("<info>✔ All tables dumped successfully to:</info> {$outputFile}");

        return Command::SUCCESS;
    }

}

<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Nimmermaer\Statica\XClass\Database\Schema;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
class SqlReader extends \TYPO3\CMS\Core\Database\Schema\SqlReader
{

    /**
     * Returns an array where every entry is a single SQL-statement.
     * Input must be formatted like an ordinary MySQL dump file. Every statements needs to be terminated by a ';'
     * and there may only be one statement (or partial statement) per line.
     *
     * @param string $dumpContent The SQL dump content.
     * @param string|null $queryRegex Regex to select which statements to return.
     * @return array Array of SQL statements
     */
    public function getStatementArray(string $dumpContent, ?string $queryRegex = null): array
    {
        $statementArray = [];
        $statementArrayPointer = 0;
        $isInMultilineComment = false;
        foreach (explode(LF, $dumpContent) as $lineContent) {
            $lineContent = trim($lineContent);
            // Skip empty lines and comments
            $isRealCommentStart = str_starts_with($lineContent, '/*') && !str_starts_with($lineContent, '/*!');
            $isRealCommentEnd   = str_ends_with($lineContent, '*/') && !str_starts_with($lineContent, '/*!');

            if (     $lineContent === ''
                || $lineContent[0] === '#'
                || str_starts_with($lineContent, '--')
                || $isRealCommentStart
                || $isInMultilineComment
                || $isRealCommentEnd
            ) {

                if ($isRealCommentStart && !$isRealCommentEnd) {
                    $isInMultilineComment = true;
                }

                if ($isRealCommentEnd) {
                    $isInMultilineComment = false;
                }
                continue;
            }
            $statementArray[$statementArrayPointer] = ($statementArray[$statementArrayPointer] ?? '') . $lineContent;


            if (str_ends_with($lineContent, ';')) {
                $statement = trim($statementArray[$statementArrayPointer]);
                if (!$statement || ($queryRegex && !preg_match('/' . $queryRegex . '/i', $statement))) {
                    unset($statementArray[$statementArrayPointer]);
                }
                $statementArrayPointer++;
            } else {
                $statementArray[$statementArrayPointer] .= ' ';
            }
        }

        return $statementArray;
    }
}

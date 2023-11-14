<?php

namespace EHAERER\PasteReference\Helper;

use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Information\Typo3Version;

/***************************************************************
 *  Copyright notice
 *  (c) 2013 Dirk Hoffmann <dirk-hoffmann@telekom.de>
 *  (c) 2021-2023 Ephraim Härer <mail@ephra.im>
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Paste reference helper class
 *
 * @author Dirk Hoffmann <dirk-hoffmann@telekom.de>
 */
class Helper implements SingletonInterface
{
    protected static ?Helper $instance = null;

    public static function getInstance(): ?Helper
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * converts tt_content uid into a pid
     *
     * @param int $uid the uid value of a tt_content record
     *
     * @return int
     * @throws DBALException|DBALDriverException
     */
    public function getPidFromUid(int $uid = 0): int
    {
        $queryBuilder = $this->getQueryBuilder();
        $triggerElement = $queryBuilder
            ->select('pid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter(abs($uid), Connection::PARAM_INT)
                )
            )
            ->executeQuery()
            ->fetchAssociative();
        $pid = (int)$triggerElement['pid'];
        return is_array($triggerElement) && $pid ? $pid : 0;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()
            ->removeByType(HiddenRestriction::class)
            ->removeByType(StartTimeRestriction::class)
            ->removeByType(EndTimeRestriction::class);
        return $queryBuilder;
    }

    public function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

}

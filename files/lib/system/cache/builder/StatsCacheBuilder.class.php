<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace poi\system\cache\builder;

use wcf\system\cache\builder\AbstractCacheBuilder;
use wcf\system\WCF;

/**
 * Caches the poi statistics.
 */
class StatsCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    protected $maxLifetime = 1200;

    /**
     * @inheritDoc
     */
    protected function rebuild(array $parameters)
    {
        $data = [];

        // number of pois
        $sql = "SELECT    COUNT(*) AS count, SUM(views) AS views
                FROM    poi" . WCF_N . "_poi";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute();
        $row = $statement->fetchSingleRow();
        $data['pois'] = $row['count'];
        $data['views'] = $row['views'];

        // views per day
        $days = \ceil((TIME_NOW - POI_INSTALL_DATE) / 86400);
        if ($days <= 0) {
            $days = 1;
        }
        $data['viewsPerDay'] = $data['views'] / $days;

        // number of comments
        $sql = "SELECT    SUM(comments)
                FROM    poi" . WCF_N . "_poi
                WHERE    comments > 0";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute();
        $data['comments'] = $statement->fetchSingleColumn();

        // number of authors
        $sql = "SELECT    COUNT(DISTINCT userID)
                FROM    poi" . WCF_N . "_poi";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute();
        $data['authors'] = $statement->fetchSingleColumn();

        return $data;
    }
}

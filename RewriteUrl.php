<?php
/*************************************************************************************/
/*      This file is part of the RewriteUrl module for Thelia.                       */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace RewriteUrl;

use Propel\Runtime\Connection\ConnectionInterface;
use Symfony\Component\Finder\Finder;
use Thelia\Model\ConfigQuery;
use Thelia\Model\Map\RewritingUrlTableMap;
use Thelia\Model\RewritingUrl;
use Thelia\Model\RewritingUrlQuery;
use Thelia\Module\BaseModule;
use Thelia\Install\Database;

/**
 * Class RewriteUrl
 * @package RewriteUrl
 * @author Vincent Lopes <vlopes@openstudio.fr>
 * @author Gilles Bourgeat <gbourgeat@openstudio.fr>
 */
class RewriteUrl extends BaseModule
{
    /** @var string  */
    const MODULE_DOMAIN = "rewriteurl";

    /** @var string  */
    const MODULE_NAME = "rewriteurl";

    /* @var string */
    const UPDATE_PATH = __DIR__ . DS . 'Config' . DS . 'update';

    /** @static null|array */
    static protected $unknownSources;



    public function preActivation(ConnectionInterface $con = null)
    {
        if (!$this->getConfigValue('is_initialized', false)) {
            $database = new Database($con);

            $database->insertSql(null, array(__DIR__ . '/Config/thelia.sql'));

            $this->setConfigValue('is_initialized', true);
        }

        return true;
    }

    /**
     * @param string $currentVersion
     * @param string $newVersion
     * @param ConnectionInterface $con
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     * @since 1.2.3
     */
    public function update($currentVersion, $newVersion, ConnectionInterface $con = null)
    {
        $finder = (new Finder())->files()->name('#.*?\.sql#')->sortByName()->in(self::UPDATE_PATH);

        if ($finder->count() === 0) {
            return;
        }

        $database = new Database($con);

        /** @var \Symfony\Component\Finder\SplFileInfo $updateSQLFile */
        foreach ($finder as $updateSQLFile) {
            if (version_compare($currentVersion, str_replace('.sql', '', $updateSQLFile->getFilename()), '<')) {
                $database->insertSql(null, [$updateSQLFile->getPathname()]);
            }
        }

        /*
         * Fix for urls that redirect on itself
         */
        $urls = RewritingUrlQuery::create()
            ->where(RewritingUrlTableMap::ID . " = " . RewritingUrlTableMap::REDIRECTED)
            ->find();

        /** @var RewritingUrl $url */
        foreach ($urls as $url) {
            $parent = RewritingUrlQuery::create()
                ->filterByView($url->getView())
                ->filterByViewId($url->getViewId())
                ->filterByViewLocale($url->getViewLocale())
                ->filterByRedirected(null)
                ->findOne();

            $url->setRedirected(($parent === null) ? null : $parent->getId())->save();
        }
    }

    /**
     * @return array|null
     */
    public static function getUnknownSources()
    {
        if (static::$unknownSources === null) {
            static::$unknownSources = [];
            if (null !== $config = ConfigQuery::read('obsolete_rewriten_url_view', null)) {
                static::$unknownSources[] = $config;
            }
        }
        return static::$unknownSources;
    }
}

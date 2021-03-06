<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PluginBundle\Bundle;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool;
use Mautic\PluginBundle\Entity\Addon;
use Mautic\PluginBundle\Entity\Plugin;
use Mautic\CoreBundle\Factory\MauticFactory;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Base Bundle class which should be extended by addon bundles
 */
abstract class PluginBundleBase extends Bundle
{
    /**
     * Called by PluginController::reloadAction when adding a new addon that's not already installed
     *
     * @param Plugin        $plugin
     * @param MauticFactory $factory
     * @param null          $metadata
     */

    static public function onPluginInstall(Plugin $plugin, MauticFactory $factory, $metadata = null)
    {
        // BC support; @deprecated 1.1.4; to be removed in 2.0
        if (method_exists(get_called_class(), 'onInstall')) {
            self::onInstall($factory);
        }

        if ($metadata !== null) {
            self::installPluginSchema($metadata, $factory);
        }
    }

    /**
     * Install plugin schema based on Doctrine metadata
     *
     * @param array         $metadata
     * @param MauticFactory $factory
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    static public function installPluginSchema(array $metadata, MauticFactory $factory)
    {
        $db             = $factory->getDatabase();
        $schemaTool     = new SchemaTool($factory->getEntityManager());
        $installQueries = $schemaTool->getCreateSchemaSql($metadata);

        $db->beginTransaction();
        try {
            foreach ($installQueries as $q) {
                $db->query($q);
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            throw $e;
        }
    }

    /**
     * Called by PluginController::reloadAction when the addon version does not match what's installed
     *
     * @param Plugin        $plugin
     * @param MauticFactory $factory
     * @param null          $metadata
     * @param Schema        $installedSchema
     *
     * @throws \Exception
     */
    static public function onPluginUpdate(Plugin $plugin, MauticFactory $factory, $metadata = null, Schema $installedSchema = null)
    {
        // BC support; @deprecated 1.1.4; to be removed in 2.0
        if (method_exists(get_called_class(), 'onUpdate')) {
            // Create a bogus Addon
            $addon = new Addon();
            $addon->setAuthor($plugin->getAuthor())
                ->setBundle($plugin->getBundle())
                ->setDescription($plugin->getDescription())
                ->setId($plugin->getId())
                ->setIntegrations($plugin->getIntegrations())
                ->setIsMissing($plugin->getIsMissing())
                ->setName($plugin->getName())
                ->setVersion($plugin->getVersion());

            self::onUpdate($addon, $factory);
        }

        // Not recommended although availalbe for simple schema changes - see updatePluginSchema docblock
        //self::updatePluginSchema($metadata, $installedSchema, $factory);
    }

    /**
     * Update plugin schema based on Doctrine metadata
     *
     * WARNING - this is not recommended as Doctrine does not guarantee results. There is a risk
     * that Doctrine will generate an incorrect query leading to lost data. If using this method,
     * be sure to thoroughly test the queries Doctrine generates
     *
     * It is preferred to check for MySql or PostgreSQL and execute appropriate raw queries to upgrade
     * from the current version installed to the new version's schema.
     *
     * @param array         $metadata
     * @param Schema        $installedSchema
     * @param MauticFactory $factory
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    static public function updatePluginSchema(array $metadata, Schema $installedSchema, MauticFactory $factory)
    {
        $db         = $factory->getDatabase();
        $schemaTool = new SchemaTool($factory->getEntityManager());
        $toSchema   = $schemaTool->getSchemaFromMetadata($metadata);
        $queries    = $installedSchema->getMigrateToSql($toSchema, $db->getDatabasePlatform());

        $db->beginTransaction();
        try {
            foreach ($queries as $q) {
                $db->query($q);
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            throw $e;
        }
    }

    /**
     * Not used yet :-)
     *
     * @param Plugin        $plugin
     * @param MauticFactory $factory
     * @param null          $metadata
     */
    static public function onPluginUninstall(Plugin $plugin, MauticFactory $factory, $metadata = null)
    {

    }

    /**
     * Drops plugin's tables based on Doctrine metadata
     *
     * @param array         $metadata
     * @param MauticFactory $factory
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    static public function dropPluginSchema(array $metadata, MauticFactory $factory)
    {
        $db          = $factory->getDatabase();
        $schemaTool  = new SchemaTool($factory->getEntityManager());
        $dropQueries = $schemaTool->getDropSchemaSQL($metadata);

        $db->beginTransaction();
        try {
            foreach ($dropQueries as $q) {
                $db->query($q);
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            throw $e;
        }
    }
}

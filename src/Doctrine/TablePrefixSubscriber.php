<?php

namespace PrestaShop\Module\Everpsblog\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

class TablePrefixSubscriber implements EventSubscriber
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $entityNamespacePrefix;

    public function __construct($prefix, $entityNamespacePrefix = 'PrestaShop\\Module\\Everpsblog\\Entity\\')
    {
        $this->prefix = (string) $prefix;
        $this->entityNamespacePrefix = (string) $entityNamespacePrefix;
    }

    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        if ('' === $this->prefix) {
            return;
        }

        $metadata = $eventArgs->getClassMetadata();
        $className = (string) $metadata->getName();

        if (0 !== strpos($className, $this->entityNamespacePrefix)) {
            return;
        }

        if (0 !== strpos($metadata->getTableName(), $this->prefix)) {
            $metadata->setPrimaryTable([
                'name' => $this->prefix . $metadata->getTableName(),
            ]);
        }

        foreach ($metadata->associationMappings as $fieldName => $mapping) {
            if (!isset($mapping['joinTable']['name'])) {
                continue;
            }

            $joinTableName = (string) $mapping['joinTable']['name'];
            if (0 === strpos($joinTableName, $this->prefix)) {
                continue;
            }

            $metadata->associationMappings[$fieldName]['joinTable']['name'] = $this->prefix . $joinTableName;
        }
    }
}

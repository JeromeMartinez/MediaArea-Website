<?php

namespace MediaConchOnlineBundle\Lib\VichUploaderBundle;

use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingInterface;

class UserDirectoryNamer implements DirectoryNamerInterface
{
    /**
     * {@inheritdoc}
     */
    public function directoryName(object|array $object, PropertyMappingInterface $mapping): string
    {
        if ($object->getUser()) {
            return $object->getUser()->getId();
        }

        return '';
    }
}

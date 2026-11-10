<?php

namespace MediaConchOnlineBundle\Lib\VichUploaderBundle;

use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

class UserDirectoryNamer implements DirectoryNamerInterface
{
    /**
     * The arguments have no type: the type of the mapping is not the same in versions 2 and 3 of VichUploaderBundle.
     *
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function directoryName($object, $mapping): string
    {
        if ($object->getUser()) {
            return $object->getUser()->getId();
        }

        return '';
    }
}

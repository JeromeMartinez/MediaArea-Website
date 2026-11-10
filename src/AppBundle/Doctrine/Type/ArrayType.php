<?php

namespace AppBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

/**
 * Array stored as a serialized PHP array in a CLOB column.
 *
 * This is the "array" type of Doctrine DBAL up to version 3, used by the roles of the users.
 */
class ArrayType extends Type
{
    const NAME = 'array';

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL($column);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        // null is serialized too, as before
        return serialize($value);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ErrorControlOperator)
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (null === $value) {
            return null;
        }

        $value = is_resource($value) ? stream_get_contents($value) : $value;

        $data = @unserialize($value, ['allowed_classes' => false]);

        if (false === $data && 'b:0;' !== $value) {
            throw new ConversionException(sprintf('Could not convert database value to "%s".', self::NAME));
        }

        return $data;
    }
}

<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Align the schema with Doctrine DBAL 4: the type of a column is not stored in its comment any more.
 */
final class Version20261110120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove the Doctrine type comments of the columns, rename an index to its mapped name';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE users CHANGE roles roles LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE financial_transactions CHANGE extended_data extended_data LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE payment_instructions CHANGE extended_data extended_data LONGTEXT NOT NULL');

        // The index is replaced instead of renamed: MariaDB has "RENAME INDEX" only since version 10.5
        if ($schema->getTable('user_quotas')->hasIndex('uniq_8h1679f4b67bf763')) {
            $this->addSql('ALTER TABLE user_quotas DROP INDEX uniq_8h1679f4b67bf763, ADD UNIQUE INDEX UNIQ_A08B56FCA76ED395 (user_id)');
        }
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE users CHANGE roles roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE financial_transactions CHANGE extended_data extended_data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:extended_payment_data)\'');
        $this->addSql('ALTER TABLE payment_instructions CHANGE extended_data extended_data LONGTEXT NOT NULL COMMENT \'(DC2Type:extended_payment_data)\'');

        if ($schema->getTable('user_quotas')->hasIndex('UNIQ_A08B56FCA76ED395')) {
            $this->addSql('ALTER TABLE user_quotas DROP INDEX UNIQ_A08B56FCA76ED395, ADD UNIQUE INDEX uniq_8h1679f4b67bf763 (user_id)');
        }
    }
}

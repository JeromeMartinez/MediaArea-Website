<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Payments in a table of the website instead of the tables of JMSPaymentCoreBundle.
 *
 * The tables of JMSPaymentCoreBundle (payment_instructions, payments, financial_transactions, credits) are not
 * modified, they are the archives of the payments made with it: the orders and the invoices keep the identifier
 * of their payment instruction, without the constraints.
 */
final class Version20261110130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the order_payment table, store the payment method in the invoices';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE order_payment (id INT AUTO_INCREMENT NOT NULL, order_id INT NOT NULL, method VARCHAR(100) NOT NULL, amount NUMERIC(10, 5) NOT NULL, currency VARCHAR(3) NOT NULL, state VARCHAR(20) NOT NULL, reference VARCHAR(255) DEFAULT NULL, reason_code VARCHAR(100) DEFAULT NULL, details LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_9B522D468D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_payment ADD CONSTRAINT FK_9B522D468D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id)');

        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_906517448789B572');
        $this->addSql('ALTER TABLE invoice DROP INDEX UNIQ_906517448789B572, ADD payment_id INT DEFAULT NULL, ADD payment_method VARCHAR(100) DEFAULT NULL');
        $this->addSql('UPDATE invoice i INNER JOIN payment_instructions p ON p.id = i.payment_instruction_id SET i.payment_method = p.payment_system_name');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517444C3A3BB FOREIGN KEY (payment_id) REFERENCES order_payment (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_906517444C3A3BB ON invoice (payment_id)');

        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEE8789B572');
        $this->addSql('ALTER TABLE orders DROP INDEX UNIQ_E52FFDEE8789B572');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE orders ADD UNIQUE INDEX UNIQ_E52FFDEE8789B572 (payment_instruction_id)');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE8789B572 FOREIGN KEY (payment_instruction_id) REFERENCES payment_instructions (id)');

        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_906517444C3A3BB');
        $this->addSql('ALTER TABLE invoice DROP INDEX UNIQ_906517444C3A3BB, DROP payment_id, DROP payment_method, ADD UNIQUE INDEX UNIQ_906517448789B572 (payment_instruction_id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517448789B572 FOREIGN KEY (payment_instruction_id) REFERENCES payment_instructions (id)');

        $this->addSql('DROP TABLE order_payment');
    }
}

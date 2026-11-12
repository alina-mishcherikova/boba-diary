<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261112143227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Move the menu currency from store_bubble_tea to store, preserving existing currencies.';
    }

    public function isTransactional(): bool
    {
        // MySQL DDL statements commit implicitly.
        return false;
    }

    public function preUp(Schema $schema): void
    {
        $conflictingStores = $this->connection->createQueryBuilder()
            ->select('store_id_id')
            ->from('store_bubble_tea')
            ->groupBy('store_id_id')
            ->having('COUNT(DISTINCT currency_id) > 1')
            ->fetchFirstColumn();

        $this->abortIf([] !== $conflictingStores, sprintf(
            'These locations have menu items in multiple currencies: %s. Choose one currency per location before retrying; prices will not be converted automatically.',
            implode(', ', $conflictingStores),
        ));
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE store ADD currency_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE store ADD CONSTRAINT FK_FF57587738248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
        $this->addSql('CREATE INDEX IDX_FF57587738248176 ON store (currency_id)');
        $this->addSql('UPDATE store SET currency_id = (SELECT MIN(currency_id) FROM store_bubble_tea WHERE store_id_id = store.id)');
        $this->addSql('ALTER TABLE store_bubble_tea DROP FOREIGN KEY `FK_E0EFBD0E38248176`');
        $this->addSql('DROP INDEX IDX_E0EFBD0E38248176 ON store_bubble_tea');
        $this->addSql('ALTER TABLE store_bubble_tea DROP currency_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE store_bubble_tea ADD currency_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE store_bubble_tea ADD CONSTRAINT FK_E0EFBD0E38248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
        $this->addSql('CREATE INDEX IDX_E0EFBD0E38248176 ON store_bubble_tea (currency_id)');
        $this->addSql('UPDATE store_bubble_tea SET currency_id = (SELECT currency_id FROM store WHERE store.id = store_bubble_tea.store_id_id)');
        $this->addSql('ALTER TABLE store DROP FOREIGN KEY FK_FF57587738248176');
        $this->addSql('DROP INDEX IDX_FF57587738248176 ON store');
        $this->addSql('ALTER TABLE store DROP currency_id');
    }
}

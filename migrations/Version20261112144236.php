<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261112144236 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename store_bubble_tea.store_id_id to store_id, preserving the store relationship.';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE store_bubble_tea DROP FOREIGN KEY `FK_E0EFBD0E37AC84E`');
        $this->addSql('DROP INDEX IDX_E0EFBD0E37AC84E ON store_bubble_tea');
        $this->addSql('ALTER TABLE store_bubble_tea CHANGE store_id_id store_id INT NOT NULL');
        $this->addSql('ALTER TABLE store_bubble_tea ADD CONSTRAINT FK_E0EFBD0EB092A811 FOREIGN KEY (store_id) REFERENCES store (id)');
        $this->addSql('CREATE INDEX IDX_E0EFBD0EB092A811 ON store_bubble_tea (store_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE store_bubble_tea DROP FOREIGN KEY FK_E0EFBD0EB092A811');
        $this->addSql('DROP INDEX IDX_E0EFBD0EB092A811 ON store_bubble_tea');
        $this->addSql('ALTER TABLE store_bubble_tea CHANGE store_id store_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE store_bubble_tea ADD CONSTRAINT `FK_E0EFBD0E37AC84E` FOREIGN KEY (store_id_id) REFERENCES store (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_E0EFBD0E37AC84E ON store_bubble_tea (store_id_id)');
    }
}

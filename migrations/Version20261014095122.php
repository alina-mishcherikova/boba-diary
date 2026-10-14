<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261014095122 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bubble_tea (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, inactivated_at DATETIME DEFAULT NULL, created_by_id INT NOT NULL, INDEX IDX_CE06E6E3B03A8386 (created_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE currency (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(3) NOT NULL, name VARCHAR(50) NOT NULL, symbol VARCHAR(10) NOT NULL, is_active TINYINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE store_bubble_tea (id INT AUTO_INCREMENT NOT NULL, price NUMERIC(10, 2) NOT NULL, is_available TINYINT NOT NULL, created_at DATETIME NOT NULL, store_id_id INT NOT NULL, bubble_tea_id INT NOT NULL, currency_id INT DEFAULT NULL, INDEX IDX_E0EFBD0E37AC84E (store_id_id), INDEX IDX_E0EFBD0E2CA25A58 (bubble_tea_id), INDEX IDX_E0EFBD0E38248176 (currency_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE bubble_tea ADD CONSTRAINT FK_CE06E6E3B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE store_bubble_tea ADD CONSTRAINT FK_E0EFBD0E37AC84E FOREIGN KEY (store_id_id) REFERENCES store (id)');
        $this->addSql('ALTER TABLE store_bubble_tea ADD CONSTRAINT FK_E0EFBD0E2CA25A58 FOREIGN KEY (bubble_tea_id) REFERENCES bubble_tea (id)');
        $this->addSql('ALTER TABLE store_bubble_tea ADD CONSTRAINT FK_E0EFBD0E38248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE store CHANGE created_by created_by_id INT NOT NULL');
        $this->addSql('ALTER TABLE store ADD CONSTRAINT FK_FF575877B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_FF575877B03A8386 ON store (created_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bubble_tea DROP FOREIGN KEY FK_CE06E6E3B03A8386');
        $this->addSql('ALTER TABLE store_bubble_tea DROP FOREIGN KEY FK_E0EFBD0E37AC84E');
        $this->addSql('ALTER TABLE store_bubble_tea DROP FOREIGN KEY FK_E0EFBD0E2CA25A58');
        $this->addSql('ALTER TABLE store_bubble_tea DROP FOREIGN KEY FK_E0EFBD0E38248176');
        $this->addSql('DROP TABLE bubble_tea');
        $this->addSql('DROP TABLE currency');
        $this->addSql('DROP TABLE store_bubble_tea');
        $this->addSql('ALTER TABLE store DROP FOREIGN KEY FK_FF575877B03A8386');
        $this->addSql('DROP INDEX IDX_FF575877B03A8386 ON store');
        $this->addSql('ALTER TABLE store CHANGE created_by_id created_by INT NOT NULL');
    }
}

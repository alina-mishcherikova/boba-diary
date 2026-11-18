<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261118082841 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE drink_log (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, store_bubble_tea_id INT NOT NULL, consumed_at DATETIME NOT NULL, created_at DATETIME NOT NULL, rating NUMERIC(2, 1) NOT NULL, note LONGTEXT DEFAULT NULL, photo_url VARCHAR(255) DEFAULT NULL, paid_price NUMERIC(10, 2) DEFAULT NULL, paid_currency_id INT DEFAULT NULL, temperature INT DEFAULT NULL, sugar_level INT DEFAULT NULL, ice_level INT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE drink_log');
    }
}

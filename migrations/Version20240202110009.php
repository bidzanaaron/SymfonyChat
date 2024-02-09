<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240202110009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE violation (id INT AUTO_INCREMENT NOT NULL, issuer_id INT DEFAULT NULL, recipient_id INT NOT NULL, type VARCHAR(255) NOT NULL, reason VARCHAR(255) NOT NULL, notes LONGTEXT NOT NULL, status VARCHAR(255) NOT NULL, valid_until DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_E7BA44E2BB9D6FEE (issuer_id), INDEX IDX_E7BA44E2E92F8F78 (recipient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE violation ADD CONSTRAINT FK_E7BA44E2BB9D6FEE FOREIGN KEY (issuer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE violation ADD CONSTRAINT FK_E7BA44E2E92F8F78 FOREIGN KEY (recipient_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE violation DROP FOREIGN KEY FK_E7BA44E2BB9D6FEE');
        $this->addSql('ALTER TABLE violation DROP FOREIGN KEY FK_E7BA44E2E92F8F78');
        $this->addSql('DROP TABLE violation');
    }
}

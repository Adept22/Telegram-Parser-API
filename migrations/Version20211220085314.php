<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211220085314 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.phones ADD code VARCHAR(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE telegram.phones RENAME COLUMN phone_code_hash TO code_hash');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.phones DROP code');
        $this->addSql('ALTER TABLE telegram.phones RENAME COLUMN code_hash TO phone_code_hash');
    }
}

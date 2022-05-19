<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220519153308 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_31f81ed5f85e0677');
        $this->addSql('ALTER TABLE telegram.phones ADD last_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE telegram.phones DROP username');
        $this->addSql('ALTER TABLE telegram.phones ALTER first_name DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.phones ADD username VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE telegram.phones DROP last_name');
        $this->addSql('ALTER TABLE telegram.phones ALTER first_name SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_31f81ed5f85e0677 ON telegram.phones (username)');
    }
}

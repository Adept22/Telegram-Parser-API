<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211215114235 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE telegram.phones (id UUID NOT NULL, phone VARCHAR(20) NOT NULL, username VARCHAR(20) NOT NULL, first_name VARCHAR(255) NOT NULL, is_verified BOOLEAN DEFAULT \'false\' NOT NULL, is_banned BOOLEAN DEFAULT \'false\' NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5984949F444F97DD ON telegram.phones (phone)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5984949FF85E0677 ON telegram.phones (username)');
        $this->addSql('COMMENT ON COLUMN telegram.phones.id IS \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE telegram.phones');
    }
}

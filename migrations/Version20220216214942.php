<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220216214942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA app');
        $this->addSql('CREATE TABLE app.export (id UUID NOT NULL, chat_id UUID DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, path VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_ED90A08B1A9A7125 ON app.export (chat_id)');
        $this->addSql('COMMENT ON COLUMN app.export.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN app.export.chat_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE app.export ADD CONSTRAINT FK_ED90A08B1A9A7125 FOREIGN KEY (chat_id) REFERENCES telegram.chats (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE app.export');
        $this->addSql('DROP SCHEMA app');
    }
}

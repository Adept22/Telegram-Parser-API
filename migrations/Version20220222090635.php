<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220222090635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE telegram.hosts (id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, public_ip VARCHAR(15) DEFAULT NULL, local_ip VARCHAR(15) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_578BB9F4780F57C ON telegram.hosts (local_ip)');
        $this->addSql('COMMENT ON COLUMN telegram.hosts.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE telegram.parsers (id UUID NOT NULL, host_id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, status VARCHAR(20) NOT NULL, api_id INT NOT NULL, api_hash VARCHAR(32) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E991ADB61FB8D185 ON telegram.parsers (host_id)');
        $this->addSql('COMMENT ON COLUMN telegram.parsers.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram.parsers.host_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE telegram.parsers ADD CONSTRAINT FK_E991ADB61FB8D185 FOREIGN KEY (host_id) REFERENCES telegram.hosts (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram.chats_medias ALTER path DROP NOT NULL');
        $this->addSql('ALTER TABLE telegram.members_medias ALTER path DROP NOT NULL');
        $this->addSql('ALTER TABLE telegram.messages_medias ALTER path DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.parsers DROP CONSTRAINT FK_E991ADB61FB8D185');
        $this->addSql('DROP TABLE telegram.hosts');
        $this->addSql('DROP TABLE telegram.parsers');
        $this->addSql('ALTER TABLE telegram.chats_medias ALTER path SET NOT NULL');
        $this->addSql('ALTER TABLE telegram.messages_medias ALTER path SET NOT NULL');
        $this->addSql('ALTER TABLE telegram.members_medias ALTER path SET NOT NULL');
    }
}

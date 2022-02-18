<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220218110527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE telegram.parsers (id UUID NOT NULL, server_id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, container_id VARCHAR(12) NOT NULL, container_name VARCHAR(255) NOT NULL, status VARCHAR(20) NOT NULL, api_id INT NOT NULL, api_hash VARCHAR(32) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E991ADB61844E6B7 ON telegram.parsers (server_id)');
        $this->addSql('COMMENT ON COLUMN telegram.parsers.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram.parsers.server_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE app.servers (id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, ip VARCHAR(15) NOT NULL, port INT NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C22DE4B4A5E3B32D ON app.servers (ip)');
        $this->addSql('COMMENT ON COLUMN app.servers.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE telegram.parsers ADD CONSTRAINT FK_E991ADB61844E6B7 FOREIGN KEY (server_id) REFERENCES app.servers (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.parsers DROP CONSTRAINT FK_E991ADB61844E6B7');
        $this->addSql('DROP TABLE telegram.parsers');
        $this->addSql('DROP TABLE app.servers');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220322150811 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.chats ADD last_message_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE telegram.chats ADD last_media_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE telegram.chats ADD members_count INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE telegram.chats ADD messages_count INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE telegram.members ADD last_media_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.members DROP last_media_id');
        $this->addSql('ALTER TABLE telegram.chats DROP last_message_id');
        $this->addSql('ALTER TABLE telegram.chats DROP last_media_id');
        $this->addSql('ALTER TABLE telegram.chats DROP members_count');
        $this->addSql('ALTER TABLE telegram.chats DROP messages_count');
    }
}

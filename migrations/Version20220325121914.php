<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220325121914 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.chats ADD last_message_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE telegram.chats ADD last_media_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE telegram.chats ADD date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE telegram.chats ADD members_count INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE telegram.chats ADD messages_count INT DEFAULT 0 NOT NULL');
        $this->addSql('COMMENT ON COLUMN telegram.chats.last_message_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram.chats.last_media_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE telegram.chats ADD CONSTRAINT FK_F0DDC529BA0E79C3 FOREIGN KEY (last_message_id) REFERENCES telegram.messages (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram.chats ADD CONSTRAINT FK_F0DDC52992FE98EB FOREIGN KEY (last_media_id) REFERENCES telegram.chats_medias (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F0DDC529BA0E79C3 ON telegram.chats (last_message_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F0DDC52992FE98EB ON telegram.chats (last_media_id)');
        $this->addSql('ALTER TABLE telegram.chats_medias ADD date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE telegram.chats_members ADD date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE telegram.members ADD last_media_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN telegram.members.last_media_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE telegram.members ADD CONSTRAINT FK_7E1C220792FE98EB FOREIGN KEY (last_media_id) REFERENCES telegram.members_medias (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7E1C220792FE98EB ON telegram.members (last_media_id)');
        $this->addSql('ALTER TABLE telegram.members_medias ADD date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE telegram.messages ADD date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE telegram.messages_medias ADD date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.chats_medias DROP date');
        $this->addSql('ALTER TABLE telegram.chats_members DROP date');
        $this->addSql('ALTER TABLE telegram.members DROP CONSTRAINT FK_7E1C220792FE98EB');
        $this->addSql('DROP INDEX UNIQ_7E1C220792FE98EB');
        $this->addSql('ALTER TABLE telegram.members DROP last_media_id');
        $this->addSql('ALTER TABLE telegram.chats DROP CONSTRAINT FK_F0DDC529BA0E79C3');
        $this->addSql('ALTER TABLE telegram.chats DROP CONSTRAINT FK_F0DDC52992FE98EB');
        $this->addSql('DROP INDEX UNIQ_F0DDC529BA0E79C3');
        $this->addSql('DROP INDEX UNIQ_F0DDC52992FE98EB');
        $this->addSql('ALTER TABLE telegram.chats DROP last_message_id');
        $this->addSql('ALTER TABLE telegram.chats DROP last_media_id');
        $this->addSql('ALTER TABLE telegram.chats DROP date');
        $this->addSql('ALTER TABLE telegram.chats DROP members_count');
        $this->addSql('ALTER TABLE telegram.chats DROP messages_count');
        $this->addSql('ALTER TABLE telegram.messages_medias DROP date');
        $this->addSql('ALTER TABLE telegram.members_medias DROP date');
        $this->addSql('ALTER TABLE telegram.messages DROP date');
    }
}

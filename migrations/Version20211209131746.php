<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211209131746 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA telegram');

        $this->addSql('CREATE TABLE telegram.chats (id UUID NOT NULL, internal_id VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN telegram.chats.id IS \'(DC2Type:uuid)\'');

        $this->addSql('CREATE TABLE telegram.chats_medias (id UUID NOT NULL, chat_id UUID DEFAULT NULL, path VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7E216051A9A7125 ON telegram.chats_medias (chat_id)');
        $this->addSql('COMMENT ON COLUMN telegram.chats_medias.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram.chats_medias.chat_id IS \'(DC2Type:uuid)\'');

        $this->addSql('CREATE TABLE telegram.chats_members (id UUID NOT NULL, chat_id UUID NOT NULL, member_id UUID NOT NULL, is_left BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AF60A57F1A9A7125 ON telegram.chats_members (chat_id)');
        $this->addSql('CREATE INDEX IDX_AF60A57F7597D3FE ON telegram.chats_members (member_id)');
        $this->addSql('COMMENT ON COLUMN telegram.chats_members.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram.chats_members.chat_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram.chats_members.member_id IS \'(DC2Type:uuid)\'');

        $this->addSql('CREATE TABLE telegram.chats_members_roles (id UUID NOT NULL, member_id UUID NOT NULL, title VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_97B780F77597D3FE ON telegram.chats_members_roles (member_id)');
        $this->addSql('COMMENT ON COLUMN telegram.chats_members_roles.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram.chats_members_roles.member_id IS \'(DC2Type:uuid)\'');

        $this->addSql('CREATE TABLE telegram.members (id UUID NOT NULL, internal_id VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, about TEXT DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN telegram.members.id IS \'(DC2Type:uuid)\'');

        $this->addSql('CREATE TABLE telegram.members_medias (id UUID NOT NULL, member_id UUID NOT NULL, path VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4AD27F9D7597D3FE ON telegram.members_medias (member_id)');
        $this->addSql('COMMENT ON COLUMN telegram.members_medias.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram.members_medias.member_id IS \'(DC2Type:uuid)\'');

        $this->addSql('CREATE TABLE telegram.messages (id UUID NOT NULL, member_id UUID NOT NULL, reply_to_id UUID DEFAULT NULL, internal_id VARCHAR(255) NOT NULL, text TEXT DEFAULT NULL, is_pinned BOOLEAN NOT NULL, forwarded_from_id VARCHAR(255) DEFAULT NULL, forwarded_from_name VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_685FD8487597D3FE ON telegram.messages (member_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_685FD848FFDF7169 ON telegram.messages (reply_to_id)');
        $this->addSql('COMMENT ON COLUMN telegram.messages.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram.messages.member_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram.messages.reply_to_id IS \'(DC2Type:uuid)\'');

        $this->addSql('CREATE TABLE telegram.messages_medias (id UUID NOT NULL, message_id UUID NOT NULL, path VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_26B96AA8537A1329 ON telegram.messages_medias (message_id)');
        $this->addSql('COMMENT ON COLUMN telegram.messages_medias.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram.messages_medias.message_id IS \'(DC2Type:uuid)\'');

        $this->addSql('ALTER TABLE telegram.chats_medias ADD CONSTRAINT FK_7E216051A9A7125 FOREIGN KEY (chat_id) REFERENCES telegram.chats (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram.chats_members ADD CONSTRAINT FK_AF60A57F1A9A7125 FOREIGN KEY (chat_id) REFERENCES telegram.chats (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram.chats_members ADD CONSTRAINT FK_AF60A57F7597D3FE FOREIGN KEY (member_id) REFERENCES telegram.members (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram.chats_members_roles ADD CONSTRAINT FK_97B780F77597D3FE FOREIGN KEY (member_id) REFERENCES telegram.chats_members (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram.members_medias ADD CONSTRAINT FK_4AD27F9D7597D3FE FOREIGN KEY (member_id) REFERENCES telegram.members (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram.messages ADD CONSTRAINT FK_685FD8487597D3FE FOREIGN KEY (member_id) REFERENCES telegram.chats_members (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram.messages ADD CONSTRAINT FK_685FD848FFDF7169 FOREIGN KEY (reply_to_id) REFERENCES telegram.messages (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram.messages_medias ADD CONSTRAINT FK_26B96AA8537A1329 FOREIGN KEY (message_id) REFERENCES telegram.messages (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.chats_medias DROP CONSTRAINT FK_7E216051A9A7125');
        $this->addSql('ALTER TABLE telegram.chats_members DROP CONSTRAINT FK_AF60A57F1A9A7125');
        $this->addSql('ALTER TABLE telegram.chats_members_roles DROP CONSTRAINT FK_97B780F77597D3FE');
        $this->addSql('ALTER TABLE telegram.messages DROP CONSTRAINT FK_685FD8487597D3FE');
        $this->addSql('ALTER TABLE telegram.chats_members DROP CONSTRAINT FK_AF60A57F7597D3FE');
        $this->addSql('ALTER TABLE telegram.members_medias DROP CONSTRAINT FK_4AD27F9D7597D3FE');
        $this->addSql('ALTER TABLE telegram.messages DROP CONSTRAINT FK_685FD848FFDF7169');
        $this->addSql('ALTER TABLE telegram.messages_medias DROP CONSTRAINT FK_26B96AA8537A1329');
        $this->addSql('DROP TABLE telegram.chats');
        $this->addSql('DROP TABLE telegram.chats_medias');
        $this->addSql('DROP TABLE telegram.chats_members');
        $this->addSql('DROP TABLE telegram.chats_members_roles');
        $this->addSql('DROP TABLE telegram.members');
        $this->addSql('DROP TABLE telegram.members_medias');
        $this->addSql('DROP TABLE telegram.messages');
        $this->addSql('DROP TABLE telegram.messages_medias');
        $this->addSql('DROP SCHEMA telegram');
    }
}

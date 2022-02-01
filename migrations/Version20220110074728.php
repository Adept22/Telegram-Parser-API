<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220110074728 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.messages ADD chat_id UUID NOT NULL');
        $this->addSql('COMMENT ON COLUMN telegram.messages.chat_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE telegram.messages ADD CONSTRAINT FK_685FD8481A9A7125 FOREIGN KEY (chat_id) REFERENCES telegram.chats (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_685FD8481A9A7125 ON telegram.messages (chat_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.messages DROP CONSTRAINT FK_685FD8481A9A7125');
        $this->addSql('DROP INDEX telegram.IDX_685FD8481A9A7125');
        $this->addSql('ALTER TABLE telegram.messages DROP chat_id');
    }
}

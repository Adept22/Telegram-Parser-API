<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220126074030 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE telegram_chat_available_telegram_phone (telegram_chat_id UUID NOT NULL, telegram_phone_id UUID NOT NULL, PRIMARY KEY(telegram_chat_id, telegram_phone_id))');
        $this->addSql('CREATE INDEX IDX_E66BB3E741DC10D3 ON telegram_chat_available_telegram_phone (telegram_chat_id)');
        $this->addSql('CREATE INDEX IDX_E66BB3E76FF63283 ON telegram_chat_available_telegram_phone (telegram_phone_id)');
        $this->addSql('COMMENT ON COLUMN telegram_chat_available_telegram_phone.telegram_chat_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram_chat_available_telegram_phone.telegram_phone_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE telegram_chat_available_telegram_phone ADD CONSTRAINT FK_E66BB3E741DC10D3 FOREIGN KEY (telegram_chat_id) REFERENCES telegram.chats (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram_chat_available_telegram_phone ADD CONSTRAINT FK_E66BB3E76FF63283 FOREIGN KEY (telegram_phone_id) REFERENCES telegram.phones (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram.phones DROP code_hash');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE telegram_chat_available_telegram_phone');
        $this->addSql('ALTER TABLE telegram.phones ADD code_hash VARCHAR(255) DEFAULT NULL');
    }
}

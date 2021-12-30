<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211230111110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram_chat_telegram_phone DROP CONSTRAINT FK_163BB0C241DC10D3');
        $this->addSql('ALTER TABLE telegram_chat_telegram_phone DROP CONSTRAINT FK_163BB0C26FF63283');
        $this->addSql('ALTER TABLE telegram_chat_telegram_phone ADD CONSTRAINT FK_163BB0C241DC10D3 FOREIGN KEY (telegram_chat_id) REFERENCES telegram.chats (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram_chat_telegram_phone ADD CONSTRAINT FK_163BB0C26FF63283 FOREIGN KEY (telegram_phone_id) REFERENCES telegram.phones (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram_chat_telegram_phone DROP CONSTRAINT fk_163bb0c241dc10d3');
        $this->addSql('ALTER TABLE telegram_chat_telegram_phone DROP CONSTRAINT fk_163bb0c26ff63283');
        $this->addSql('ALTER TABLE telegram_chat_telegram_phone ADD CONSTRAINT fk_163bb0c241dc10d3 FOREIGN KEY (telegram_chat_id) REFERENCES telegram.chats (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram_chat_telegram_phone ADD CONSTRAINT fk_163bb0c26ff63283 FOREIGN KEY (telegram_phone_id) REFERENCES telegram.phones (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Uuid;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220418141126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE telegram.chats_phones (id UUID NOT NULL, chat_id UUID NOT NULL, phone_id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, is_using BOOLEAN DEFAULT \'false\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F61897711A9A7125 ON telegram.chats_phones (chat_id)');
        $this->addSql('CREATE INDEX IDX_F61897713B7323CB ON telegram.chats_phones (phone_id)');
        $this->addSql('CREATE UNIQUE INDEX chat_phone_unique ON telegram.chats_phones (chat_id, phone_id)');
        $this->addSql('COMMENT ON COLUMN telegram.chats_phones.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram.chats_phones.chat_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram.chats_phones.phone_id IS \'(DC2Type:uuid)\'');

        $qb = $this->connection->createQueryBuilder();
        $aps = $qb->select(["telegram_chat_id", "telegram_phone_id"])
            ->from("telegram_chat_available_telegram_phone")
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($aps as $ap) {
            $id = (string) Uuid::uuid1();
            $chatId = $ap["telegram_chat_id"];
            $phoneId = $ap["telegram_phone_id"];

            $this->addSql("INSERT INTO telegram.chats_phones (id, chat_id, phone_id) VALUES ('$id', '$chatId', '$phoneId')");
        }

        $this->addSql("UPDATE telegram.chats_phones SET is_using = TRUE WHERE phone_id in (SELECT phone_id FROM telegram_chat_telegram_phone)");

        $this->addSql('ALTER TABLE telegram.chats_phones ADD CONSTRAINT FK_F61897711A9A7125 FOREIGN KEY (chat_id) REFERENCES telegram.chats (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram.chats_phones ADD CONSTRAINT FK_F61897713B7323CB FOREIGN KEY (phone_id) REFERENCES telegram.phones (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE telegram_chat_telegram_phone');
        $this->addSql('DROP TABLE telegram_chat_available_telegram_phone');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE telegram_chat_telegram_phone (telegram_chat_id UUID NOT NULL, telegram_phone_id UUID NOT NULL, PRIMARY KEY(telegram_chat_id, telegram_phone_id))');
        $this->addSql('CREATE INDEX idx_163bb0c26ff63283 ON telegram_chat_telegram_phone (telegram_phone_id)');
        $this->addSql('CREATE INDEX idx_163bb0c241dc10d3 ON telegram_chat_telegram_phone (telegram_chat_id)');
        $this->addSql('COMMENT ON COLUMN telegram_chat_telegram_phone.telegram_chat_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram_chat_telegram_phone.telegram_phone_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE telegram_chat_available_telegram_phone (telegram_chat_id UUID NOT NULL, telegram_phone_id UUID NOT NULL, PRIMARY KEY(telegram_chat_id, telegram_phone_id))');
        $this->addSql('CREATE INDEX idx_e66bb3e741dc10d3 ON telegram_chat_available_telegram_phone (telegram_chat_id)');
        $this->addSql('CREATE INDEX idx_e66bb3e76ff63283 ON telegram_chat_available_telegram_phone (telegram_phone_id)');
        $this->addSql('COMMENT ON COLUMN telegram_chat_available_telegram_phone.telegram_chat_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram_chat_available_telegram_phone.telegram_phone_id IS \'(DC2Type:uuid)\'');
        
        $this->addSql("INSERT INTO telegram_chat_available_telegram_phone SELECT chat_id, phone_id FROM telegram.chats_phones");
        $this->addSql("INSERT INTO telegram_chat_telegram_phone SELECT chat_id, phone_id FROM telegram.chats_phones WHERE is_using = true");

        $this->addSql('ALTER TABLE telegram_chat_telegram_phone ADD CONSTRAINT fk_163bb0c241dc10d3 FOREIGN KEY (telegram_chat_id) REFERENCES telegram.chats (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram_chat_telegram_phone ADD CONSTRAINT fk_163bb0c26ff63283 FOREIGN KEY (telegram_phone_id) REFERENCES telegram.phones (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram_chat_available_telegram_phone ADD CONSTRAINT fk_e66bb3e741dc10d3 FOREIGN KEY (telegram_chat_id) REFERENCES telegram.chats (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram_chat_available_telegram_phone ADD CONSTRAINT fk_e66bb3e76ff63283 FOREIGN KEY (telegram_phone_id) REFERENCES telegram.phones (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE telegram.chats_phones');
    }
}

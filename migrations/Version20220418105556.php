<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Uuid;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220418105556 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE telegram.chats_available_phones (id UUID NOT NULL, chat_id UUID NOT NULL, parser_phone_id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B0B9AE061A9A7125 ON telegram.chats_available_phones (chat_id)');
        $this->addSql('CREATE INDEX IDX_B0B9AE0669A43005 ON telegram.chats_available_phones (parser_phone_id)');
        $this->addSql('CREATE UNIQUE INDEX chat_available_phone_unique ON telegram.chats_available_phones (chat_id, parser_phone_id)');
        $this->addSql('COMMENT ON COLUMN telegram.chats_available_phones.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram.chats_available_phones.chat_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram.chats_available_phones.parser_phone_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE telegram.chats_phones (id UUID NOT NULL, chat_id UUID NOT NULL, available_phone_id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F61897711A9A7125 ON telegram.chats_phones (chat_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F618977120F9D09B ON telegram.chats_phones (available_phone_id)');
        $this->addSql('COMMENT ON COLUMN telegram.chats_phones.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram.chats_phones.chat_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram.chats_phones.available_phone_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE telegram.parsers_phones (id UUID NOT NULL, parser_id UUID NOT NULL, phone_id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BB2DB1CEF54E453B ON telegram.parsers_phones (parser_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BB2DB1CE3B7323CB ON telegram.parsers_phones (phone_id)');
        $this->addSql('CREATE UNIQUE INDEX parser_phone_unique ON telegram.parsers_phones (parser_id, phone_id)');
        $this->addSql('COMMENT ON COLUMN telegram.parsers_phones.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram.parsers_phones.parser_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram.parsers_phones.phone_id IS \'(DC2Type:uuid)\'');

        $chatAvailablePhones = [];

        $qb = $this->connection->createQueryBuilder();
        $phones = $qb->select(["id", "parser_id"])
            ->from("telegram.phones")
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($phones as $phone) {
            $parserPhoneId = (string) Uuid::uuid1();
            $parserId = $phone["parser_id"];
            $phoneId = $phone["id"];

            $this->addSql("INSERT INTO telegram.parsers_phones (id, parser_id, phone_id) VALUES ('$parserPhoneId', '$parserId', '$phoneId')");

            $chatAvailablePhones[$phoneId] = [];

            $qb = $this->connection->createQueryBuilder();
            $availablePhones = $qb->select(["telegram_chat_id"])
                ->from("telegram_chat_available_telegram_phone")
                ->where("telegram_phone_id = :phoneId")
                ->setParameter("phoneId", $phoneId)
                ->executeQuery()
                ->fetchAllAssociative();

            foreach ($availablePhones as $availablePhone) {
                $chatAvailablePhoneId = (string) Uuid::uuid1();
                $chatId = $availablePhone['telegram_chat_id'];

                $this->addSql("INSERT INTO telegram.chats_available_phones (id, chat_id, parser_phone_id) VALUES ('$chatAvailablePhoneId', '$chatId', '$parserPhoneId')");

                $chatAvailablePhones[$phoneId][$chatId] = $chatAvailablePhoneId;
            }

            $qb = $this->connection->createQueryBuilder();
            $chatPhones = $qb->select(["telegram_chat_id"])
                ->from("telegram_chat_telegram_phone")
                ->where("telegram_phone_id = :phoneId")
                ->setParameter("phoneId", $phoneId)
                ->executeQuery()
                ->fetchAllAssociative();

            foreach ($chatPhones as $chatPhone) {
                $chatPhoneId = (string) Uuid::uuid1();
                $chatId = $chatPhone["telegram_chat_id"];
                $chatAvailablePhoneId = $chatAvailablePhones[$phoneId][$chatId];

                $this->addSql("INSERT INTO telegram.chats_phones (id, chat_id, available_phone_id) VALUES ('$chatPhoneId', '$chatId', '$chatAvailablePhoneId')");
            }
        }

        $this->addSql('ALTER TABLE telegram.chats_available_phones ADD CONSTRAINT FK_B0B9AE061A9A7125 FOREIGN KEY (chat_id) REFERENCES telegram.chats (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram.chats_available_phones ADD CONSTRAINT FK_B0B9AE0669A43005 FOREIGN KEY (parser_phone_id) REFERENCES telegram.parsers_phones (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram.chats_phones ADD CONSTRAINT FK_F61897711A9A7125 FOREIGN KEY (chat_id) REFERENCES telegram.chats (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram.chats_phones ADD CONSTRAINT FK_F618977120F9D09B FOREIGN KEY (available_phone_id) REFERENCES telegram.chats_available_phones (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram.parsers_phones ADD CONSTRAINT FK_BB2DB1CEF54E453B FOREIGN KEY (parser_id) REFERENCES telegram.parsers (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram.parsers_phones ADD CONSTRAINT FK_BB2DB1CE3B7323CB FOREIGN KEY (phone_id) REFERENCES telegram.phones (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE telegram_chat_telegram_phone');
        $this->addSql('DROP TABLE telegram_chat_available_telegram_phone');
        $this->addSql('ALTER TABLE telegram.phones DROP CONSTRAINT fk_31f81ed5f54e453b');
        $this->addSql('DROP INDEX telegram.idx_31f81ed5f54e453b');
        $this->addSql('ALTER TABLE telegram.phones DROP parser_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.chats_phones DROP CONSTRAINT FK_F618977120F9D09B');
        $this->addSql('ALTER TABLE telegram.chats_available_phones DROP CONSTRAINT FK_B0B9AE0669A43005');
        $this->addSql('CREATE TABLE telegram_chat_telegram_phone (telegram_chat_id UUID NOT NULL, telegram_phone_id UUID NOT NULL, PRIMARY KEY(telegram_chat_id, telegram_phone_id))');
        $this->addSql('CREATE INDEX idx_163bb0c26ff63283 ON telegram_chat_telegram_phone (telegram_phone_id)');
        $this->addSql('CREATE INDEX idx_163bb0c241dc10d3 ON telegram_chat_telegram_phone (telegram_chat_id)');
        $this->addSql('COMMENT ON COLUMN telegram_chat_telegram_phone.telegram_chat_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram_chat_telegram_phone.telegram_phone_id IS \'(DC2Type:uuid)\'');

        $this->addSql('CREATE TABLE telegram_chat_available_telegram_phone (telegram_chat_id UUID NOT NULL, telegram_phone_id UUID NOT NULL, PRIMARY KEY(telegram_chat_id, telegram_phone_id))');
        $this->addSql('CREATE INDEX idx_e66bb3e76ff63283 ON telegram_chat_available_telegram_phone (telegram_phone_id)');
        $this->addSql('CREATE INDEX idx_e66bb3e741dc10d3 ON telegram_chat_available_telegram_phone (telegram_chat_id)');
        $this->addSql('COMMENT ON COLUMN telegram_chat_available_telegram_phone.telegram_chat_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN telegram_chat_available_telegram_phone.telegram_phone_id IS \'(DC2Type:uuid)\'');

        $this->addSql('ALTER TABLE telegram.phones ADD parser_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN telegram.phones.parser_id IS \'(DC2Type:uuid)\'');

        $qb = $this->connection->createQueryBuilder();
        $qb->select()
            ->from("telegram.parsers_phones");
        $phones = $qb->executeQuery()->fetchAllAssociative();

        foreach ($phones as $phone) {
            $phoneId = $phone["phone_id"];
            $parserId = $phone["parser_id"];

            $this->addSql("UPDATE telegram.phones SET parser_id = '$parserId' WHERE id = '$phoneId'");

            $qb = $this->connection->createQueryBuilder();
            $qb->select(["id", "chat_id"])
                ->from("telegram.chats_available_phones")
                ->where("parser_id = :parserId")
                ->setParameter("parserId", $parserId);
            $availablePhones = $qb->executeQuery()->fetchAllAssociative();
    
            foreach ($availablePhones as $availablePhone) {
                $availablePhoneId = $availablePhone["id"];
                $chatId = $availablePhone["chat_id"];
    
                $this->addSql("INSERT INTO telegram_chat_available_telegram_phone (telegram_chat_id, telegram_phone_id) VALUES ('$chatId', '$phoneId')");

                $qb = $this->connection->createQueryBuilder();
                $qb->select(["chat_id"])
                    ->from("telegram.chats_phones")
                    ->where("available_phone_id = :availablePhoneId")
                    ->setParameter("availablePhoneId", $availablePhoneId);
                $chatPhones = $qb->executeQuery()->fetchAllAssociative();

                foreach ($chatPhones as $chatPhone) {
                    $this->addSql("INSERT INTO telegram_chat_available_telegram_phone (telegram_chat_id, telegram_phone_id) VALUES ('$chatId', '$phoneId')");
                }
            }
        }

        $this->addSql('ALTER TABLE telegram.phones ALTER COLUMN parser_id SET NOT NULL');
        $this->addSql('ALTER TABLE telegram_chat_telegram_phone ADD CONSTRAINT fk_163bb0c241dc10d3 FOREIGN KEY (telegram_chat_id) REFERENCES telegram.chats (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram_chat_telegram_phone ADD CONSTRAINT fk_163bb0c26ff63283 FOREIGN KEY (telegram_phone_id) REFERENCES telegram.phones (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram_chat_available_telegram_phone ADD CONSTRAINT fk_e66bb3e741dc10d3 FOREIGN KEY (telegram_chat_id) REFERENCES telegram.chats (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE telegram_chat_available_telegram_phone ADD CONSTRAINT fk_e66bb3e76ff63283 FOREIGN KEY (telegram_phone_id) REFERENCES telegram.phones (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE telegram.chats_available_phones');
        $this->addSql('DROP TABLE telegram.chats_phones');
        $this->addSql('DROP TABLE telegram.parsers_phones');
        $this->addSql('ALTER TABLE telegram.phones ADD CONSTRAINT fk_31f81ed5f54e453b FOREIGN KEY (parser_id) REFERENCES telegram.parsers (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_31f81ed5f54e453b ON telegram.phones (parser_id)');
    }
}

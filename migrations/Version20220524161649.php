<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220524161649 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE OR REPLACE FUNCTION telegram_chat_members_increment() RETURNS TRIGGER AS $$ BEGIN UPDATE telegram.chats SET members_count = members_count + 1 WHERE id = NEW.chat_id; RETURN NEW; END $$ LANGUAGE \'plpgsql\'');
        $this->addSql('CREATE TRIGGER telegram_chat_members_increment_trigger AFTER INSERT ON telegram.chats_members FOR EACH ROW EXECUTE PROCEDURE telegram_chat_members_increment()');
        $this->addSql('CREATE OR REPLACE FUNCTION telegram_chat_members_decrement() RETURNS TRIGGER AS $$ BEGIN UPDATE telegram.chats SET members_count = members_count - 1 WHERE id = NEW.chat_id; RETURN NEW; END $$ LANGUAGE \'plpgsql\'');
        $this->addSql('CREATE TRIGGER telegram_chat_members_decrement_trigger AFTER DELETE ON telegram.chats_members FOR EACH ROW EXECUTE PROCEDURE telegram_chat_members_decrement()');
        
        $this->addSql('CREATE OR REPLACE FUNCTION telegram_chat_messages_increment() RETURNS TRIGGER AS $$ BEGIN UPDATE telegram.chats SET messages_count = messages_count + 1 WHERE id = NEW.chat_id; RETURN NEW; END $$ LANGUAGE \'plpgsql\'');
        $this->addSql('CREATE TRIGGER telegram_chat_messages_increment_trigger AFTER INSERT ON telegram.messages FOR EACH ROW EXECUTE PROCEDURE telegram_chat_messages_increment()');
        $this->addSql('CREATE OR REPLACE FUNCTION telegram_chat_messages_decrement() RETURNS TRIGGER AS $$ BEGIN UPDATE telegram.chats SET messages_count = messages_count - 1 WHERE id = NEW.chat_id; RETURN NEW; END $$ LANGUAGE \'plpgsql\'');
        $this->addSql('CREATE TRIGGER telegram_chat_messages_decrement_trigger AFTER DELETE ON telegram.messages FOR EACH ROW EXECUTE PROCEDURE telegram_chat_messages_decrement()');
        

        $this->addSql('CREATE OR REPLACE FUNCTION telegram_chat_last_message_date_update() RETURNS TRIGGER AS $$ BEGIN UPDATE telegram.chats SET last_message_date = (SELECT date FROM telegram.messages WHERE chat_id = telegram.chats.id ORDER BY date DESC LIMIT 1) WHERE id = NEW.chat_id; RETURN NEW; END $$ LANGUAGE \'plpgsql\'');
        $this->addSql('CREATE TRIGGER telegram_chat_last_message_date_update_trigger AFTER INSERT OR DELETE ON telegram.messages FOR EACH ROW EXECUTE PROCEDURE telegram_chat_last_message_date_update()');
        
        $this->addSql('CREATE OR REPLACE FUNCTION telegram_chat_last_media_update() RETURNS TRIGGER AS $$ BEGIN UPDATE telegram.chats SET last_media_id = (SELECT id FROM telegram.chats_medias WHERE chat_id = telegram.chats.id ORDER BY date DESC LIMIT 1) WHERE id = NEW.chat_id; RETURN NEW; END $$ LANGUAGE \'plpgsql\'');
        $this->addSql('CREATE TRIGGER telegram_chat_last_media_update_trigger AFTER INSERT OR DELETE ON telegram.chats_medias FOR EACH ROW EXECUTE PROCEDURE telegram_chat_last_media_update()');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

        $this->addSql('DROP TRIGGER telegram_chat_members_increment_trigger ON telegram.chats_members');
        $this->addSql('DROP FUNCTION telegram_chat_members_increment');
        $this->addSql('DROP TRIGGER telegram_chat_members_decrement_trigger ON telegram.chats_members');
        $this->addSql('DROP FUNCTION telegram_chat_members_decrement');
        $this->addSql('DROP TRIGGER telegram_chat_messages_increment_trigger ON telegram.messages');
        $this->addSql('DROP FUNCTION telegram_chat_messages_increment');
        $this->addSql('DROP TRIGGER telegram_chat_messages_decrement_trigger ON telegram.messages');
        $this->addSql('DROP FUNCTION telegram_chat_messages_decrement');
        $this->addSql('DROP TRIGGER telegram_chat_last_message_date_update_trigger ON telegram.messages');
        $this->addSql('DROP FUNCTION telegram_chat_last_message_date_update');
        $this->addSql('DROP TRIGGER telegram_chat_last_media_update_trigger ON telegram.chats_medias');
        $this->addSql('DROP FUNCTION telegram_chat_last_media_update');
    }
}

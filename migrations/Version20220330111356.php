<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220330111356 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.chats DROP CONSTRAINT fk_f0ddc529ba0e79c3');
        $this->addSql('DROP INDEX telegram.uniq_f0ddc529ba0e79c3');
        $this->addSql('ALTER TABLE telegram.chats ADD last_message_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE telegram.chats DROP last_message_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.chats ADD last_message_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE telegram.chats DROP last_message_date');
        $this->addSql('COMMENT ON COLUMN telegram.chats.last_message_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE telegram.chats ADD CONSTRAINT fk_f0ddc529ba0e79c3 FOREIGN KEY (last_message_id) REFERENCES telegram.messages (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_f0ddc529ba0e79c3 ON telegram.chats (last_message_id)');
    }
}

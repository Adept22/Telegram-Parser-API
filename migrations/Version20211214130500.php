<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211214130500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.chats ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE telegram.chats ALTER created_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE telegram.chats_medias ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE telegram.chats_medias ALTER created_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE telegram.chats_members ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE telegram.chats_members ALTER created_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE telegram.members ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE telegram.members ALTER created_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE telegram.members_medias ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE telegram.members_medias ALTER created_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE telegram.messages ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE telegram.messages ALTER created_at SET DEFAULT CURRENT_TIMESTAMP');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.chats ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE telegram.chats ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE telegram.chats_medias ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE telegram.chats_medias ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE telegram.chats_members ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE telegram.chats_members ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE telegram.members ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE telegram.members ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE telegram.members_medias ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE telegram.members_medias ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE telegram.messages ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE telegram.messages ALTER created_at DROP DEFAULT');
    }
}

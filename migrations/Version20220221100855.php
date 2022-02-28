<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220221100855 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.chats_medias ALTER path DROP NOT NULL');
        $this->addSql('ALTER TABLE telegram.members_medias ALTER path DROP NOT NULL');
        $this->addSql('ALTER TABLE telegram.messages_medias ALTER path DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.chats_medias ALTER path SET NOT NULL');
        $this->addSql('ALTER TABLE telegram.messages_medias ALTER path SET NOT NULL');
        $this->addSql('ALTER TABLE telegram.members_medias ALTER path SET NOT NULL');
    }
}

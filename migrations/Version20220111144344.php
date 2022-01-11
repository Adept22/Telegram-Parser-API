<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220111144344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.chats ALTER internal_id TYPE BIGINT');
        $this->addSql('ALTER TABLE telegram.chats ALTER internal_id DROP DEFAULT');
        $this->addSql('ALTER TABLE telegram.messages ALTER forwarded_from_id TYPE BIGINT');
        $this->addSql('ALTER TABLE telegram.messages ALTER forwarded_from_id DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.chats ALTER internal_id TYPE INT');
        $this->addSql('ALTER TABLE telegram.chats ALTER internal_id DROP DEFAULT');
        $this->addSql('ALTER TABLE telegram.messages ALTER forwarded_from_id TYPE INT');
        $this->addSql('ALTER TABLE telegram.messages ALTER forwarded_from_id DROP DEFAULT');
    }
}

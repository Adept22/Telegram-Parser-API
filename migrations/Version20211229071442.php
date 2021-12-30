<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211229071442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.chats ALTER internal_id DROP DEFAULT');
        $this->addSql('ALTER TABLE telegram.chats ALTER internal_id TYPE INT USING internal_id::INT');
        $this->addSql('ALTER TABLE telegram.chats ALTER access_hash DROP DEFAULT');
        $this->addSql('ALTER TABLE telegram.chats ALTER access_hash TYPE BIGINT USING access_hash::BIGINT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.chats ALTER internal_id TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE telegram.chats ALTER internal_id DROP DEFAULT');
        $this->addSql('ALTER TABLE telegram.chats ALTER access_hash TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE telegram.chats ALTER access_hash DROP DEFAULT');
    }
}

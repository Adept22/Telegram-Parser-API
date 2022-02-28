<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220203133339 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.chats ADD description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE telegram.chats ADD system_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE telegram.chats ADD system_description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE telegram.chats ADD lat DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE telegram.chats ADD lon DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.chats DROP description');
        $this->addSql('ALTER TABLE telegram.chats DROP system_title');
        $this->addSql('ALTER TABLE telegram.chats DROP system_description');
        $this->addSql('ALTER TABLE telegram.chats DROP lat');
        $this->addSql('ALTER TABLE telegram.chats DROP lon');
    }
}

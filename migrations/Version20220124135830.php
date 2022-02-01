<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220124135830 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.chats_medias ADD internal_id BIGINT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7E21605BFDFB4D8 ON telegram.chats_medias (internal_id)');
        $this->addSql('ALTER TABLE telegram.members_medias ADD internal_id BIGINT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4AD27F9DBFDFB4D8 ON telegram.members_medias (internal_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX telegram.UNIQ_7E21605BFDFB4D8');
        $this->addSql('ALTER TABLE telegram.chats_medias DROP internal_id');
        $this->addSql('DROP INDEX telegram.UNIQ_4AD27F9DBFDFB4D8');
        $this->addSql('ALTER TABLE telegram.members_medias DROP internal_id');
    }
}

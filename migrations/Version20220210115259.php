<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220210115259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.messages_medias ADD internal_id BIGINT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_26B96AA8BFDFB4D8 ON telegram.messages_medias (internal_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_26B96AA8BFDFB4D8');
        $this->addSql('ALTER TABLE telegram.messages_medias DROP internal_id');
    }
}

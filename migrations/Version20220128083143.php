<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220128083143 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.messages ALTER internal_id TYPE BIGINT');
        $this->addSql('ALTER TABLE telegram.messages ALTER internal_id DROP DEFAULT');
        $this->addSql('ALTER TABLE telegram.phones ADD internal_id BIGINT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_31F81ED5BFDFB4D8 ON telegram.phones (internal_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_31F81ED5BFDFB4D8');
        $this->addSql('ALTER TABLE telegram.phones DROP internal_id');
        $this->addSql('ALTER TABLE telegram.messages ALTER internal_id TYPE INT');
        $this->addSql('ALTER TABLE telegram.messages ALTER internal_id DROP DEFAULT');
    }
}

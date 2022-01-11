<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220111095343 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.messages ALTER grouped_id TYPE BIGINT');
        $this->addSql('ALTER TABLE telegram.messages ALTER grouped_id DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.messages ALTER grouped_id TYPE INT');
        $this->addSql('ALTER TABLE telegram.messages ALTER grouped_id DROP DEFAULT');
    }
}

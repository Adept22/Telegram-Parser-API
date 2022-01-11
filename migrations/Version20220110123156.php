<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220110123156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX telegram.uniq_685fd848ffdf7169');
        $this->addSql('CREATE INDEX IDX_685FD848FFDF7169 ON telegram.messages (reply_to_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_685FD848FFDF7169');
        $this->addSql('CREATE UNIQUE INDEX uniq_685fd848ffdf7169 ON telegram.messages (reply_to_id)');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211230093152 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX telegram.uniq_f0ddc529276104be');
        $this->addSql('ALTER TABLE telegram.chats DROP access_hash');
        $this->addSql('ALTER TABLE telegram.chats ALTER is_available DROP DEFAULT');
        $this->addSql('DROP INDEX telegram.uniq_7e1c2207276104be');
        $this->addSql('ALTER TABLE telegram.members DROP access_hash');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.members ADD access_hash BIGINT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_7e1c2207276104be ON telegram.members (access_hash)');
        $this->addSql('ALTER TABLE telegram.chats ADD access_hash BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE telegram.chats ALTER is_available SET DEFAULT \'false\'');
        $this->addSql('CREATE UNIQUE INDEX uniq_f0ddc529276104be ON telegram.chats (access_hash)');
    }
}

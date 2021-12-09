<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211209153232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F0DDC529BFDFB4D8 ON telegram.chats (internal_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7E1C2207BFDFB4D8 ON telegram.members (internal_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_685FD848BFDFB4D8 ON telegram.messages (internal_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_F0DDC529BFDFB4D8');
        $this->addSql('DROP INDEX UNIQ_7E1C2207BFDFB4D8');
        $this->addSql('DROP INDEX UNIQ_685FD848BFDFB4D8');
    }
}

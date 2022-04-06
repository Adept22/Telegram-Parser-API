<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220406145910 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX chat_member_unique ON telegram.chats_members (chat_id, member_id)');
        $this->addSql('CREATE UNIQUE INDEX chat_member_role_unique ON telegram.chats_members_roles (member_id, title, code)');
        $this->addSql('CREATE UNIQUE INDEX message_unique ON telegram.messages (internal_id, chat_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX chat_member_unique');
        $this->addSql('DROP INDEX chat_member_role_unique');
        $this->addSql('DROP INDEX message_unique');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220222104213 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.chats ADD parser_id UUID NOT NULL');
        $this->addSql('COMMENT ON COLUMN telegram.chats.parser_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE telegram.chats ADD CONSTRAINT FK_F0DDC529F54E453B FOREIGN KEY (parser_id) REFERENCES telegram.parsers (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F0DDC529F54E453B ON telegram.chats (parser_id)');

        $this->addSql('ALTER TABLE telegram.phones ADD parser_id UUID NOT NULL');
        $this->addSql('COMMENT ON COLUMN telegram.phones.parser_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE telegram.phones ADD CONSTRAINT FK_31F81ED5F54E453B FOREIGN KEY (parser_id) REFERENCES telegram.parsers (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_31F81ED5F54E453B ON telegram.phones (parser_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.chats DROP CONSTRAINT FK_F0DDC529F54E453B');
        $this->addSql('DROP INDEX IDX_F0DDC529F54E453B');
        $this->addSql('ALTER TABLE telegram.chats DROP parser_id');
        $this->addSql('ALTER TABLE telegram.phones DROP CONSTRAINT FK_31F81ED5F54E453B');
        $this->addSql('DROP INDEX IDX_31F81ED5F54E453B');
        $this->addSql('ALTER TABLE telegram.phones DROP parser_id');
    }
}

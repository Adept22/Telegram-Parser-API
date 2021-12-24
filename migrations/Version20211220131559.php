<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211220131559 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_31f81ed5444f97dd');
        $this->addSql('ALTER TABLE telegram.phones RENAME COLUMN phone TO number');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_31F81ED596901F54 ON telegram.phones (number)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_31F81ED596901F54');
        $this->addSql('ALTER TABLE telegram.phones RENAME COLUMN number TO phone');
        $this->addSql('CREATE UNIQUE INDEX uniq_31f81ed5444f97dd ON telegram.phones (phone)');
    }
}

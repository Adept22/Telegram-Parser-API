<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211215140706 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.phones ADD phone_code_hash VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER INDEX telegram.uniq_5984949f444f97dd RENAME TO UNIQ_31F81ED5444F97DD');
        $this->addSql('ALTER INDEX telegram.uniq_5984949ff85e0677 RENAME TO UNIQ_31F81ED5F85E0677');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.phones DROP phone_code_hash');
        $this->addSql('ALTER INDEX telegram.uniq_31f81ed5f85e0677 RENAME TO uniq_5984949ff85e0677');
        $this->addSql('ALTER INDEX telegram.uniq_31f81ed5444f97dd RENAME TO uniq_5984949f444f97dd');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211229111632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram.members ADD access_hash BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE telegram.members ALTER internal_id DROP DEFAULT');
        $this->addSql('ALTER TABLE telegram.members ALTER internal_id TYPE INT USING internal_id::INT');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7E1C2207276104BE ON telegram.members (access_hash)');
        $this->addSql('ALTER TABLE telegram.messages ALTER internal_id DROP DEFAULT');
        $this->addSql('ALTER TABLE telegram.messages ALTER internal_id TYPE INT USING internal_id::INT');
        $this->addSql('ALTER TABLE telegram.messages ALTER forwarded_from_id DROP DEFAULT');
        $this->addSql('ALTER TABLE telegram.messages ALTER forwarded_from_id TYPE INT USING forwarded_from_id::INT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_7E1C2207276104BE');
        $this->addSql('ALTER TABLE telegram.members DROP access_hash');
        $this->addSql('ALTER TABLE telegram.members ALTER internal_id DROP DEFAULT');
        $this->addSql('ALTER TABLE telegram.members ALTER internal_id TYPE VARCHAR(255) USING internal_id::VARCHAR');
        $this->addSql('ALTER TABLE telegram.messages ALTER internal_id DROP DEFAULT');
        $this->addSql('ALTER TABLE telegram.messages ALTER internal_id TYPE VARCHAR(255) USING internal_id::VARCHAR');
        $this->addSql('ALTER TABLE telegram.messages ALTER forwarded_from_id DROP DEFAULT');
        $this->addSql('ALTER TABLE telegram.messages ALTER forwarded_from_id TYPE VARCHAR(255) USING forwarded_from_id::VARCHAR');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200701112939 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mission CHANGE subscription subscription LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE sensor ADD metadata LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', ADD stream LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', ADD stream_id VARCHAR(255) DEFAULT NULL, ADD stream_observation LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', ADD stream_observation_id VARCHAR(255) DEFAULT NULL, DROP type');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mission CHANGE subscription subscription VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE sensor ADD type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP metadata, DROP stream, DROP stream_id, DROP stream_observation, DROP stream_observation_id');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200323085421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE experiment_log_entry (id INT AUTO_INCREMENT NOT NULL, experiment_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', sensor_id VARCHAR(255) DEFAULT NULL, content LONGTEXT NOT NULL, logged_at DATETIME NOT NULL, type VARCHAR(255) DEFAULT NULL, INDEX IDX_5AB75B1EFF444C8 (experiment_id), INDEX IDX_5AB75B1EA247991F (sensor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE experiment_log_entry ADD CONSTRAINT FK_5AB75B1EFF444C8 FOREIGN KEY (experiment_id) REFERENCES experiment (id)');
        $this->addSql('ALTER TABLE experiment_log_entry ADD CONSTRAINT FK_5AB75B1EA247991F FOREIGN KEY (sensor_id) REFERENCES sensor (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE experiment_log_entry');
    }
}

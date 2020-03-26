<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200326145706 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sensor_warning (id INT AUTO_INCREMENT NOT NULL, experiment_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', sensor_id VARCHAR(255) NOT NULL, message VARCHAR(1024) NOT NULL, min INT DEFAULT NULL, max INT DEFAULT NULL, INDEX IDX_5D571B8CFF444C8 (experiment_id), INDEX IDX_5D571B8CA247991F (sensor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sensor_warning ADD CONSTRAINT FK_5D571B8CFF444C8 FOREIGN KEY (experiment_id) REFERENCES experiment (id)');
        $this->addSql('ALTER TABLE sensor_warning ADD CONSTRAINT FK_5D571B8CA247991F FOREIGN KEY (sensor_id) REFERENCES sensor (id)');
        $this->addSql('ALTER TABLE experiment ADD finished_at DATETIME DEFAULT NULL, ADD latitude DOUBLE PRECISION NOT NULL, ADD longitude DOUBLE PRECISION NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sensor_warning');
        $this->addSql('ALTER TABLE experiment DROP finished_at, DROP latitude, DROP longitude');
    }
}

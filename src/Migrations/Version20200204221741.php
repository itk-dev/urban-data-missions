<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200204221741 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sensor (id VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE measurement (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', experiment_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', sensor VARCHAR(255) NOT NULL, measured_at DATETIME NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', payload LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', value DOUBLE PRECISION NOT NULL, INDEX IDX_2CE0D811FF444C8 (experiment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE experiment (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', title VARCHAR(255) NOT NULL, subscription VARCHAR(255) DEFAULT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, deleted_by VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE experiment_sensor (experiment_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', sensor_id VARCHAR(255) NOT NULL, INDEX IDX_4B4C3CCDFF444C8 (experiment_id), INDEX IDX_4B4C3CCDA247991F (sensor_id), PRIMARY KEY(experiment_id, sensor_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE measurement ADD CONSTRAINT FK_2CE0D811FF444C8 FOREIGN KEY (experiment_id) REFERENCES experiment (id)');
        $this->addSql('ALTER TABLE experiment_sensor ADD CONSTRAINT FK_4B4C3CCDFF444C8 FOREIGN KEY (experiment_id) REFERENCES experiment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE experiment_sensor ADD CONSTRAINT FK_4B4C3CCDA247991F FOREIGN KEY (sensor_id) REFERENCES sensor (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE experiment_sensor DROP FOREIGN KEY FK_4B4C3CCDA247991F');
        $this->addSql('ALTER TABLE measurement DROP FOREIGN KEY FK_2CE0D811FF444C8');
        $this->addSql('ALTER TABLE experiment_sensor DROP FOREIGN KEY FK_4B4C3CCDFF444C8');
        $this->addSql('DROP TABLE sensor');
        $this->addSql('DROP TABLE measurement');
        $this->addSql('DROP TABLE experiment');
        $this->addSql('DROP TABLE experiment_sensor');
    }
}

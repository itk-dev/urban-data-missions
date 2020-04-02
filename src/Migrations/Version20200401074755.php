<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200401074755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE mission (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', theme_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, location VARCHAR(255) NOT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, finished_at DATETIME DEFAULT NULL, subscription VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_9067F23C59027487 (theme_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cms_page (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(128) NOT NULL, type VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, image VARCHAR(255) DEFAULT NULL, position INT NOT NULL, published TINYINT(1) NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_D39C1B5D989D9B62 (slug), INDEX IDX_D39C1B5D727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mission_log_entry (id INT AUTO_INCREMENT NOT NULL, mission_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', sensor_id VARCHAR(255) DEFAULT NULL, content LONGTEXT NOT NULL, logged_at DATETIME NOT NULL, type VARCHAR(255) DEFAULT NULL, INDEX IDX_B9EA8104BE6CAE90 (mission_id), INDEX IDX_B9EA8104A247991F (sensor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sensor (id VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mission_sensor (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', mission_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', sensor_id VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, INDEX IDX_E47ECDFEBE6CAE90 (mission_id), INDEX IDX_E47ECDFEA247991F (sensor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE measurement (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', mission_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', sensor_id VARCHAR(255) NOT NULL, measured_at DATETIME NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', payload LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', value DOUBLE PRECISION NOT NULL, INDEX IDX_2CE0D811BE6CAE90 (mission_id), INDEX IDX_2CE0D811A247991F (sensor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sensor_warning (id INT AUTO_INCREMENT NOT NULL, sensor_id VARCHAR(255) NOT NULL, mission_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', message VARCHAR(1024) NOT NULL, min INT DEFAULT NULL, max INT DEFAULT NULL, INDEX IDX_5D571B8CA247991F (sensor_id), INDEX IDX_5D571B8CBE6CAE90 (mission_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mission_theme (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, icon VARCHAR(255) NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mission ADD CONSTRAINT FK_9067F23C59027487 FOREIGN KEY (theme_id) REFERENCES mission_theme (id)');
        $this->addSql('ALTER TABLE cms_page ADD CONSTRAINT FK_D39C1B5D727ACA70 FOREIGN KEY (parent_id) REFERENCES cms_page (id)');
        $this->addSql('ALTER TABLE mission_log_entry ADD CONSTRAINT FK_B9EA8104BE6CAE90 FOREIGN KEY (mission_id) REFERENCES mission (id)');
        $this->addSql('ALTER TABLE mission_log_entry ADD CONSTRAINT FK_B9EA8104A247991F FOREIGN KEY (sensor_id) REFERENCES sensor (id)');
        $this->addSql('ALTER TABLE mission_sensor ADD CONSTRAINT FK_E47ECDFEBE6CAE90 FOREIGN KEY (mission_id) REFERENCES mission (id)');
        $this->addSql('ALTER TABLE mission_sensor ADD CONSTRAINT FK_E47ECDFEA247991F FOREIGN KEY (sensor_id) REFERENCES sensor (id)');
        $this->addSql('ALTER TABLE measurement ADD CONSTRAINT FK_2CE0D811BE6CAE90 FOREIGN KEY (mission_id) REFERENCES mission (id)');
        $this->addSql('ALTER TABLE measurement ADD CONSTRAINT FK_2CE0D811A247991F FOREIGN KEY (sensor_id) REFERENCES sensor (id)');
        $this->addSql('ALTER TABLE sensor_warning ADD CONSTRAINT FK_5D571B8CA247991F FOREIGN KEY (sensor_id) REFERENCES sensor (id)');
        $this->addSql('ALTER TABLE sensor_warning ADD CONSTRAINT FK_5D571B8CBE6CAE90 FOREIGN KEY (mission_id) REFERENCES mission (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mission_log_entry DROP FOREIGN KEY FK_B9EA8104BE6CAE90');
        $this->addSql('ALTER TABLE mission_sensor DROP FOREIGN KEY FK_E47ECDFEBE6CAE90');
        $this->addSql('ALTER TABLE measurement DROP FOREIGN KEY FK_2CE0D811BE6CAE90');
        $this->addSql('ALTER TABLE sensor_warning DROP FOREIGN KEY FK_5D571B8CBE6CAE90');
        $this->addSql('ALTER TABLE cms_page DROP FOREIGN KEY FK_D39C1B5D727ACA70');
        $this->addSql('ALTER TABLE mission_log_entry DROP FOREIGN KEY FK_B9EA8104A247991F');
        $this->addSql('ALTER TABLE mission_sensor DROP FOREIGN KEY FK_E47ECDFEA247991F');
        $this->addSql('ALTER TABLE measurement DROP FOREIGN KEY FK_2CE0D811A247991F');
        $this->addSql('ALTER TABLE sensor_warning DROP FOREIGN KEY FK_5D571B8CA247991F');
        $this->addSql('ALTER TABLE mission DROP FOREIGN KEY FK_9067F23C59027487');
        $this->addSql('DROP TABLE mission');
        $this->addSql('DROP TABLE cms_page');
        $this->addSql('DROP TABLE mission_log_entry');
        $this->addSql('DROP TABLE sensor');
        $this->addSql('DROP TABLE mission_sensor');
        $this->addSql('DROP TABLE measurement');
        $this->addSql('DROP TABLE sensor_warning');
        $this->addSql('DROP TABLE mission_theme');
    }
}

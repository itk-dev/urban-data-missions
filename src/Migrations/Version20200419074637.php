<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200419074637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE mission_sensor_warning (id INT AUTO_INCREMENT NOT NULL, mission_sensor_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', min INT DEFAULT NULL, max INT DEFAULT NULL, message VARCHAR(1024) NOT NULL, INDEX IDX_9F51882610F26E36 (mission_sensor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mission_sensor_warning ADD CONSTRAINT FK_9F51882610F26E36 FOREIGN KEY (mission_sensor_id) REFERENCES mission_sensor (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE mission_sensor_warning');
    }
}

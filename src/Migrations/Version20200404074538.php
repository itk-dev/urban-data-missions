<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200404074538 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mission_log_entry DROP FOREIGN KEY FK_B9EA8104A247991F');
        $this->addSql('DROP INDEX IDX_B9EA8104A247991F ON mission_log_entry');
        $this->addSql('ALTER TABLE mission_log_entry ADD measurement_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', DROP sensor_id');
        $this->addSql('ALTER TABLE mission_log_entry ADD CONSTRAINT FK_B9EA8104924EA134 FOREIGN KEY (measurement_id) REFERENCES measurement (id)');
        $this->addSql('CREATE INDEX IDX_B9EA8104924EA134 ON mission_log_entry (measurement_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mission_log_entry DROP FOREIGN KEY FK_B9EA8104924EA134');
        $this->addSql('DROP INDEX IDX_B9EA8104924EA134 ON mission_log_entry');
        $this->addSql('ALTER TABLE mission_log_entry ADD sensor_id VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP measurement_id');
        $this->addSql('ALTER TABLE mission_log_entry ADD CONSTRAINT FK_B9EA8104A247991F FOREIGN KEY (sensor_id) REFERENCES sensor (id)');
        $this->addSql('CREATE INDEX IDX_B9EA8104A247991F ON mission_log_entry (sensor_id)');
    }
}

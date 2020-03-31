<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200331073509 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mission_sensor DROP FOREIGN KEY FK_E47ECDFEA247991F');
        $this->addSql('ALTER TABLE mission_sensor DROP FOREIGN KEY FK_E47ECDFEBE6CAE90');
        $this->addSql('ALTER TABLE mission_sensor ADD id INT AUTO_INCREMENT NOT NULL, ADD name VARCHAR(255) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE mission_sensor ADD CONSTRAINT FK_E47ECDFEA247991F FOREIGN KEY (sensor_id) REFERENCES sensor (id)');
        $this->addSql('ALTER TABLE mission_sensor ADD CONSTRAINT FK_E47ECDFEBE6CAE90 FOREIGN KEY (mission_id) REFERENCES mission (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mission_sensor MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE mission_sensor DROP FOREIGN KEY FK_E47ECDFEBE6CAE90');
        $this->addSql('ALTER TABLE mission_sensor DROP FOREIGN KEY FK_E47ECDFEA247991F');
        $this->addSql('ALTER TABLE mission_sensor DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE mission_sensor DROP id, DROP name');
        $this->addSql('ALTER TABLE mission_sensor ADD CONSTRAINT FK_E47ECDFEBE6CAE90 FOREIGN KEY (mission_id) REFERENCES mission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mission_sensor ADD CONSTRAINT FK_E47ECDFEA247991F FOREIGN KEY (sensor_id) REFERENCES sensor (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mission_sensor ADD PRIMARY KEY (mission_id, sensor_id)');
    }
}

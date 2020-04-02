<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200401112355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cms_page ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL, DROP created_by, DROP updated_by');
        $this->addSql('ALTER TABLE cms_page ADD CONSTRAINT FK_D39C1B5DB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cms_page ADD CONSTRAINT FK_D39C1B5D896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_D39C1B5DB03A8386 ON cms_page (created_by_id)');
        $this->addSql('CREATE INDEX IDX_D39C1B5D896DBBDE ON cms_page (updated_by_id)');
        $this->addSql('ALTER TABLE mission_theme ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL, DROP created_by, DROP updated_by');
        $this->addSql('ALTER TABLE mission_theme ADD CONSTRAINT FK_7E3B835B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE mission_theme ADD CONSTRAINT FK_7E3B835896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_7E3B835B03A8386 ON mission_theme (created_by_id)');
        $this->addSql('CREATE INDEX IDX_7E3B835896DBBDE ON mission_theme (updated_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cms_page DROP FOREIGN KEY FK_D39C1B5DB03A8386');
        $this->addSql('ALTER TABLE cms_page DROP FOREIGN KEY FK_D39C1B5D896DBBDE');
        $this->addSql('ALTER TABLE mission_theme DROP FOREIGN KEY FK_7E3B835B03A8386');
        $this->addSql('ALTER TABLE mission_theme DROP FOREIGN KEY FK_7E3B835896DBBDE');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP INDEX IDX_D39C1B5DB03A8386 ON cms_page');
        $this->addSql('DROP INDEX IDX_D39C1B5D896DBBDE ON cms_page');
        $this->addSql('ALTER TABLE cms_page ADD created_by VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD updated_by VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP created_by_id, DROP updated_by_id');
        $this->addSql('DROP INDEX IDX_7E3B835B03A8386 ON mission_theme');
        $this->addSql('DROP INDEX IDX_7E3B835896DBBDE ON mission_theme');
        $this->addSql('ALTER TABLE mission_theme ADD created_by VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD updated_by VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP created_by_id, DROP updated_by_id');
    }
}

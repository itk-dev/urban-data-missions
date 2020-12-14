<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201212083040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE whatwedo_search_index (id BIGINT AUTO_INCREMENT NOT NULL, foreign_id VARCHAR(255) NOT NULL, model VARCHAR(150) NOT NULL, field VARCHAR(90) NOT NULL, content LONGTEXT NOT NULL, FULLTEXT INDEX IDX_38033FA6FEC530A9 (content), INDEX IDX_38033FA6D79572D9 (model), UNIQUE INDEX search_index (foreign_id, model, field), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sensor ADD type VARCHAR(255) DEFAULT NULL, ADD name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE whatwedo_search_index');
        $this->addSql('ALTER TABLE sensor DROP type, DROP name');
    }
}

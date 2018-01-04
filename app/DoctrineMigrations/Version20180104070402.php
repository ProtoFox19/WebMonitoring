<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180104070402 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sitemap_settings DROP INDEX IDX_32241BACFE18474D, ADD UNIQUE INDEX UNIQ_32241BACFE18474D (web_id)');
        $this->addSql('ALTER TABLE sitemap_settings CHANGE not_included_path not_included_path MEDIUMTEXT DEFAULT NULL, CHANGE notably_path notably_path MEDIUMTEXT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sitemap_settings DROP INDEX UNIQ_32241BACFE18474D, ADD INDEX IDX_32241BACFE18474D (web_id)');
        $this->addSql('ALTER TABLE sitemap_settings CHANGE not_included_path not_included_path VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE notably_path notably_path VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}

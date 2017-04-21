<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170421184250 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Repository DROP FOREIGN KEY FK_13A3541DD145533');
        $this->addSql('ALTER TABLE Repository CHANGE key_id key_id VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE GpgKey CHANGE id id VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE GpgKey CHANGE fingerprint fingerprint VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE Repository ADD CONSTRAINT FK_13A3541DD145533 FOREIGN KEY (key_id) REFERENCES GpgKey (id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Repository DROP FOREIGN KEY FK_13A3541DD145533');
        $this->addSql('ALTER TABLE GpgKey CHANGE id id VARCHAR(255) NOT NULL COLLATE utf8_general_ci');
        $this->addSql('ALTER TABLE GpgKey CHANGE fingerprint fingerprint LONGBLOB NOT NULL');
        $this->addSql('ALTER TABLE Repository CHANGE key_id key_id VARCHAR(255) DEFAULT NULL COLLATE utf8_general_ci');
        $this->addSql('ALTER TABLE Repository ADD CONSTRAINT FK_13A3541DD145533 FOREIGN KEY (key_id) REFERENCES GpgKey (id)');
    }
}

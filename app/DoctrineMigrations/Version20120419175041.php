<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20120419175041 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql');

        $this->addSql('CREATE TABLE Repository (id INT AUTO_INCREMENT NOT NULL, key_id VARCHAR(40) DEFAULT NULL, url VARCHAR(255) NOT NULL, repoType VARCHAR(255) NOT NULL, INDEX IDX_13A3541DD145533 (key_id), PRIMARY KEY(id)) ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Repository ADD CONSTRAINT FK_13A3541DD145533 FOREIGN KEY (key_id) REFERENCES GpgKey (id)');
        $this->addSql('ALTER TABLE GpgKey ADD fingerprint LONGBLOB NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql');

        $this->addSql('DROP TABLE Repository');
        $this->addSql('ALTER TABLE GpgKey DROP fingerprint');
    }
}

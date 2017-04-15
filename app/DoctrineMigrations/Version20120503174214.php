<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20120503174214 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql');

        $this->addSql("ALTER TABLE Groups ADD name VARCHAR(150) NOT NULL, ADD roles LONGTEXT NOT NULL COMMENT '(DC2Type:array)'");
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F7C13C465E237E06 ON Groups (name)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql');

        $this->addSql('DROP INDEX UNIQ_F7C13C465E237E06 ON Groups');
        $this->addSql('ALTER TABLE Groups DROP name, DROP roles');
    }
}

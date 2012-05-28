<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20120528234903 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        
        $this->addSql("ALTER TABLE Package ADD user_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE Package ADD CONSTRAINT FK_11D55E09A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)");
        $this->addSql("CREATE INDEX IDX_11D55E09A76ED395 ON Package (user_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        
        $this->addSql("ALTER TABLE Package DROP FOREIGN KEY FK_11D55E09A76ED395");
        $this->addSql("DROP INDEX IDX_11D55E09A76ED395 ON Package");
        $this->addSql("ALTER TABLE Package DROP user_id");
    }
}

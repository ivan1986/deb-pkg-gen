<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160812064519 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Package DROP FOREIGN KEY FK_11D55E09A76ED395');
        $this->addSql('DROP INDEX IDX_11D55E09A76ED395 ON Package');
        $this->addSql('ALTER TABLE Package DROP user_id, DROP checked, DROP link');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Package ADD user_id INT DEFAULT NULL, ADD checked INT DEFAULT NULL, ADD link VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_general_ci');
        $this->addSql('ALTER TABLE Package ADD CONSTRAINT FK_11D55E09A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_11D55E09A76ED395 ON Package (user_id)');
    }
}

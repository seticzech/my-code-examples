<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181018062922 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE proposition (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, project_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, proposition_id VARCHAR(40) NOT NULL, product_id VARCHAR(40) NOT NULL, article_elk_id VARCHAR(40) NOT NULL, article_gas_id VARCHAR(40) NOT NULL, duration INTEGER DEFAULT NULL, customer_type INTEGER NOT NULL, active_at BOOLEAN DEFAULT \'0\' NOT NULL, active_from DATE DEFAULT NULL, active_to DATE DEFAULT NULL, created_at DATETIME NOT NULL, modified_at DATETIME DEFAULT NULL, deleted BOOLEAN DEFAULT \'0\' NOT NULL)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE proposition');
    }
}

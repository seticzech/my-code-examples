<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20210531064915 extends AbstractMigration
{

    /**
      * Row level security table names
      *
      * @var array
      */
    protected $rlsPoliciesTableNames = [];


    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE bb_sys.tenants_hosts (host VARCHAR(64) NOT NULL, tenant_id UUID NOT NULL, PRIMARY KEY(tenant_id, host))');
        $this->addSql('CREATE INDEX idx_tenants_hosts_tenant_id ON bb_sys.tenants_hosts (tenant_id)');
        $this->addSql('COMMENT ON COLUMN bb_sys.tenants_hosts.tenant_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE bb_sys.tenants_hosts ADD CONSTRAINT fk_tenants_hosts_tenant_id FOREIGN KEY (tenant_id) REFERENCES bb_sys.tenants (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE bb_sys.tenants_hosts');
    }

}

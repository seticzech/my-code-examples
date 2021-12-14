<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200911090147 extends AbstractMigration
{

    /**
     * Row level security table names
     *
     * @var array
     */
    protected $rlsPoliciesTableNames = [];


    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE bb_erp.oauth_clients (id UUID NOT NULL, grant_type VARCHAR(20) NOT NULL, name VARCHAR(64) DEFAULT NULL, secret VARCHAR(128) NOT NULL, redirect_uris TEXT DEFAULT NULL, is_active BOOLEAN DEFAULT \'true\' NOT NULL, revoked BOOLEAN DEFAULT \'false\' NOT NULL, revoked_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, scopes TEXT DEFAULT NULL, tenant_id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN bb_erp.oauth_clients.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bb_erp.oauth_clients.revoked_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN bb_erp.oauth_clients.tenant_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bb_erp.oauth_clients.redirect_uris IS \'(DC2Type:oauth2_redirect_uri)\'');
        $this->addSql('COMMENT ON COLUMN bb_erp.oauth_clients.scopes IS \'(DC2Type:oauth2_scope)\'');
        $this->addSql('CREATE TABLE bb_erp.oauth_authorization_codes (id VARCHAR(80) NOT NULL, client_id UUID NOT NULL, user_id VARCHAR(128) DEFAULT NULL, revoked BOOLEAN DEFAULT \'false\' NOT NULL, revoked_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_oauth_authorization_codes_client_id ON bb_erp.oauth_authorization_codes (client_id)');
        $this->addSql('COMMENT ON COLUMN bb_erp.oauth_authorization_codes.client_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bb_erp.oauth_authorization_codes.revoked_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE bb_erp.oauth_access_tokens (id UUID NOT NULL, client_id UUID NOT NULL, user_identifier VARCHAR(128) DEFAULT NULL, expiry TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, revoked BOOLEAN DEFAULT \'false\' NOT NULL, revoked_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, scopes TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_oauth_access_tokens_client_id ON bb_erp.oauth_access_tokens (client_id)');
        $this->addSql('COMMENT ON COLUMN bb_erp.oauth_access_tokens.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bb_erp.oauth_access_tokens.client_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bb_erp.oauth_access_tokens.expiry IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN bb_erp.oauth_access_tokens.revoked_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN bb_erp.oauth_access_tokens.scopes IS \'(DC2Type:oauth2_scope)\'');
        $this->addSql('CREATE TABLE bb_erp.oauth_refresh_tokens (id UUID NOT NULL, access_token_id UUID NOT NULL, expiry TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, revoked BOOLEAN DEFAULT \'false\' NOT NULL, revoked_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_oauth_refresh_tokens_access_token_id ON bb_erp.oauth_refresh_tokens (access_token_id)');
        $this->addSql('COMMENT ON COLUMN bb_erp.oauth_refresh_tokens.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bb_erp.oauth_refresh_tokens.access_token_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bb_erp.oauth_refresh_tokens.expiry IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN bb_erp.oauth_refresh_tokens.revoked_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE bb_erp.oauth_authorization_codes ADD CONSTRAINT fk_oauth_authorization_codes_client_id FOREIGN KEY (client_id) REFERENCES bb_erp.oauth_clients (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bb_erp.oauth_access_tokens ADD CONSTRAINT fk_oauth_access_tokens_client_id FOREIGN KEY (client_id) REFERENCES bb_erp.oauth_clients (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bb_erp.oauth_refresh_tokens ADD CONSTRAINT fk_oauth_refresh_tokens_access_token_id FOREIGN KEY (access_token_id) REFERENCES bb_erp.oauth_access_tokens (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE bb_erp.oauth_authorization_codes DROP CONSTRAINT fk_oauth_authorization_codes_client_id');
        $this->addSql('ALTER TABLE bb_erp.oauth_access_tokens DROP CONSTRAINT fk_oauth_access_tokens_client_id');
        $this->addSql('ALTER TABLE bb_erp.oauth_refresh_tokens DROP CONSTRAINT fk_oauth_refresh_tokens_access_token_id');
        $this->addSql('DROP TABLE bb_erp.oauth_clients');
        $this->addSql('DROP TABLE bb_erp.oauth_authorization_codes');
        $this->addSql('DROP TABLE bb_erp.oauth_access_tokens');
        $this->addSql('DROP TABLE bb_erp.oauth_refresh_tokens');
    }

}

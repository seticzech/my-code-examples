<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200911082702 extends AbstractMigration
{

    /**
     * Row level security table names
     *
     * @var array
     */
    protected $rlsPoliciesTableNames = [
        'core_companies',
        'core_languages_to_tenants',
        'core_modules_to_tenants',
        'core_translations_custom',
        'core_users',
    ];


    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE bb_erp.core_modules (id UUID NOT NULL, name VARCHAR(64) NOT NULL, code VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN bb_erp.core_modules.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE bb_erp.core_navigations (id UUID NOT NULL, module_id UUID NOT NULL, parent_id UUID DEFAULT NULL, code VARCHAR(128) NOT NULL, icon VARCHAR(64) DEFAULT NULL, is_active BOOLEAN DEFAULT \'true\' NOT NULL, sort_order SMALLINT DEFAULT 1 NOT NULL, url VARCHAR(128) DEFAULT \'/\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_core_navigations_module_id ON bb_erp.core_navigations (module_id)');
        $this->addSql('CREATE INDEX idx_core_navigations_parent_id ON bb_erp.core_navigations (parent_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_core_navigations_module_id_parent_id_code ON bb_erp.core_navigations (COALESCE(module_id, \'00000000-0000-0000-0000-000000000000\'::uuid), COALESCE(parent_id, \'00000000-0000-0000-0000-000000000000\'::uuid), code)');
        $this->addSql('COMMENT ON COLUMN bb_erp.core_navigations.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bb_erp.core_navigations.module_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bb_erp.core_navigations.parent_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE bb_erp.core_users (id UUID NOT NULL, company_id UUID DEFAULT NULL, email VARCHAR(64) NOT NULL, first_name VARCHAR(48) DEFAULT NULL, last_name VARCHAR(48) DEFAULT NULL, is_client BOOLEAN DEFAULT \'false\' NOT NULL, is_company BOOLEAN DEFAULT \'false\' NOT NULL, is_host BOOLEAN DEFAULT \'false\' NOT NULL, password VARCHAR(255) NOT NULL, phone VARCHAR(16) DEFAULT NULL, user_code VARCHAR(64) DEFAULT NULL, approved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_approved BOOLEAN DEFAULT \'false\' NOT NULL, is_rejected BOOLEAN DEFAULT \'false\' NOT NULL, rejected_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, reject_reason VARCHAR(256) DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, tenant_id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_core_users_company_id ON bb_erp.core_users (company_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_core_users_email_tenant_id ON bb_erp.core_users (email, tenant_id)');
        $this->addSql('COMMENT ON COLUMN bb_erp.core_users.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bb_erp.core_users.company_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bb_erp.core_users.tenant_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE bb_erp.core_translations (id UUID NOT NULL, language_id UUID NOT NULL, module_id UUID DEFAULT NULL, context VARCHAR(128) DEFAULT NULL, code VARCHAR(128) NOT NULL, description VARCHAR(256) DEFAULT NULL, message TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_core_translations_language_id ON bb_erp.core_translations (language_id)');
        $this->addSql('CREATE INDEX idx_core_translations_module_id ON bb_erp.core_translations (module_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_core_translations_language_id_module_id_context_code ON bb_erp.core_translations (language_id, COALESCE(module_id, \'00000000-0000-0000-0000-000000000000\'::uuid), COALESCE(context, \'\'::character varying), code)');
        $this->addSql('COMMENT ON COLUMN bb_erp.core_translations.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bb_erp.core_translations.language_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bb_erp.core_translations.module_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE bb_erp.core_companies (id UUID NOT NULL, name VARCHAR(64) NOT NULL, tenant_id UUID NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN bb_erp.core_companies.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bb_erp.core_companies.tenant_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE bb_erp.core_languages_to_tenants (tenant_id UUID NOT NULL, language_id UUID NOT NULL, is_default BOOLEAN DEFAULT \'false\' NOT NULL, is_active BOOLEAN DEFAULT \'false\' NOT NULL, sort_order SMALLINT DEFAULT 1 NOT NULL, PRIMARY KEY(language_id, tenant_id))');
        $this->addSql('CREATE INDEX idx_core_languages_to_tenants_language_id ON bb_erp.core_languages_to_tenants (language_id)');
        $this->addSql('COMMENT ON COLUMN bb_erp.core_languages_to_tenants.tenant_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bb_erp.core_languages_to_tenants.language_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE bb_erp.core_modules_to_tenants (tenant_id UUID NOT NULL, module_id UUID NOT NULL, PRIMARY KEY(module_id, tenant_id))');
        $this->addSql('CREATE INDEX idx_core_modules_to_tenants_module_id ON bb_erp.core_modules_to_tenants (module_id)');
        $this->addSql('COMMENT ON COLUMN bb_erp.core_modules_to_tenants.tenant_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bb_erp.core_modules_to_tenants.module_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE bb_erp.mlm_icons (id UUID NOT NULL, icon_fa VARCHAR(64) DEFAULT NULL, icon_svg TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN bb_erp.mlm_icons.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE bb_erp.core_translations_custom (tenant_id UUID NOT NULL, translation_id UUID NOT NULL, message TEXT NOT NULL, PRIMARY KEY(translation_id, tenant_id))');
        $this->addSql('CREATE INDEX idx_core_translations_custom_translation_id ON bb_erp.core_translations_custom (translation_id)');
        $this->addSql('COMMENT ON COLUMN bb_erp.core_translations_custom.tenant_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bb_erp.core_translations_custom.translation_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE bb_erp.core_languages (id UUID NOT NULL, name VARCHAR(64) NOT NULL, native_name VARCHAR(48) NOT NULL, adverb VARCHAR(64) NOT NULL, iso_639_1 VARCHAR(2) NOT NULL, iso_639_2_b VARCHAR(3) DEFAULT NULL, locale VARCHAR(24) NOT NULL, direction VARCHAR(10) DEFAULT \'ltr\' NOT NULL, is_active BOOLEAN DEFAULT \'false\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN bb_erp.core_languages.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE bb_erp.core_navigations ADD CONSTRAINT fk_core_navigations_module_id FOREIGN KEY (module_id) REFERENCES bb_erp.core_modules (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bb_erp.core_navigations ADD CONSTRAINT fk_core_navigations_parent_id FOREIGN KEY (parent_id) REFERENCES bb_erp.core_navigations (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bb_erp.core_users ADD CONSTRAINT fk_core_users_company_id FOREIGN KEY (company_id) REFERENCES bb_erp.core_companies (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bb_erp.core_translations ADD CONSTRAINT fk_core_translations_language_id FOREIGN KEY (language_id) REFERENCES bb_erp.core_languages (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bb_erp.core_translations ADD CONSTRAINT fk_core_translations_module_id FOREIGN KEY (module_id) REFERENCES bb_erp.core_modules (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bb_erp.core_languages_to_tenants ADD CONSTRAINT fk_core_languages_to_tenants_language_id FOREIGN KEY (language_id) REFERENCES bb_erp.core_languages (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bb_erp.core_modules_to_tenants ADD CONSTRAINT fk_core_modules_to_tenants_module_id FOREIGN KEY (module_id) REFERENCES bb_erp.core_modules (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bb_erp.core_translations_custom ADD CONSTRAINT fk_core_translations_custom_translation_id FOREIGN KEY (translation_id) REFERENCES bb_erp.core_translations (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE bb_erp.core_navigations DROP CONSTRAINT fk_core_navigations_module_id');
        $this->addSql('ALTER TABLE bb_erp.core_translations DROP CONSTRAINT fk_core_translations_module_id');
        $this->addSql('ALTER TABLE bb_erp.core_modules_to_tenants DROP CONSTRAINT fk_core_modules_to_tenants_module_id');
        $this->addSql('ALTER TABLE bb_erp.core_navigations DROP CONSTRAINT fk_core_navigations_parent_id');
        $this->addSql('ALTER TABLE bb_erp.core_translations_custom DROP CONSTRAINT fk_core_translations_custom_translation_id');
        $this->addSql('ALTER TABLE bb_erp.core_users DROP CONSTRAINT fk_core_users_company_id');
        $this->addSql('ALTER TABLE bb_erp.core_translations DROP CONSTRAINT fk_core_translations_language_id');
        $this->addSql('ALTER TABLE bb_erp.core_languages_to_tenants DROP CONSTRAINT fk_core_languages_to_tenants_language_id');
        $this->addSql('DROP TABLE bb_erp.core_modules');
        $this->addSql('DROP TABLE bb_erp.core_navigations');
        $this->addSql('DROP TABLE bb_erp.core_users');
        $this->addSql('DROP TABLE bb_erp.core_translations');
        $this->addSql('DROP TABLE bb_erp.core_companies');
        $this->addSql('DROP TABLE bb_erp.core_languages_to_tenants');
        $this->addSql('DROP TABLE bb_erp.core_modules_to_tenants');
        $this->addSql('DROP TABLE bb_erp.mlm_icons');
        $this->addSql('DROP TABLE bb_erp.core_translations_custom');
        $this->addSql('DROP TABLE bb_erp.core_languages');
    }

}

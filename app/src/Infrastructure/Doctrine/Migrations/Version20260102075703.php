<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260102075703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE events (id BIGINT AUTO_INCREMENT NOT NULL, event_type VARCHAR(50) NOT NULL, status_code VARCHAR(20) NOT NULL, latency_ms INT DEFAULT NULL, metadata JSON DEFAULT NULL, created_at DATETIME NOT NULL, user_id BINARY(16) DEFAULT NULL, INDEX IDX_5387574AA76ED395 (user_id), INDEX idx_events_created_at (created_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE ingredient_registry (id BIGINT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, visibility VARCHAR(20) DEFAULT \'global\' NOT NULL, is_verified TINYINT DEFAULT 0 NOT NULL, calories_per100g NUMERIC(8, 2) DEFAULT NULL, external_api_id VARCHAR(255) DEFAULT NULL, created_by_user_id BINARY(16) DEFAULT NULL, UNIQUE INDEX UNIQ_BFAE4D845E237E06 (name), INDEX IDX_BFAE4D847D182D95 (created_by_user_id), INDEX idx_ingredient_name (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE recipe_ingredients (id BIGINT AUTO_INCREMENT NOT NULL, original_text VARCHAR(255) NOT NULL, quantity NUMERIC(12, 4) DEFAULT NULL, original_quantity NUMERIC(12, 4) DEFAULT NULL, is_parsed TINYINT DEFAULT 0 NOT NULL, is_scalable TINYINT DEFAULT 1 NOT NULL, recipe_version_id BINARY(16) NOT NULL, ingredient_id BIGINT DEFAULT NULL, unit_id VARCHAR(50) DEFAULT NULL, substitution_for_ingredient_id BIGINT DEFAULT NULL, INDEX IDX_9F925F2B933FE08C (ingredient_id), INDEX IDX_9F925F2BF8BD700D (unit_id), INDEX IDX_9F925F2BEE379F3A (substitution_for_ingredient_id), INDEX idx_ingredients_version (recipe_version_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE recipe_versions (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, servings INT NOT NULL, steps JSON NOT NULL, total_calories INT DEFAULT NULL, calories_per_serving INT DEFAULT NULL, calorie_confidence VARCHAR(20) DEFAULT NULL, tailoring_type VARCHAR(30) NOT NULL, ai_prompt_version VARCHAR(50) DEFAULT NULL, recipe_id BINARY(16) NOT NULL, parent_version_id BINARY(16) DEFAULT NULL, INDEX IDX_6F4CE54C59D8A214 (recipe_id), INDEX IDX_6F4CE54CCFFA355 (parent_version_id), INDEX idx_versions_recipe_created (recipe_id, created_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE recipes (id BINARY(16) NOT NULL, title VARCHAR(255) NOT NULL, is_favorite TINYINT DEFAULT 0 NOT NULL, search_text LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, servings INT NOT NULL, total_calories INT DEFAULT NULL, calories_per_serving INT DEFAULT NULL, calorie_confidence VARCHAR(20) DEFAULT NULL, user_id BINARY(16) NOT NULL, active_version_id BINARY(16) DEFAULT NULL, INDEX IDX_A369E2B5A76ED395 (user_id), UNIQUE INDEX UNIQ_A369E2B56A1E45F3 (active_version_id), INDEX idx_recipes_user_updated (user_id, updated_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE tailoring_quotas (weekly_attempts_count INT DEFAULT 0 NOT NULL, last_reset_at DATETIME NOT NULL, user_id BINARY(16) NOT NULL, PRIMARY KEY (user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE unit_conversions (factor NUMERIC(12, 6) NOT NULL, from_unit_id VARCHAR(50) NOT NULL, to_unit_id VARCHAR(50) NOT NULL, INDEX IDX_F165B1837EE393A2 (from_unit_id), INDEX IDX_F165B18376254DF8 (to_unit_id), PRIMARY KEY (from_unit_id, to_unit_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE unit_registry (id VARCHAR(50) NOT NULL, abbreviation VARCHAR(20) DEFAULT NULL, is_scalable TINYINT DEFAULT 1 NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user_preferences (target_calories_per_serving INT DEFAULT NULL, unit_system VARCHAR(10) DEFAULT \'metric\' NOT NULL, user_id BINARY(16) NOT NULL, PRIMARY KEY (user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE users (id BINARY(16) NOT NULL, email VARCHAR(255) NOT NULL, google_id VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), UNIQUE INDEX UNIQ_1483A5E976F5C865 (google_id), INDEX idx_users_email (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user_avoid_list (user_id BINARY(16) NOT NULL, ingredient_id BIGINT NOT NULL, INDEX IDX_A23EF2F8A76ED395 (user_id), INDEX IDX_A23EF2F8933FE08C (ingredient_id), PRIMARY KEY (user_id, ingredient_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ingredient_registry ADD CONSTRAINT FK_BFAE4D847D182D95 FOREIGN KEY (created_by_user_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE recipe_ingredients ADD CONSTRAINT FK_9F925F2B2C2A166 FOREIGN KEY (recipe_version_id) REFERENCES recipe_versions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipe_ingredients ADD CONSTRAINT FK_9F925F2B933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient_registry (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE recipe_ingredients ADD CONSTRAINT FK_9F925F2BF8BD700D FOREIGN KEY (unit_id) REFERENCES unit_registry (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE recipe_ingredients ADD CONSTRAINT FK_9F925F2BEE379F3A FOREIGN KEY (substitution_for_ingredient_id) REFERENCES ingredient_registry (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE recipe_versions ADD CONSTRAINT FK_6F4CE54C59D8A214 FOREIGN KEY (recipe_id) REFERENCES recipes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipe_versions ADD CONSTRAINT FK_6F4CE54CCFFA355 FOREIGN KEY (parent_version_id) REFERENCES recipe_versions (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE recipes ADD CONSTRAINT FK_A369E2B5A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipes ADD CONSTRAINT FK_A369E2B56A1E45F3 FOREIGN KEY (active_version_id) REFERENCES recipe_versions (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tailoring_quotas ADD CONSTRAINT FK_94988855A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE unit_conversions ADD CONSTRAINT FK_F165B1837EE393A2 FOREIGN KEY (from_unit_id) REFERENCES unit_registry (id)');
        $this->addSql('ALTER TABLE unit_conversions ADD CONSTRAINT FK_F165B18376254DF8 FOREIGN KEY (to_unit_id) REFERENCES unit_registry (id)');
        $this->addSql('ALTER TABLE user_preferences ADD CONSTRAINT FK_402A6F60A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_avoid_list ADD CONSTRAINT FK_A23EF2F8A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_avoid_list ADD CONSTRAINT FK_A23EF2F8933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient_registry (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574AA76ED395');
        $this->addSql('ALTER TABLE ingredient_registry DROP FOREIGN KEY FK_BFAE4D847D182D95');
        $this->addSql('ALTER TABLE recipe_ingredients DROP FOREIGN KEY FK_9F925F2B2C2A166');
        $this->addSql('ALTER TABLE recipe_ingredients DROP FOREIGN KEY FK_9F925F2B933FE08C');
        $this->addSql('ALTER TABLE recipe_ingredients DROP FOREIGN KEY FK_9F925F2BF8BD700D');
        $this->addSql('ALTER TABLE recipe_ingredients DROP FOREIGN KEY FK_9F925F2BEE379F3A');
        $this->addSql('ALTER TABLE recipe_versions DROP FOREIGN KEY FK_6F4CE54C59D8A214');
        $this->addSql('ALTER TABLE recipe_versions DROP FOREIGN KEY FK_6F4CE54CCFFA355');
        $this->addSql('ALTER TABLE recipes DROP FOREIGN KEY FK_A369E2B5A76ED395');
        $this->addSql('ALTER TABLE recipes DROP FOREIGN KEY FK_A369E2B56A1E45F3');
        $this->addSql('ALTER TABLE tailoring_quotas DROP FOREIGN KEY FK_94988855A76ED395');
        $this->addSql('ALTER TABLE unit_conversions DROP FOREIGN KEY FK_F165B1837EE393A2');
        $this->addSql('ALTER TABLE unit_conversions DROP FOREIGN KEY FK_F165B18376254DF8');
        $this->addSql('ALTER TABLE user_preferences DROP FOREIGN KEY FK_402A6F60A76ED395');
        $this->addSql('ALTER TABLE user_avoid_list DROP FOREIGN KEY FK_A23EF2F8A76ED395');
        $this->addSql('ALTER TABLE user_avoid_list DROP FOREIGN KEY FK_A23EF2F8933FE08C');
        $this->addSql('DROP TABLE events');
        $this->addSql('DROP TABLE ingredient_registry');
        $this->addSql('DROP TABLE recipe_ingredients');
        $this->addSql('DROP TABLE recipe_versions');
        $this->addSql('DROP TABLE recipes');
        $this->addSql('DROP TABLE tailoring_quotas');
        $this->addSql('DROP TABLE unit_conversions');
        $this->addSql('DROP TABLE unit_registry');
        $this->addSql('DROP TABLE user_preferences');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE user_avoid_list');
    }
}

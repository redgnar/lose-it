### 1. Tables and Columns

#### Users
- `id`: `UUID` (Primary Key)
- `email`: `VARCHAR(255)` (Unique, Not Null)
- `google_id`: `VARCHAR(255)` (Unique, Not Null) - For Google OAuth
- `created_at`: `DATETIME` (Not Null, Default: CURRENT_TIMESTAMP)
- `deleted_at`: `DATETIME` (Nullable) - For soft delete/account deletion process

#### UserPreferences
- `user_id`: `UUID` (Primary Key, Foreign Key to `Users.id`, ON DELETE CASCADE)
- `target_calories_per_serving`: `INT` (Nullable)
- `unit_system`: `ENUM('metric', 'us')` (Not Null, Default: 'metric')
- `is_profile_complete`: `BOOLEAN` (Generated: `target_calories_per_serving IS NOT NULL OR EXISTS (avoid_list)`) - Managed at App Layer

#### IngredientRegistry
- `id`: `BIGINT` (Primary Key, Auto-increment)
- `name`: `VARCHAR(255)` (Unique, Not Null)
- `visibility`: `ENUM('global', 'private')` (Not Null, Default: 'global')
- `is_verified`: `BOOLEAN` (Not Null, Default: false)
- `calories_per_100g`: `DECIMAL(8, 2)` (Nullable)
- `external_api_id`: `VARCHAR(255)` (Nullable) - Reference to USDA/Edamam
- `created_by_user_id`: `UUID` (Nullable, Foreign Key to `Users.id`, ON DELETE SET NULL)

#### UserAvoidList (Junction Table)
- `user_id`: `UUID` (Primary Key, Foreign Key to `Users.id`, ON DELETE CASCADE)
- `ingredient_id`: `BIGINT` (Primary Key, Foreign Key to `IngredientRegistry.id`, ON DELETE CASCADE)

#### UnitRegistry
- `id`: `VARCHAR(50)` (Primary Key) - e.g., 'gram', 'cup', 'tablespoon'
- `abbreviation`: `VARCHAR(20)` (Nullable)
- `is_scalable`: `BOOLEAN` (Not Null, Default: true) - False for "salt to taste"

#### UnitConversions
- `from_unit_id`: `VARCHAR(50)` (Primary Key, Foreign Key to `UnitRegistry.id`)
- `to_unit_id`: `VARCHAR(50)` (Primary Key, Foreign Key to `UnitRegistry.id`)
- `factor`: `DECIMAL(12, 6)` (Not Null)

#### Recipes
- `id`: `UUID` (Primary Key)
- `user_id`: `UUID` (Not Null, Foreign Key to `Users.id`, ON DELETE CASCADE)
- `active_version_id`: `UUID` (Nullable) - Points to the current `RecipeVersion`
- `title`: `VARCHAR(255)` (Not Null)
- `is_favorite`: `BOOLEAN` (Not Null, Default: false)
- `search_text`: `TEXT` (Not Null) - Denormalized for FULLTEXT search (title + ingredients)
- `created_at`: `DATETIME` (Not Null)
- `updated_at`: `DATETIME` (Not Null)
- 
- -- Redundant fields from active_version_id for P95 read performance
- `servings`: `INT` (Not Null)
- `total_calories`: `INT` (Nullable)
- `calories_per_serving`: `INT` (Nullable)
- `calorie_confidence`: `ENUM('low', 'medium', 'high')` (Nullable)

#### RecipeVersions
- `id`: `UUID` (Primary Key)
- `recipe_id`: `UUID` (Not Null, Foreign Key to `Recipes.id`, ON DELETE CASCADE)
- `parent_version_id`: `UUID` (Nullable, Foreign Key to `RecipeVersions.id`) - For branching/undo
- `created_at`: `DATETIME` (Not Null)
- `servings`: `INT` (Not Null)
- `steps`: `JSON` (Not Null, CHECK (JSON_VALID(`steps`)))
- `total_calories`: `INT` (Nullable)
- `calories_per_serving`: `INT` (Nullable)
- `calorie_confidence`: `ENUM('low', 'medium', 'high')` (Nullable)
- `tailoring_type`: `ENUM('original', 'scale', 'calorie_reduction')` (Not Null)
- `ai_prompt_version`: `VARCHAR(50)` (Nullable)

#### RecipeIngredients
- `id`: `BIGINT` (Primary Key, Auto-increment)
- `recipe_version_id`: `UUID` (Not Null, Foreign Key to `RecipeVersions.id`, ON DELETE CASCADE)
- `ingredient_id`: `BIGINT` (Nullable, Foreign Key to `IngredientRegistry.id`)
- `original_text`: `VARCHAR(255)` (Not Null) - Raw input line
- `quantity`: `DECIMAL(12, 4)` (Nullable)
- `unit_id`: `VARCHAR(50)` (Nullable, Foreign Key to `UnitRegistry.id`)
- `original_quantity`: `DECIMAL(12, 4)` (Nullable) - To avoid destructive rounding
- `is_parsed`: `BOOLEAN` (Not Null, Default: false)
- `is_scalable`: `BOOLEAN` (Not Null, Default: true)
- `substitution_for_ingredient_id`: `BIGINT` (Nullable, Foreign Key to `IngredientRegistry.id`) - Tracking AI swaps

#### TailoringQuotas
- `user_id`: `UUID` (Primary Key, Foreign Key to `Users.id`, ON DELETE CASCADE)
- `weekly_attempts_count`: `INT` (Not Null, Default: 0)
- `last_reset_at`: `DATETIME` (Not Null)

#### Events (Partitioned)
- `id`: `BIGINT` (Primary Key, Auto-increment)
- `user_id`: `UUID` (Nullable, Foreign Key to `Users.id`, ON DELETE SET NULL) - Anonymized on user delete
- `event_type`: `VARCHAR(50)` (Not Null)
- `status_code`: `VARCHAR(20)` (Not Null)
- `latency_ms`: `INT` (Nullable)
- `metadata`: `JSON` (Nullable)
- `created_at`: `DATETIME` (Not Null)

---

### 2. Relationships

| Entity 1 | Cardinality | Entity 2 | Notes |
| :--- | :---: | :--- | :--- |
| `Users` | 1:1 | `UserPreferences` | User profile data |
| `Users` | 1:1 | `TailoringQuotas` | Usage tracking |
| `Users` | 1:N | `Recipes` | Ownership |
| `Users` | M:N | `IngredientRegistry` | via `UserAvoidList` |
| `Recipes` | 1:N | `RecipeVersions` | History of recipe states |
| `RecipeVersions` | 1:N | `RecipeIngredients` | Line-items for a version |
| `RecipeVersions` | 1:1 | `RecipeVersions` | Parent-child via `parent_version_id` |
| `IngredientRegistry`| 1:N | `RecipeIngredients` | Normalized link |
| `UnitRegistry` | 1:N | `RecipeIngredients` | Unit normalization |
| `UnitRegistry` | M:N | `UnitRegistry` | via `UnitConversions` |

---

### 3. Indexes

- `Recipes`: `FULLTEXT INDEX (search_text)` - For title and ingredient search.
- `Recipes`: `INDEX (user_id, updated_at)` - For dashboard listing.
- `RecipeVersions`: `INDEX (recipe_id, created_at)` - For version history navigation.
- `RecipeIngredients`: `INDEX (recipe_version_id)` - For fast loading of recipe content.
- `IngredientRegistry`: `INDEX (name)` - For quick lookup during parsing.
- `Events`: `INDEX (created_at)` - Supporting range-based partitioning and cleanup.
- `Users`: `INDEX (email)` - Login lookup.

---

### 4. Design Decisions & Notes

1.  **Redundant Recipe Pattern**: The `Recipes` table stores snapshots of the `active_version_id`'s metrics (servings, calories). This allows the application to render the "My Recipes" list and individual recipe views without joining `RecipeVersions` or aggregating `RecipeIngredients` in most cases.
2.  **Immutability**: `RecipeVersions` and `RecipeIngredients` are immutable. Any "edit" or "tailoring" creates a new `RecipeVersion`. This supports the **Undo** functionality by simply updating the `active_version_id` in the `Recipes` table to point back to the `parent_version_id`.
3.  **MariaDB JSON**: `RecipeVersions.steps` uses the `JSON` type with `JSON_VALID()` constraint. This allows storing an array of step objects (e.g., `[{ "order": 1, "text": "Boil water", "timer_minutes": 5 }]`) while ensuring structural integrity.
4.  **Partitioning**: The `Events` table is designed to be partitioned by `RANGE` on `created_at` (monthly). This allows for instant drops of old data (90-day retention) without incurring massive DELETE performance penalties.
5.  **Atomic Quotas**: Quota enforcement is handled via SQL:
    ```sql
    UPDATE TailoringQuotas 
    SET weekly_attempts_count = IF(last_reset_at < DATE_SUB(NOW(), INTERVAL 7 DAY), 1, weekly_attempts_count + 1),
        last_reset_at = IF(last_reset_at < DATE_SUB(NOW(), INTERVAL 7 DAY), NOW(), last_reset_at)
    WHERE user_id = ? AND (weekly_attempts_count < 50 OR last_reset_at < DATE_SUB(NOW(), INTERVAL 7 DAY));
    ```
6.  **Precision Math**: `DECIMAL(12, 4)` is used for all quantities to ensure that scaling (multiplication/division) doesn't introduce floating-point errors.
7.  **Search Index**: The `search_text` column in `Recipes` is updated synchronously by the application whenever a new version is set as active, combining the title and all ingredient names into a single searchable blob.
8.  **Row-Level Security**: Since MariaDB lacks native RLS (unlike PostgreSQL), isolation is enforced by the Application Layer using **Doctrine Filters** to automatically append `WHERE user_id = :current_user` to all queries.
9.  **External IDs**: `IngredientRegistry` includes `external_api_id` to link with nutritional databases like USDA, facilitating future expansions for automated calorie estimation updates.

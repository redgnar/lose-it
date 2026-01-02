# REST API Plan

## 1. Resources
- **User / Profile**: Corresponds to `Users`, `UserPreferences`, and `UserAvoidList` tables. Manages identity and dietary preferences.
- **Recipe**: Corresponds to the `Recipes` table. The top-level container for recipe metadata.
- **Recipe Version**: Corresponds to `RecipeVersions` and `RecipeIngredients` tables. Represents a specific state (original or tailored) of a recipe.
- **Ingredient Registry**: Corresponds to `IngredientRegistry`. A global/private catalog of ingredients for parsing and calorie data.
- **Unit Registry**: Corresponds to `UnitRegistry`. A lookup table for unit normalization and scaling rules.
- **Tailoring Job**: A virtual resource for the two-step tailoring process (Scale & Reduce).

## 2. Endpoints

### 2.1. Profile Management
#### `GET /api/profile/me`
- **Description**: Retrieve current user's profile and preferences.
- **Response Payload**:
  ```json
  {
    "id": "uuid",
    "email": "user@example.com",
    "preferences": {
      "target_calories_per_serving": 500,
      "unit_system": "metric",
      "avoid_list": [
        {"id": 1, "name": "Peanuts"}
      ],
      "is_profile_complete": true
    },
    "quota": {
      "weekly_attempts_count": 5,
      "limit": 50,
      "remaining": 45,
      "reset_at": "2026-01-09T09:15:00Z"
    }
  }
  ```
- **Success**: `200 OK`

#### `PATCH /api/profile/me/preferences`
- **Description**: Update user dietary preferences and unit system.
- **Request Payload**:
  ```json
  {
    "target_calories_per_serving": 600,
    "unit_system": "us",
    "avoid_list": [
      {"id": 1, "name": "Peanuts"}
    ],
    "is_profile_complete": true
  }
  ```
- **Success**: `200 OK`

#### `DELETE /api/profile/me`
- **Description**: Permanent account deletion. Triggers cascading delete of all user data.
- **Success**: `204 No Content`

---

### 2.2. Recipe CRUD
#### `GET /api/recipes`
- **Description**: List recipes with pagination, filtering, and search.
- **Query Parameters**:
  - `q`: Search string (title or ingredients)
  - `favorites`: `true|false`
  - `page`: default 1
  - `limit`: default 20
- **Response Payload**:
  ```json
  {
    "items": [
      {
        "id": "uuid",
        "title": "Spaghetti Carbonara",
        "is_favorite": true,
        "servings": 4,
        "calories_per_serving": 450,
        "updated_at": "2026-01-02T10:00:00Z"
      }
    ],
    "meta": { "total_count": 1, "total_pages": 1 }
  }
  ```

#### `POST /api/recipes`
- **Description**: Create a new recipe.
- **Request Payload**:
  ```json
  {
    "title": "New Recipe",
    "raw_ingredients": "200g Pasta\n2 Eggs",
    "raw_steps": "1. Boil pasta\n2. Mix eggs",
    "servings": 2
  }
  ```
- **Success**: `201 Created`

#### `GET /api/recipes/{id}`
- **Description**: Get full recipe details including the active version's ingredients and steps.
- **Response Payload**:
  ```json
  {
    "id": "uuid",
    "title": "Spaghetti Carbonara",
    "is_favorite": true,
    "version_id": "uuid",
    "servings": 4,
    "ingredients": [
      { "original_text": "200g Pasta", "quantity": 200, "unit": "gram", "item": "Pasta", "is_parsed": true },
      { "original_text": "2 Eggs", "quantity": 2, "unit": "piece", "item": "Egg", "is_parsed": true }
    ],
    "steps": ["Boil water", "Cook pasta for 10 mins"],
    "total_calories": 1800,
    "calories_per_serving": 450,
    "calorie_confidence": "high"
  }
  ```

#### `PATCH /api/recipes/{id}`
- **Description**: Update recipe metadata (e.g., favorite status).
- **Request Payload**:
  ```json
  {
    "is_favorite": true,
    "title": "New Title"
  }
  ```
- **Success**: `200 OK`

---

### 2.3. Recipe Tailoring & Parsing
#### `POST /api/recipes/{id}/parse`
- **Description**: (Re)parse the ingredients of the active version. Used for the "Guided Correction" UI.
- **Request Payload**:
  ```json
  {
    "ingredients": [
      { "id": 123, "original_text": "200g modified pasta" }
    ]
  }
  ```
- **Response Payload**:
  ```json
  {
    "success_rate": 0.85,
    "ingredients": [
      { "id": 123, "original_text": "200g modified pasta", "is_parsed": true }
    ]
  }
  ```
- **Error**: `422 Unprocessable Entity` if parse gate (80%) fails.

#### `POST /api/recipes/{id}/tailor`
- **Description**: Execute the two-step tailoring process.
- **Request Payload**:
  ```json
  {
    "target_servings": 2,
    "aggressiveness": "medium",
    "keep_similar": true,
    "save_strategy": "overwrite"
  }
  ```
- **Response Payload**:
  ```json
  {
    "scaled_version": { "id": "uuid", "servings": 2, "ingredients": [] },
    "final_version": { "id": "uuid", "servings": 2, "ingredients": [] },
    "status": "success",
    "message": "Target not achievable; closest estimate shown"
  }
  ```
- **Success Codes**: `200 OK`

---

### 2.4. Versioning & Undo
#### `GET /api/recipes/{id}/versions`
- **Description**: Get version history for a recipe.
- **Success**: `200 OK`

#### `POST /api/recipes/{id}/undo`
- **Description**: Revert `active_version_id` to the `parent_version_id` of the current active version.
- **Success**: `200 OK`

---

### 2.5. Registries
#### `GET /api/units`
- **Description**: List available units and their abbreviations.
#### `GET /api/ingredients/search?q=...`
- **Description**: Search the `IngredientRegistry` for matching items.

---

## 3. Authentication and Authorization
- **Mechanism**: Google OAuth 2.0.
- **Implementation**: Handled via Symfony Security and `knpuniversity/oauth2-client-bundle`.
- **Session**: Stateful sessions using standard Symfony cookies.
- **Authorization**: Ownership is enforced at the Application Layer (Doctrine Filters). Users can only access/modify resources where `user_id` matches their own.

---

## 4. Validation and Business Logic
### Validation Conditions
- **Recipe Creation**: Both Ingredients and Steps sections must not be empty.
- **Parse Gate**: ≥ 80% success and ≤ 2 unknown units required for tailoring.
- **Servings**: Must be an integer between 1 and 12.
- **Aggressiveness**: Must be one of `low`, `medium`, `high`.
- **Unit Registry**: Units must exist in `UnitRegistry` for scaling to be deterministic.

### Business Logic Implementation
- **Scale First**: Scaling is deterministic (PHP). Numeric values in steps are updated via regex matching ingredient quantities.
- **AI Reduction**: AI (Openrouter) performs swaps based on `aggressiveness` and `avoid_list`.
- **Keep-it-similar**: AI is prompted to preserve core protein and cooking methods.
- **Quota**: Checked via atomic SQL update on `TailoringQuotas` before AI calls.
- **Latency Handling**: If AI takes > 8s, the system returns the scaled version with "Calories unavailable".
- **Cascading Delete**: Deleting a user removes `UserPreferences`, `TailoringQuotas`, `Recipes`, `RecipeVersions`, `RecipeIngredients`, and `UserAvoidList` entries.

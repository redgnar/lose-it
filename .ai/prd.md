# Product Requirements Document (PRD) - Lose It!

## 1. Product Overview
Lose It! is an MVP application designed for individuals seeking to lose weight by simplifying the process of adapting cooking recipes to their specific dietary needs and calorie goals. The application leverages AI to modify user-provided recipes, focusing on two primary functions: deterministic portion scaling and AI-driven calorie reduction. The goal is to provide a fast, reliable way to tailor recipes while preserving the core flavor and cooking methods of the original dish.

## 2. User Problem
Weight-conscious individuals often find recipes online that look appealing but do not match their portion requirements or calorie targets. Manually calculating ingredient substitutions to reduce calories while maintaining the dish's identity is difficult and time-consuming. Existing tools often require manual entry of every ingredient and do not provide an automated way to "healthify" a recipe while keeping it similar to the original.

## 3. Functional Requirements

### 3.1. Authentication and Security
- Sign-in: Access is restricted to users authenticated via Google OAuth.
- Identity: Each user's recipes and profile are isolated and associated with their unique ID.
- Account Deletion: Users can delete their account, which triggers a cascading deletion of all associated data.

### 3.2. User Profile Management
- Preferences: Users can store an avoid list (items to exclude) and a target calorie per serving preference.
- Localization: Users can toggle between Metric and US unit systems.
- Profile Completion: A profile is considered complete if the user has provided at least one avoid item OR set a target calorie goal.

### 3.3. Recipe Management (CRUD)
- Input: Users create recipes by pasting text into a template with Ingredients and Steps sections.
- Auto-format: A one-click utility that validates the structure and formats the text before parsing.
- Browsing: Users can view recent recipes, mark recipes as favorites, and search by title or keyword.
- Storage: Recipes are stored using a structured schema: title, servings, ingredients (line-items), and steps.
- Versioning: Every tailoring action creates an immutable version. Users can overwrite the current version or save as a new recipe.
- Undo: Single-step undo for version overwrites via a toast notification.

### 3.4. Ingredient Parsing and Correction
- Structured Parsing: Before tailoring, recipes must be parsed into quantity, unit, and item fields.
- Parse Gate: A recipe must have 80% of lines successfully parsed and no more than 2 unknown units to proceed to tailoring.
- Guided Correction: A simple UI allows users to manually edit ingredient lines that failed to parse correctly.
- Unit Registry: A versioned JSON registry handles unit normalization and synonym mapping.

### 3.5. Recipe Tailoring Logic
- Sequential Flow: Tailoring follows a two-step process—Scale Portions first, then Reduce Calories.
- Step 1: Scaling: Deterministically multiplies ingredient quantities and adjusts numeric values in steps.
- Step 2: Calorie Reduction: AI-driven swaps and quantity rebalancing based on selected aggressiveness.
- Aggressiveness Thresholds:
  - Low: Up to 3 ingredient changes; max 15% calorie reduction.
  - Medium: Up to 6 ingredient changes; max 25% calorie reduction.
  - High: Up to 10 ingredient changes; max 35% calorie reduction.
- Keep-it-similar Constraint: Hard requirement to preserve primary protein, cooking method, and core flavor base.

### 3.6. Performance and Response UX
- Latency Budgets:
  - Parse P95: ≤ 2 seconds.
  - Scale P95: ≤ 0.2 seconds.
  - AI Rewrite P95: ≤ 6 seconds.
  - End-to-end P95: ≤ 8 seconds.
- Two-phase Response: Always return the scaled recipe within 2 seconds. Attempt the AI rewrite/calorie estimate; if it takes longer than 8 seconds, return the scaled recipe with a "Calories unavailable" status.

### 3.7. Analytics and Data
- Internal Events: Append-only table capturing user actions, success/failure codes, and latency.
- Data Retention: Event data is kept for 30–90 days.

## 4. Product Boundaries
- No recipe importing from URLs (copy/paste only).
- No multimedia support (no photos, videos, or audio).
- No social or sharing features (no recipe sharing or community feeds).
- No ingredient translation (English UI and input only).
- Web-only responsive interface; no native mobile applications.

## 5. User Stories

ID: US-001
Title: Secure Authentication via Google
Description: As a user, I want to sign in with my Google account so that I can securely access my recipes and preferences without creating a new password.
Acceptance Criteria:
1. User can click a Sign in with Google button on the landing page.
2. User is redirected to the dashboard after successful authentication.
3. System creates a unique user record on first login.
4. User session persists across page refreshes until logout.

ID: US-002
Title: Set Dietary Preferences and Profile Completion
Description: As a user, I want to define my avoid list and calorie targets so that the AI can tailor recipes to my needs.
Acceptance Criteria:
1. User can add/remove items from an avoid list in their profile.
2. User can set an optional target calories per serving value.
3. User can toggle between Metric and US unit systems.
4. Profile status shows as complete if at least one avoid item is set or target calories are defined.

ID: US-003
Title: Input Recipe via Copy/Paste
Description: As a user, I want to paste a recipe into a template so that I can quickly add existing recipes to the app.
Acceptance Criteria:
1. System provides a multi-line text input with clear headers for Ingredients and Steps.
2. A one-click auto-format button cleans up common paste artifacts.
3. System validates that both Ingredients and Steps sections contain text before saving.

ID: US-004
Title: Automated Ingredient Parsing
Description: As a user, I want the system to automatically parse my ingredients into structured data so that they can be scaled and analyzed.
Acceptance Criteria:
1. System extracts quantity, unit, and item from each ingredient line.
2. Items that cannot be scaled are flagged as non-scalable but preserved in raw text.
3. System normalizes units (e.g., "g" to "grams") using the internal registry.

ID: US-005
Title: Guided Parse Correction
Description: As a user, I want to fix ingredients that the system couldn't parse so that I can proceed with tailoring.
Acceptance Criteria:
1. If the parse gate (80% success) is not met, the system highlights failing lines.
2. User can edit the raw text of specific ingredient lines in a focused editor.
3. System re-parses the edited lines and updates the success percentage in real-time.

ID: US-006
Title: Deterministic Portion Scaling
Description: As a user, I want to change the number of servings for a recipe so that I can cook the right amount.
Acceptance Criteria:
1. User can select a serving size between 1 and 12.
2. System multiplies all scalable ingredient quantities by the scaling factor.
3. Numeric values in the steps (e.g., "add 2 eggs") are updated if they match ingredient quantities.
4. Scaled output is displayed in under 2 seconds.

ID: US-007
Title: Calorie Reduction Tailoring
Description: As a user, I want the AI to reduce the calories in my recipe while following my preferences.
Acceptance Criteria:
1. User can select an aggressiveness level (Low, Medium, High).
2. AI applies substitutions and quantity changes within the allowed thresholds.
3. System enforces the keep-it-similar rules (protein/method/flavor) unless disabled.
4. Output includes estimated total calories and calories per serving.

ID: US-008
Title: Avoid List Enforcement
Description: As a user, I want the system to ensure that none of the ingredients I avoid appear in my tailored recipes.
Acceptance Criteria:
1. AI checks all substitutions against the user's profile avoid list.
2. If an original ingredient is on the avoid list, the AI must remove or replace it.
3. If a conflict occurs with a core ingredient, the user is prompted to resolve it (e.g., allow alternative protein).

ID: US-009
Title: Calorie Confidence Labeling
Description: As a user, I want to know how accurate the calorie estimate is so that I can make informed decisions.
Acceptance Criteria:
1. Every tailored recipe shows a confidence indicator: Low, Medium, or High.
2. Confidence is based on the success rate of ingredient parsing and data availability in the AI model.
3. If calorie estimation fails, the recipe is still shown with a "Calories unavailable" message.

ID: US-010
Title: Recipe Versioning and Overwrite
Description: As a user, I want to choose whether to save a tailored recipe as a new entry or overwrite the current one.
Acceptance Criteria:
1. User is presented with "Save as New" and "Overwrite" options after tailoring.
2. Overwriting updates the latest version pointer but keeps the prior version in the database.
3. Saving as new creates a completely separate recipe entity.

ID: US-011
Title: Undo Overwrite Action
Description: As a user, I want to quickly undo an accidental overwrite so that I don't lose my previous recipe version.
Acceptance Criteria:
1. A toast notification with an Undo button appears immediately after an overwrite.
2. Clicking Undo reverts the recipe version pointer to the previous state.
3. The toast disappears after 5 seconds or upon manual dismissal.

ID: US-012
Title: Browsing and Searching Recipes
Description: As a user, I want to easily find my saved recipes so that I can reuse them.
Acceptance Criteria:
1. Dashboard displays a list of recent recipes.
2. User can filter recipes by a Favorites flag.
3. A search bar allows finding recipes by words in the title or ingredients.

ID: US-013
Title: Usage Limit Management
Description: As a user, I want to know when I am approaching my weekly tailoring limit so that I can plan accordingly.
Acceptance Criteria:
1. System tracks the number of tailoring attempts per user per week (resetting every 7 days).
2. A warning message appears when the user reaches 40 attempts (80% of limit).
3. At 50 attempts, the tailoring button is disabled with a "Try again next week" message.

ID: US-014
Title: Handling Unrealistic Targets
Description: As a user, I want to be notified if my calorie target cannot be met within the selected constraints.
Acceptance Criteria:
1. If AI cannot reach the target within aggressiveness/keep-similar limits, it provides the closest possible version.
2. The output includes a standard note: "Target not achievable within selected aggressiveness/keep-similar; closest estimate shown."

ID: US-015
Title: Account Deletion
Description: As a user, I want to delete my account and all associated data for privacy reasons.
Acceptance Criteria:
1. System provides a Delete Account button in the profile settings.
2. User must confirm the action via a modal dialog.
3. Upon confirmation, the user is logged out and all profile, recipe, and version data is permanently deleted.

## 6. Success Metrics

Metric: Profile Completion Rate
- Target: 90% of users have a complete profile.
- Calculation: (Users with at least 1 avoid item OR target calories set) / (Total authenticated users).

Metric: Weekly Tailoring Engagement
- Target: 75% of users generate one or more tailored recipes per week.
- Calculation: (Distinct users with at least one success event in the last 7 days) / (Total distinct users active in the last 7 days).

Metric: Tailoring Success Rate
- Target: 90% of tailoring attempts result in a successful output.
- Calculation: (tailor_success events) / (tailor_success + tailor_failure events).

Metric: Performance Budget Adherence
- Target: P95 end-to-end tailoring latency ≤ 8 seconds.
- Calculation: 95th percentile of the latency field in the events table for tailoring actions.

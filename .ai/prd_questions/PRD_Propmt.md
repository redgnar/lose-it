You are an experienced product manager whose task is to create a comprehensive Product Requirements Document (PRD) based on the following descriptions:

<project_description>
# Application – Lose It (MVP)

### Main problem

Adapting cooking recipes available online to personal needs and dietary requirements is challenging. The app uses AI and user preferences to suggest tailored recipes.

### Minimum feature set

* Saving, reading, browsing, and deleting recipes in text form
* A simple user account system to associate users with their own recipes
* A user profile page for storing dietary preferences
* AI integration that allows modifying recipes according to the user’s dietary preferences

### What is NOT included in the MVP scope

* Importing recipes from a URL
* Rich multimedia support (e.g., recipe photos)
* Sharing recipes with other users
* Social/community features

### Success criteria

* 90% of users have the dietary preferences section filled out in their profile
* 75% of users generate one or more recipes per week

</project_description>

<project_details>
<conversation_summary>
<decisions>
1. Target users: individuals who want to lose weight.
2. MVP focuses on tailoring user-provided recipes (not generating from scratch).
3. Recipe data model: minimal structured schema with required fields: title, servings, ingredients, steps.
4. Recipe input UX: optimized for copy/paste using an input template with section headers (Ingredients/Steps), lightweight validation, and a one-click auto-format step before tailoring.
5. Tailoring modes: two primary modes—Scale portions and Reduce calories—with a simple “Mode + controls” UI.
6. Controls/defaults: servings is an explicit control (1–12); optional profile preference includes target calories per serving; defaults pulled from profile where available.
7. Tailoring order: sequential flow—Scale first, then optionally Reduce calories (explicit step 1 / step 2 UX).
8. Output format: standardized final recipe only (no comparison view): Title → Servings → Estimated calories/serving (+ confidence low/med/high) → Ingredients (with quantities) → Steps.
9. Units/localization: default to metric with locale detection; user-selectable Metric/US toggle stored in profile; MVP UI language is English with locale-aware formatting (e.g., decimal separators); ingredient translation out of scope.
10. Calorie estimation: AI-based estimation with transparent labeling; show total + per-serving plus a confidence indicator (low/med/high). If unavailable, show “Calories: unavailable (retry)” and still output the recipe.
11. Primary product JTBD: Primary = fast tailoring; Secondary = stays similar. De-prioritize precise calorie accuracy in MVP messaging/acceptance criteria.
12. “Keep it similar” constraint: preserve cooking method + primary protein → preserve core flavor base → apply non-core swaps/portion rebalancing within aggressiveness → chase calorie target last. “Never remove” core items unless user disables keep-similar.
13. Aggressiveness thresholds are explicit:
   - Low: ≤3 ingredient changes and ≤15% calorie reduction
   - Medium: ≤6 ingredient changes and ≤25% calorie reduction
   - High: ≤10 ingredient changes and ≤35% calorie reduction
14. Ingredient change counting: one change per ingredient line-item for replace/remove/add; quantity-only adjustments do not count unless ingredient identity changes.
15. Scaling behavior: deterministically scale ingredient quantities; step edits restricted to obvious numeric multipliers; never change temperatures; if uncertain, do not edit steps.
16. Handling unrealistic targets: best-effort tailoring; if target cannot be met within constraints, include standardized note: “Target not achievable within selected aggressiveness/keep-similar; closest estimate shown.”
17. Parsing requirement: require structured extraction into ingredient line-items (quantity + unit + item) before tailoring; if parsing fails, use guided correction. Guided correction is MVP-simple: per-ingredient raw line editing (no advanced UI).
18. Parse gate: proceed only if ≥80% of ingredient lines are parsed with quantity OR a recognized non-scalable marker; and no more than N unknown units where N=2.
19. Ingredient storage: store each ingredient line as (raw_text, parsed_quantity, parsed_unit, parsed_item, confidence) plus MVP additions: is_scalable boolean and non_scalable_reason; parsed_quantity/unit may be nullable.
20. Non-scalable handling: preserve raw_text, label “(not scaled)”, and exclude from calorie math unless confidently inferable.
21. Unit handling: define a versioned unit registry (JSON) with canonical units + synonyms per system; normalize units during parse; log unknown units and map common ones weekly.
22. Partial parsing policy: accept partial parsing; mark multi-item lines as low confidence; require edits only if needed to satisfy the ≥80% parse threshold.
23. AI integration approach: multi-step pipeline with caching:
    (1) format/parse,
    (2) compute scaling deterministically,
    (3) AI rewrite + calorie estimate;
    cache parsed recipes for reuse.
24. Caching: cache parsed ingredients by normalized recipe text hash + unit system + locale; invalidate cache on any ingredient-line edit.
25. Performance budgets: define hard budgets—Parse P95 ≤ 2s, Scale P95 ≤ 0.2s, Rewrite+estimate P95 ≤ 6s, End-to-end P95 ≤ 8s—and a secondary UX metric time-to-first-result ≤ 2s.
26. Tailoring response UX: two-phase response—always return scaled output ≤2s; attempt rewrite+estimate within the same request up to 8s; if not done, finalize scaled-only with “Calories unavailable” + retry CTA.
27. Usage limits: enforce a conservative cap of 50 tailors/week; warn at 80% usage; hard block at 100% with “try again next week” messaging; encourage saving/browsing cached recipes.
28. Persistence/versioning: tailored recipes are saved as new immutable versions with metadata (mode, target, aggressiveness, timestamp, model version); “Overwrite existing” is implemented as “set latest pointer”; “Save as new” creates a new recipe entity.
29. Undo: MVP supports single-step undo by reverting the latest pointer to the prior version, exposed via toast after overwrite; full version history UI deferred.
30. Browsing: support Recent, Favorites (simple boolean flag + filter), and basic search by title/keyword.
31. Profile completion definition: profile is complete if either (a) at least one avoid item is provided OR (b) one weight-loss preference is set (e.g., target calories/serving).
32. Avoid list rules: avoid list is a hard constraint; if it conflicts with keep-similar core items, user must choose to disable keep-similar or accept an alternative protein; otherwise tailoring must not include avoided items.
33. Avoid matching scope: explicit matching only against parsed_item + raw_text; do not infer hidden ingredients; clarify in settings/help copy.
34. Authentication: sign-in first in MVP; Google OAuth only.
35. Analytics approach: no external analytics stack in MVP; instead store minimal internal data (append-only events table) with retention 30–90 days, capturing profile completion, tailoring success/fail, saves, latency, and failure codes.
36. Success metrics definitions:
   - weekly tailoring users = distinct users with recipe_tailored_success in last 7 days
   - tailor success rate = success / (success + failure) for tailor attempts
   - profile_complete = avoid_count ≥ 1 OR target_calories_set = true
     Document SQL-like queries in PRD.
37. Metrics review: lightweight weekly review using internal queries/exports; initial thresholds: profile_complete ≥ 60%, weekly tailoring users ≥ 40%, tailor success rate ≥ 90%, P95 under budget.
38. Data policy & deletion: store only necessary profile fields; events store user_id + timestamps + non-sensitive properties; implement account delete and recipe delete with cascading deletion of recipe versions; document event retention and whether any aggregated metrics remain.
39. Platform constraint: web-only, responsive; prioritize mobile-friendly editing with vertical ingredient list and no horizontal scrolling for paste → auto-format → edit lines → run step 1.
    </decisions>

<matched_recommendations>
1. Focus MVP on “paste recipe → tailor recipe” with tailoring only (no recipe generation).
2. Use a minimal structured recipe schema (title, servings, ingredients, steps) to ensure deterministic scaling and predictable storage/browsing.
3. Optimize recipe input for copy/paste with a template and one-click auto-format + lightweight validation.
4. Standardize output contract to a single final recipe view including calories total/per-serving and confidence labeling.
5. Provide simple mode-based UI (Scale portions / Reduce calories) with explicit controls, defaults from profile, and sequential Scale → Reduce flow.
6. Enforce “keep it similar” via hard constraints (primary protein + cooking method + core flavor base) and prioritize non-core substitutions first.
7. Implement aggressiveness with explicit thresholds and strict line-item change counting to bound transformations and user surprise.
8. Require ingredient parsing before tailoring; store parsed ingredient fields with confidence and add is_scalable/non_scalable_reason to support deterministic scaling and safe handling of ambiguous lines.
9. Implement a multi-step AI pipeline with caching (parse → scale deterministic → AI rewrite+estimate) to meet latency and cost constraints.
10. Define and enforce explicit performance budgets and timeouts with a two-phase response + deterministic fallback matrix.
11. Add usage caps and rate-limiting policy to protect costs while maintaining a clear UX.
12. Persist tailored outputs as immutable versions; implement “Overwrite” as a pointer update; provide single-step undo for safety.
13. MVP favorites = boolean flag + filter; defer complex collections.
14. Avoid list is hard constraint with explicit conflict resolution; explicit-only matching (no hidden ingredient inference).
15. Replace external analytics with an internal append-only events table, metric definitions, and weekly review cadence; document the SQL-like queries in the PRD.
16. Prioritize mobile-friendly UX for editing and tailoring flow on web responsive layouts.
    </matched_recommendations>

<prd_planning_summary>
a. Main functional requirements (MVP)
- Authentication & accounts:
   - Google OAuth-only login.
   - Sign-in required before use.
   - Account deletion supported.
- User profile (weight-loss focused):
   - Avoid list (free-form items to exclude).
   - Optional target calories per serving.
   - Unit preference toggle (Metric/US) stored in profile; English UI with locale-aware formatting (decimal separators).
   - Profile “complete” if avoid list has ≥1 item OR target calories/serving set.
- Recipe management (CRUD + browsing):
   - Create recipes primarily via copy/paste into a template with Ingredients/Steps sections.
   - Validate presence of core sections; one-click auto-format.
   - Store recipes with minimal schema: title, servings, ingredients, steps.
   - Browsing: Recent, Favorites (boolean flag + filter), basic search by title/keyword.
   - Deleting a recipe deletes all versions (cascade).
- Parsing & correction:
   - Must parse into structured ingredient line-items (quantity + unit + item) before tailoring.
   - Parse gate: ≥80% parsed (quantity OR non-scalable marker) and ≤2 unknown units.
   - Guided correction: per-ingredient raw line edits (MVP-simple).
   - Ingredient storage: (raw_text, parsed_quantity, parsed_unit, parsed_item, confidence, is_scalable, non_scalable_reason).
   - Non-scalable lines are preserved, labeled “(not scaled)”, excluded from calorie math unless confidently inferable.
   - Units: versioned unit registry JSON with canonical units + synonyms; normalize; log unknowns for periodic mapping.
   - Partial parsing allowed; multi-item lines flagged low confidence; edits required only to pass parse gate.
- Tailoring (core MVP)
   - Modes: Scale portions and Reduce calories.
   - Flow: explicit step 1 (Scale) then optional step 2 (Reduce calories).
   - Controls:
      - Servings (1–12).
      - Optional target calories/serving.
      - Aggressiveness: Low/Medium/High with thresholds:
         - Low: ≤3 ingredient changes and ≤15% calorie reduction
         - Medium: ≤6 changes and ≤25%
         - High: ≤10 changes and ≤35%
      - Keep-it-similar toggle with hard constraints:
         - Preserve cooking method + primary protein, then core flavor base; prioritize non-core swaps; chase calorie target last.
      - Change counting: one change per ingredient line-item for add/remove/replace; quantity-only changes don’t count unless identity changes.
   - Scaling rules:
      - Deterministic scaling of ingredient quantities.
      - Step edits restricted to obvious numeric multipliers; never change temperatures; if uncertain, do not edit steps.
   - Output contract:
      - Title → Servings → Estimated calories (total + per serving) + confidence (low/med/high) → Ingredients (with quantities) → Steps.
      - No comparison/diff UI.
      - If target not met: standardized note “Target not achievable within selected aggressiveness/keep-similar; closest estimate shown.”
      - If calorie estimate unavailable: “Calories: unavailable (retry)” but still output recipe.
   - Keep-similar heuristics:
      - Deterministic heuristic layer for detecting primary protein (category + quantity dominance) and core flavor base (top N aromatics/sauces/spices).
      - Store detected_core_items[] in version metadata for enforcement and internal debugging (not exposed in MVP UI).
- Performance, reliability, and limits:
   - Budgets:
      - Parse P95 ≤ 2s
      - Scale P95 ≤ 0.2s
      - Rewrite+estimate P95 ≤ 6s
      - End-to-end P95 ≤ 8s
      - Time-to-first-result ≤ 2s
   - Two-phase response:
      - Always return scaled output ≤2s.
      - Attempt rewrite+estimate up to 8s; if not finished, return scaled-only with “Calories unavailable” + retry CTA.
   - Failure matrix:
      - Parse fails → guided correction required before tailoring.
      - Rewrite fails → return scaled recipe + “calories unavailable” + retry CTA.
      - Calorie estimate fails → return recipe + “estimate unavailable.”
      - Each failure path logged distinctly.
   - Usage cap:
      - 50 tailors/week; warn at 80%; block at 100% with “try again next week.”
- Data, instrumentation, and measurement:
   - No external analytics vendor in MVP.
   - Internal append-only events table (30–90 day retention) storing user_id + timestamps + non-sensitive properties, plus latency and failure codes.
   - Metric definitions:
      - weekly tailoring users = distinct users with recipe_tailored_success in last 7 days
      - tailor success rate = success / (success + failure) for tailor attempts
      - profile_complete = avoid_count ≥ 1 OR target_calories_set = true
      - Document SQL-like queries in PRD.
   - Review cadence: weekly review via internal queries/exports.
   - Initial success thresholds: profile_complete ≥ 60%, weekly tailoring users ≥ 40%, tailor success rate ≥ 90%, and P95 under budget.
- UX / platform constraints:
   - Web-only, responsive.
   - Prioritize mobile-friendly editing: vertical list for ingredient lines; avoid horizontal scrolling in paste → auto-format → edit → run flow.
   - No URL importing, no images/multimedia, no social/sharing features.

b. Key user stories and usage paths
- US1: Sign in and set preferences
   - User signs in via Google, sets avoid list and/or target calories/serving, chooses Metric/US.
- US2: Add recipe by paste + auto-format
   - User pastes recipe into template; auto-format; parse runs; user edits ingredient lines if needed to pass gate; recipe saved.
- US3: Tailor recipe—Scale portions (Step 1)
   - User selects servings (1–12); deterministic scaling applied; scaled output shown quickly (≤2s).
- US4: Tailor recipe—Reduce calories (Step 2)
   - User selects aggressiveness + keep-similar; optional target calories/serving; system applies swaps/rebalances within constraints; shows final recipe with estimated calories + confidence.
- US5: Handle constraints and conflicts
   - If avoid list conflicts with core items, user must disable keep-similar or accept an alternative protein; tailoring proceeds without avoided items.
- US6: Save, overwrite, undo
   - Tailored output saved as new immutable version with metadata; overwrite updates latest pointer; user can single-step undo overwrite via toast.
- US7: Browse and reuse
   - User browses Recent/Favorites, searches by keyword, re-runs tailoring using cached parsing.

c. Important success criteria and ways to measure them
- Core product goals (MVP):
   - Fast tailoring: end-to-end P95 ≤ 8s; time-to-first-result ≤ 2s; measured via internal events latency fields.
   - Tailoring reliability: tailor success rate ≥ 90%; measured via success/failure event ratio.
   - Engagement: weekly tailoring users ≥ 40%; measured via distinct users with recipe_tailored_success over 7 days.
   - Onboarding/intent capture: profile_complete ≥ 60%; measured via profile fields and profile_completed event.
- Operational guardrails:
   - Usage cap adherence: track tailors/week per user; warn/block events.

d. Unresolved issues / areas needing further clarification
- Exact allowed unit registry content for MVP (canonical list + synonym coverage) and operational process for updating it.
- Definition of “top N” for core flavor base and any category lists used for protein/aromatic detection (initial heuristics taxonomy).
- Exact data retention policy choice for events after user deletion (strict delete vs keeping aggregated non-identifiable metrics) and the compliance stance required.
- Precise UI/UX spec for two-phase response presentation (how scaled-first display transitions to final rewrite, and how retry is offered).
- Exact SQL storage technology/stack choices for recipes, versions, caching, and events (implementation detail not selected yet).
  </prd_planning_summary>

<unresolved_issues>
1. Finalize the MVP unit registry contents (canonical units + synonyms) and maintenance workflow (how “map common unknowns weekly” is executed operationally).
2. Specify the heuristic taxonomy for detected_core_items[] (protein categories, aromatic/sauce/spice lists) and choose N for “top N flavor base.”
3. Confirm privacy/deletion semantics for the internal events table (strict per-user deletion vs retaining aggregated non-identifiable metrics) and retention within 30–90 days.
4. Detail the two-phase response UX (presentation, transitions, retry behavior) to ensure it is understandable without a comparison view.
5. Decide implementation stack details (DB choice, caching layer, job/request handling approach) necessary to meet the latency budgets and immutability/versioning guarantees.
   </unresolved_issues>
   </conversation_summary>

</project_details>

Follow these steps to create a comprehensive and well-organized document:

1. Divide the PRD into the following sections:
   a. Project Overview
   b. User Problem
   c. Functional Requirements
   d. Project Boundaries
   e. User Stories
   f. Success Metrics

2. In each section, provide detailed and relevant information based on the project description and answers to clarifying questions. Make sure to:
    - Use clear and concise language
    - Provide specific details and data as needed
    - Maintain consistency throughout the document
    - Address all points listed in each section

3. When creating user stories and acceptance criteria
    - List ALL necessary user stories, including basic, alternative, and edge case scenarios.
    - Assign a unique requirement identifier (e.g., US-001) to each user story for direct traceability.
    - Include at least one user story specifically for secure access or authentication, if the application requires user identification or access restrictions.
    - Ensure that no potential user interaction is omitted.
    - Ensure that each user story is testable.

Use the following structure for each user story:
- ID
- Title
- Description
- Acceptance Criteria

4. After completing the PRD, review it against this checklist:
    - Is each user story testable?
    - Are the acceptance criteria clear and specific?
    - Do we have enough user stories to build a fully functional application?
    - Have we included authentication and authorization requirements (if applicable)?

5. PRD Formatting:
    - Maintain consistent formatting and numbering.
    - Do not use bold formatting in markdown ( ** ).
    - List ALL user stories.
    - Format the PRD in proper markdown.

Prepare the PRD with the following structure:

```markdown
# Product Requirements Document (PRD) - Lose It!
## 1. Product Overview
## 2. User Problem
## 3. Functional Requirements
## 4. Product Boundaries
## 5. User Stories
## 6. Success Metrics
```

Remember to fill each section with detailed, relevant information based on the project description and our clarifying questions. Ensure the PRD is comprehensive, clear, and contains all relevant information needed for further product development.

The final output should consist solely of the PRD in the specified markdown format, which you will save in the file .ai/prd.md

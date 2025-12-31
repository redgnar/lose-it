# Lose It!

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-777bb4.svg)](https://php.net)
[![Symfony Version](https://img.shields.io/badge/symfony-8.0-black.svg)](https://symfony.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**Lose It!** is an MVP application designed for individuals seeking to lose weight by simplifying the process of adapting cooking recipes to their specific dietary needs and calorie goals. By leveraging AI, the application provides a fast and reliable way to "healthify" recipes while preserving their core flavor and cooking methods.

---

## Table of Contents
- [Project Description](#project-description)
- [Tech Stack](#tech-stack)
- [Getting Started Locally](#getting-started-locally)
- [Available Scripts](#available-scripts)
- [Project Scope](#project-scope)
- [Project Status](#project-status)
- [License](#license)

---

## Project Description

Weight-conscious individuals often find appealing recipes online that don't match their calorie targets. **Lose It!** solves this by automating recipe tailoring through a two-step process:

1.  **Deterministic Portion Scaling:** Multiplies ingredient quantities and adjusts numeric values in cooking steps based on the desired number of servings.
2.  **AI-Driven Calorie Reduction:** Uses AI to perform smart ingredient swaps and quantity rebalancing based on user preferences and selected "aggressiveness" levels (Low, Medium, High).

The application ensures that primary proteins, cooking methods, and core flavor bases are preserved, keeping the tailored dish as similar as possible to the original.

---

## Tech Stack

### Frontend
- **Framework:** Symfony 8.0 with [Symfony UX](https://ux.symfony.com/) (Twig + Stimulus)
- **Interactive Components:** [React 19](https://react.dev/) (used for the Recipe Parser)
- **Styling:** [Tailwind CSS 4](https://tailwindcss.com/), [Flowbite](https://flowbite.com/)
- **Language:** TypeScript 5

### Backend
- **Language:** PHP 8.4
- **Framework:** [Symfony 8.0](https://symfony.com/)
- **ORM:** [Doctrine ORM 3](https://www.doctrine-project.org/)
- **Database:** MariaDB
- **Authentication:** Google OAuth via `knpuniversity/oauth2-client-bundle`

### AI & Infrastructure
- **AI Engine:** [OpenRouter.ai](https://openrouter.ai/)
- **CI/CD:** GitHub Actions
- **Hosting:** DigitalOcean App Platform (PaaS)
- **Environment:** Docker-based development

---

## Getting Started Locally

### Prerequisites
- [Docker](https://www.docker.com/) and Docker Compose
- [Make](https://www.gnu.org/software/make/)

### Installation Steps

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/redgnar/lose-it.git
    cd lose-it
    ```

2.  **Configure Environment Variables:**
    Copy the `.env` file and fill in the required credentials (Google OAuth Client ID/Secret and OpenRouter API Key):
    ```bash
    cp app/.env app/.env.local
    ```

3.  **Build and Setup the Project:**
    This command will build the containers, install dependencies, and prepare the database:
    ```bash
    make setup
    ```

4.  **Access the Application:**
    Open your browser and navigate to `http://localhost`.

---

## Available Scripts

The project uses a `Makefile` to simplify common development tasks:

### Setup & Cleanup
- `make setup` - Full project initialization (rebuild, install, database setup).
- `make cleanup` (or `make cln`) - Stop containers and remove volumes, vendor folders, and cache.
- `make up` / `make down` - Start or stop the Docker containers.

### Testing & Quality
- `make qa` - Run all quality checks (CS-Fixer, PHPStan, and all tests).
- `make tt` - Run the entire PHPUnit test suite.
- `make tu` / `make ta` / `make ti` / `make tf` - Run Unit, API, Integration, or Functional tests respectively.
- `make phpstan` - Run static analysis.
- `make cf` - Automatically fix code style issues.

### Database
- `make migrations-diff` - Generate a new database migration based on entity changes.
- `make migrations-migrate` - Apply pending migrations.
- `make lf` - Load database fixtures.

---

## Project Scope

- **Authentication:** Secure sign-in via Google OAuth.
- **User Profile:** Manage dietary avoid lists, calorie targets, and unit systems (Metric/US).
- **Recipe Management:** CRUD operations with text-based input and auto-formatting.
- **Ingredient Parsing:** AI-assisted structured parsing of raw ingredient text.
- **Tailoring Logic:** Deterministic scaling and AI-driven calorie reduction with three aggressiveness levels.
- **Versioning:** Immutable recipe versions with "undo" capability for overwrites.
- **Usage Limits:** Weekly tailoring attempt limits per user.

---

## Project Status

The project is currently in the **MVP (Minimum Viable Product)** stage. Core functionality for recipe scaling and AI tailoring is implemented, with a focus on performance and reliability.

---

## License

This project is licensed under the **MIT License**. See the `LICENSE` file (if available) for more details.

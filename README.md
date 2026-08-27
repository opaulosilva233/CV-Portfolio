# 💼 Modern Portfolio & Career CMS

> A full-stack, data-driven platform for managing professional identity, interactive CVs, and visitor analytics.

[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-9553E9?style=for-the-badge&logo=inertia&logoColor=white)](https://inertiajs.com/)
[![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)
[![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com/)

---

## 🎯 Overview

Designed to go beyond a traditional static portfolio, this project functions as a **custom Headless CMS**. It gives total autonomy to update career history, featured projects, and technical skills through an authenticated admin panel—eliminating the need to edit source code or trigger new deployments for routine content updates.

🌐 **Live Website:** [paulosilvadev.me](https://paulosilvadev.me/)

---

## ✨ Key Features

### 🌐 Dynamic Internationalization (i18n & Queues)
- Native support for multiple languages (**PT, EN, NL**)[cite: 1].
- Database-backed translation system with asynchronous processing via **Queues / Jobs (`TranslateModelJob`)** and custom Artisan CLI commands (`php artisan translate:all`)[cite: 1].

### 📊 Custom Telemetry & Analytics Dashboard
- Built-in traffic monitoring without full reliance on heavy third-party tracking scripts.
- Page view analytics (`PageView`) and granular section engagement metrics (`SectionEngagement`)[cite: 1].

### 🛠️ Comprehensive Backoffice (CMS)
- **Admin Dashboard:** Granular control over bio information, landing page sections, and site settings (`SiteSetting`)[cite: 1].
- **Career Timeline:** Structured work experience and education timelines with support for nested roles (`ExperienceRoles`)[cite: 1].
- **Project Showcase:** Reorderable project gallery with image uploads, dynamic tech stacks, and completion status[cite: 1].
- **Skills & Interests:** Categorized tech skills with custom SVG/text icons[cite: 1].
- **Inbox:** Centralized contact form message inbox and triage[cite: 1].

### ⚡ Performance & Architecture
- Automatic cache clearing strategies via model traits (`ClearsPortfolioCache`)[cite: 1].
- Single-page application (SPA) experience powered by **Inertia.js** without full page reloads[cite: 1].
- Containerized architecture configured with **Docker Compose (Nginx + PHP-FPM)**[cite: 1].

---

## 🛠️ Tech Stack

* **Backend:** PHP 8.2+ / Laravel 11[cite: 1]
* **Frontend:** Vue.js / Inertia.js[cite: 1]
* **Styling & UI:** Tailwind CSS[cite: 1]
* **Database:** MySQL / PostgreSQL / SQLite[cite: 1]
* **Infrastructure:** Docker & Docker Compose (Nginx + PHP-FPM)[cite: 1]

---

## 👤 Author

**Paulo Silva**
- Website: [paulosilvadev.me](https://paulosilvadev.me/)
- GitHub: [@opaulosilva233](https://github.com/opaulosilva233)

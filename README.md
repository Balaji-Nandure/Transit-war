# Transit-war

This repository contains the Transit-war PHP application for Phase 2.

Quick setup

1. Install dependencies (if any) and configure `config/db.php`.
2. Start the application (see `docker/` for Docker setup).

Recommended files included:
- `README.md` — this file
- `LICENSE` — project license
- `.gitignore` — ignores common files

To push this local project to GitHub (example):

```bash
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/Balaji-Nandure/Transit-war.git
git push -u origin main
```

See `docker/docker-compose.yml` for the development environment.
# NSP - TransactiWar Secure Web Application

## Overview
Secure PHP web application built for the Network Security course project. It provides:
- Authentication (register/login/logout)
- Dashboard and account balance view
- Profile management with image upload
- User search and profile viewing
- Transfer and transaction history
- Request/activity logging


## Repository Structure
```text
NSP/
├── app/
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── ProfileController.php
│   │   ├── SearchController.php
│   │   ├── TransactionHistoryController.php
│   │   └── TransferController.php
│   ├── middleware/
│   │   └── LoggerMiddleware.php
│   └── services/
│       └── SecurityService.php
├── config/
│   └── db.php
├── docker/
│   ├── docker-compose.yml
│   ├── Dockerfile
│   ├── entrypoint.sh
│   └── ssl.conf
├── public/
│   ├── index.php
│   ├── login.php
│   ├── login_form.php
│   ├── register.php
│   ├── register_form.php
│   ├── dashboard.php
│   ├── dashboard_view.php
│   ├── profile.php
│   ├── profile_view.php
│   ├── view_profile.php
│   ├── view_profile_view.php
│   ├── search.php
│   ├── search_form.php
│   ├── transfer.php
│   ├── transfer_form.php
│   ├── transaction_history.php
│   ├── transaction_history_view.php
│   ├── serve_image.php
│   ├── logout.php
│   └── styles.css
├── scripts/
│   └── create_test_users.php
├── sql/
│   └── schema.sql
├── SECURITY_REPORT.txt
└── README.md
```

## Runtime-Generated Paths
These paths are created inside the container during build/startup and are not tracked in git:
- `/var/www/storage/uploads`
- `/var/www/storage/logs`
- `/var/www/sessions`

## Quick Start (Docker)

### Prerequisites
- Docker
- Docker Compose

### Run
```sh
docker compose -f docker/docker-compose.yml up --build
```

### Access
- Web app (HTTPS): `https://localhost:8443`
- MySQL runs in Docker as service `db` (internal host: `db`)

Note: The app uses a self-signed SSL certificate, so your browser will show a security warning the first time.

### Reset Database
```sh
docker compose -f docker/docker-compose.yml down -v
docker compose -f docker/docker-compose.yml up --build
```
Using `-v` removes the MySQL volume and re-applies `sql/schema.sql`.

## Test Users
On container startup, `scripts/create_test_users.php` runs from `docker/entrypoint.sh` and seeds users (if they do not already exist):
- `alice@example.com`
- `bob@example.com`
- `charlie@example.com`
- `dave@example.com`
- `eve@example.com`

Default password for all seeded users:
- `78d9ed00fbc7d1d6056d480c77cc3d7e2a55923be88bd9b8a219d3afe61ed8ba`

## Database Tables
- `users`
- `transactions`
- `logs`
- `login_attempts`

See `sql/schema.sql` for exact columns, constraints, and indexes.

## Security Highlights
- PDO prepared statements (`ATTR_EMULATE_PREPARES=false`)
- CSRF token protection for POST forms
- Session hardening and regeneration
- Password hashing with `password_hash()` / verification with `password_verify()`
- Login rate limiting using `login_attempts`
- File upload validation and controlled image serving
- Transaction locking/atomicity for balance updates
- Dual activity logging (file + database)

## Tech Stack
- PHP 8.2 + Apache
- MySQL 8.0
- HTML/CSS (Bootstrap-based UI)
- Docker / Docker Compose

## Team
- Team-12

#### Authors

- Balaji Nandure: Money transfer module, frontend development, and general testing.
- Gadekar S.B.: Authentication, database integration, CSRF token implementation, SQL injection testing, and transaction history.
- Soham Pawar: Rate limiting, database integration, rate-limiting tests, and frontend development.
- Aditya Waghmare: Profile page, users page, file inclusion testing, and frontend development.
- Siddhant Godbole: Transaction workflows, frontend development, XSS testing, and CSRF testing.



ANTI-PLAGIARISM STATEMENT 
We certify that this assignment/report is our own work, based on our personal study and/or research, and that we have acknowledged all material and sources used in its preparation, whether books, articles, packages, datasets, reports, lecture notes, or any other document, electronic or personal communication. We also certify that this assignment/report has not previously been submitted for assessment/project in any other course lab, except where specific permission has been granted from all course instructors involved, or at any other time in this course, and that we have not copied in part or whole or otherwise plagiarized the work of other students and/or persons. We pledge to uphold the principles of honesty and responsibility at CSE@IITH. In addition, we understand our responsibility to report honor violations by other students if we become aware of them. 
Names:
Siddhant Godbole, Aditya Waghmare, Soham Pawar, Gadekar S.B., Balaji Nandure
CS22BTECH11054, CS22BTECH11061, CS22BTECH11055, CS22BTECH11022, CS25MTECH11007
DATE : 13 MARCH 2026
Signature: S.G., A.W., S.P., S.G., B.N.

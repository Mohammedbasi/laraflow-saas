# LaraFlow — Multi-Tenant SaaS Backend (Laravel)

LaraFlow is a **production-grade, API-first multi-tenant SaaS backend** built with Laravel.
The project focuses on real-world backend engineering concerns such as tenant isolation,
authorization, background processing, real-time updates, billing, and reporting.

This project was designed intentionally beyond CRUD tutorials, following patterns commonly
used in real SaaS products.

---

## ✨ Key Features

- **Single-database multi-tenancy** with strict tenant isolation
- **API-first authentication** using Laravel Sanctum
- **Role-based authorization** using Policies (Spatie Permission with Teams)
- **Real-time collaboration** using Laravel Events & Broadcasting
- **Background job processing** with queues, chaining, retries, and failure handling
- **Scheduled reporting system** with PDF generation and email delivery
- **Stripe billing integration** with subscription limits and webhooks
- **Audit logging** for critical domain events
- **Admin impersonation** for tenant support and debugging
- **Private file storage** with secure download endpoints

---

## 🧠 Architecture Highlights

LaraFlow follows a clean, production-oriented architecture:

- **Tenant isolation by default**
  - All tenant-owned models include `tenant_id`
  - Global scopes ensure no cross-tenant data leaks
- **Thin controllers**
  - Controllers handle validation + authorization only
  - Business logic lives in dedicated `Actions` and `Services`
- **Policies over role middleware**
  - Avoids Spatie Teams caching pitfalls
  - Ensures reliable tenant-aware authorization
- **Events for side effects only**
  - Broadcasting, activity feeds, notifications
- **Queue-safe jobs**
  - No reliance on request context or middleware
  - Deterministic inputs passed explicitly

---

## 🧩 Modules Overview

### Module A — Core SaaS Foundation
- Authentication (register, login, tokens)
- Tenant creation and ownership
- Role & permission system (tenant roles + platform admin)
- Invitations flow (email-based onboarding)
- Admin panel with impersonation
- Activity audit logging

### Module B — Billing & Subscription Limits
- Stripe integration using Laravel Cashier
- Free vs paid plan enforcement
- Subscription lifecycle via webhooks
- Seat limits enforced at business logic level

### Module C — Task Management
- Projects & tasks with ordering (drag & drop)
- Polymorphic comments
- File attachments (private storage)
- Activity logging for task events

### Module D — Real-Time Updates
- Task updates broadcasted per tenant
- Batched drag-and-drop updates
- Presence channels (who’s online)
- Live activity feed via broadcasting

### Module E — Reporting & Performance
- Weekly scheduled reports (console scheduler)
- Heavy stats calculation via queued jobs
- PDF generation (DomPDF)
- Email delivery with retries
- Idempotent report generation per tenant/week

---

## 🔄 Background Processing

- Database-backed queues (Windows-friendly)
- Job chaining for ordered workflows
- Failed job handling and retry support
- Designed for Horizon in Linux-based production environments

---

## 🔐 Security Considerations

- Strict tenant isolation at query level
- No public file URLs (private downloads only)
- Rate limiting for sensitive endpoints
- Authorization enforced via policies
- Queue jobs isolated from request context

---

## 🧪 Testing Strategy

- Pest for feature and integration tests
- Tests cover:
  - Tenant isolation
  - Authorization rules
  - Subscription limits
  - Background job dispatching
  - Business rule enforcement

---

## 🛠️ Tech Stack

- **Backend:** Laravel (API-first)
- **Auth:** Sanctum
- **Authorization:** Policies + Spatie Permission (Teams)
- **Queues:** Database queue (Horizon-ready)
- **Realtime:** Laravel Broadcasting
- **PDF:** DomPDF
- **Billing:** Stripe + Laravel Cashier
- **Database:** MySQL
- **Testing:** Pest

---

## 🎯 What This Project Demonstrates

- Designing a real SaaS backend beyond CRUD
- Handling multi-tenancy safely in a single database
- Writing queue-safe, production-ready jobs
- Applying clean architecture patterns in Laravel
- Thinking about scalability, failure modes, and operability

---

## 🔎 API Documentation

A Postman collection is included to explore and test the API without a frontend.

See:
- `docs/postman/` — Postman collection and usage instructions

## 🚀 Getting Started (Development)

```bash
git clone https://github.com/Mohammedbasi/laraflow-saas.git
cd laraflow-saas
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve

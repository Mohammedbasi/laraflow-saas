# LaraFlow API — Postman Collection

This directory contains a Postman collection for testing and exploring the LaraFlow API.

The collection demonstrates:
- Authentication flows
- Multi-tenant isolation
- Role-based authorization
- Business rule enforcement
- Background processing triggers

---

## 📦 Files

- `LaraFlow.postman_collection.json`  
  Complete API request collection.

- `LaraFlow.postman_environment.json`  
  Environment variables for local development.

---

## 🚀 How to Use

### 1) Import into Postman
- Open Postman
- Click **Import**
- Select both JSON files in this directory

### 2) Configure environment
Set:
- `base_url` → `http://localhost:8000/api/v1`
- Other values are populated automatically after authentication

### 3) Authentication
Run:
- `Auth → Register` or `Auth → Login`

The access token is automatically stored and reused.

---

## 🔐 Authorization Scenarios

The collection includes requests that demonstrate:

- Tenant-scoped resource access
- Forbidden access across tenants (404 / 403)
- Role-restricted actions
- Admin-only endpoints

---

## 🧪 Notes

- The API is backend-focused and designed for SPA or mobile clients.
- Background jobs (reports, emails) require queue workers to be running.

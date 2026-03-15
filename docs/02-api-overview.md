# API Overview

All API routes are loaded from module route files via `routes/api.php`:

- `app/Modules/Auth/api.php`
- `app/Modules/Permissions/api.php`
- `app/Modules/Users/api.php`

## Auth + MFA

- `POST /api/auth/signup`
- `POST /api/auth/login` (MFA token is required only when MFA is enabled for the user)
- `POST /api/auth/mfa/setup`
- `POST /api/auth/mfa/verify`
- `POST /api/auth/logout` (auth:sanctum)
- `POST /api/auth/tokens/revoke` (auth:sanctum)

## Protected resources (auth:sanctum)

- Users: `/api/users...`
- Roles: `/api/roles...`
- Permissions: `/api/permissions...`

## Postman

Import:

- `postman/Auth-2FA.postman_collection.json`

Recommended flow:

1. Sign Up
2. Login (No MFA)
3. Setup MFA
4. Verify MFA
5. Login (With MFA)

The collection also includes docs endpoints:

- `GET /docs/api`
- `GET /docs/api.json`

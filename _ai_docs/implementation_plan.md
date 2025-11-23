# Implementation Plan - Environment Variables

The goal is to externalize sensitive configuration (JWT Secret, Database Credentials) into `.env` files for both Frontend and Backend.

## User Review Required
> [!IMPORTANT]
> **Frontend Security**: `VITE_JWT_SECRET` will still be exposed in the client-side bundle. This is inherent to the shared-secret architecture requested.
> **Backend .env**: Ensure the `.env` file is added to `.gitignore` to prevent accidental commit of secrets.

## Proposed Changes

### Frontend
#### [NEW] [.env](my-app/.env)
- Add `VITE_JWT_SECRET=SECRET1234567890`

#### [MODIFY] [jwt.js](my-app/src/utils/jwt.js)
- Replace hardcoded secret with `import.meta.env.VITE_JWT_SECRET`.

### Backend
#### [NEW] [.env](my-app/backend/.env)
- Add `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `JWT_SECRET`.

#### [NEW] [env_loader.php](my-app/backend/config/env_loader.php)
- Create a helper to parse `.env` file and populate `$_ENV` or `getenv`.

#### [MODIFY] [db.php](my-app/backend/config/db.php)
- Include `env_loader.php`.
- Use `getenv()` or `$_ENV` for database credentials.

#### [MODIFY] [jwt_utils.php](my-app/backend/utils/jwt_utils.php)
- Include `../config/env_loader.php` (if not already included via other files, but safe to include).
- Use `getenv('JWT_SECRET')` for the secret key.

## Verification Plan

### Manual Verification
1.  **Frontend**:
    - Check if `VITE_JWT_SECRET` is correctly loaded (console log or just verify login works).
2.  **Backend**:
    - Verify database connection works.
    - Verify JWT generation and validation works.
3.  **End-to-End**:
    - Perform a full login flow.
    - Perform a user fetch (GET) to verify token validation.

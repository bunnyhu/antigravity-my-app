# Walkthrough - JWT Encoded Communication

I have implemented JWT encoding for all communication between the frontend and backend, and moved configuration to environment variables.

## Changes

### Configuration (Environment Variables)
- **Frontend**: `VITE_JWT_SECRET` in `.env` (root directory).
- **Backend**: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `JWT_SECRET` in `backend/.env`.

### Frontend
- **[NEW] `src/utils/jwt.js`**: Utility to sign and verify JWTs using Web Crypto API. Uses `import.meta.env.VITE_JWT_SECRET`.
- **[MODIFY] `src/utils/api.js`**: Added Axios interceptors:
    - **Request**: Signs the request body (if present) and wraps it in `{ payload: "..." }`.
    - **Response**: Verifies the response body (if it contains `payload`) and extracts the data.

### Backend
- **[NEW] `backend/config/env_loader.php`**: Loads environment variables from `.env`.
- **[MODIFY] `backend/config/db.php`**: Uses environment variables for database connection.
- **[MODIFY] `backend/utils/jwt_utils.php`**: Added `encodePayload($data)` and `decodePayload($token)` methods. Uses `JWT_SECRET` from environment.
- **[MODIFY] `backend/api/users.php`, `login.php`, `register.php`**:
    - Decodes incoming request payload from `{ payload: "..." }`.
    - Encodes outgoing responses into `{ payload: "..." }`.

### Database Migration
- **[NEW] `backend/migrations/migrate.php`**: Script to automatically create the database and tables.
- **[MODIFY] `backend/migrations/migrations.sql`**: Updated to use `INSERT IGNORE` to prevent duplicate entry errors.

### Directory Security
- **[NEW] `backend/**/index.html`**: Added empty/warning HTML files to all backend subdirectories to prevent directory listing.

## Verification

To verify the changes, please perform the following steps:

1.  **Environment Setup**:
    - Ensure `my-app\.env` exists with `VITE_JWT_SECRET=SECRET1234567890`.
    - Ensure `my-app\backend\.env` exists with correct DB credentials and `JWT_SECRET=SECRET1234567890`.

2.  **User Management Page**:
    - Navigate to the User Management page.
    - Open the **Network** tab in your browser's Developer Tools.
    - Refresh the page to trigger the `GET /api/users.php` request.
    - **Response**: Check the response body. It should be a JSON object with a `payload` field containing a JWT string.

3.  **Login**:
    - Try to login.
    - **Request**: Check the request payload. It should be `{ payload: "..." }`.
    - **Response**: The response should also be a JWT encoded payload.

4.  **Database Migration**:
    - Run `php backend/migrations/migrate.php`.
    - **Expected Output**: "Connected to MySQL server successfully", "Database ... created or already exists", "Tables and data migrated successfully".

5.  **Directory Security**:
    - Navigate to `http://localhost:8000/backend/` or `http://localhost:8000/backend/api/` in your browser.
    - **Expected Output**: You should see an "Access Denied" page instead of a list of files.

> [!NOTE]
> The secret key is now managed via `.env` files. Ensure `VITE_JWT_SECRET` (Frontend) and `JWT_SECRET` (Backend) match.

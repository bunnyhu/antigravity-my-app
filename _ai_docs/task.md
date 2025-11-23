# Project Task List

## Phase 1: Initial Setup & Core Features
- [x] Initialize Project Structure (React Frontend + PHP Backend)
- [x] Setup Database Schema (MySQL users table)
- [x] Implement Backend API
    - [x] Database connection (PDO)
    - [x] User Registration endpoint
    - [x] Login endpoint with JWT generation
    - [x] User Management endpoints (List, Delete, Update Role)
- [x] Implement Frontend Application
    - [x] Setup Vite + React
    - [x] Create UI Components (Forms, Buttons, Layout)
    - [x] Implement Authentication Context
    - [x] Create Pages (Login, Register, Dashboard, Admin)
- [x] Implement Role-Based Access Control (RBAC)

## Phase 2: Bug Fixes & Refinements
- [x] Fix CSS `background-clip` compatibility issue

## Phase 3: Security Enhancements (JWT Payload Encoding)
- [x] Analyze current frontend API calls and backend handling <!-- id: 0 -->
- [x] Create Implementation Plan <!-- id: 1 -->
- [x] Implement JWT encoding/decoding on Frontend (Web Crypto API) <!-- id: 2 -->
- [x] Implement JWT encoding/decoding on Backend (Custom JWTUtils) <!-- id: 3 -->
- [x] Verify communication works with JWT payloads <!-- id: 4 -->
- [x] Fix login endpoint to handle JWT payloads <!-- id: 5 -->

## Phase 4: Configuration Management
- [x] Move Frontend config to .env (`VITE_JWT_SECRET`) <!-- id: 6 -->
- [x] Move Backend config to .env (`DB_CREDS`, `JWT_SECRET`) <!-- id: 7 -->
- [x] Verify application with env vars <!-- id: 8 -->

## Phase 5: Documentation
- [x] Create README.md with project info and usage guide
- [x] Archive AI documentation to `_ai_docs` folder

## Phase 6: Database Migration
- [x] Create migration script (`migrate.php`) to setup DB and tables
- [x] Update SQL schema for idempotency (`INSERT IGNORE`)
- [x] Verify migration script execution

# Development Notes: MyCities Server Admin

## Project Overview
This document tracks the development progress, decisions, and active tasks for the MyCities Server Admin (Laravel) project.

## Active Feature: Date-to-Date Billing & Site Login
**Goal:** Allow sites to be configured for "Date-to-Date" billing instead of just "Monthly", and provide specific login credentials for site-level access.

### Current Status (2025-12-01)
*   **Pull Request:** #41 (Draft) - "Add Date-to-Date Billing and Site Specific Login features".
*   **Status:** Pending Review.
*   **Changes Implemented in PR:**
    *   **Database:** Added `billing_type` (string), `site_username`, `site_password` to `sites` table.
    *   **Models:** Updated `Site` model to include new fields in `$fillable` and hide `site_password`.
    *   **Controller:** Updated `AdminController` (`createSite`, `editSite`) to handle new fields and password hashing.
    *   **Views:** Updated `create_site.blade.php` and `edit_site.blade.php` with new form inputs.

### Next Steps
1.  Review code in PR #41.
2.  Merge PR #41 if satisfactory.
3.  Clean up duplicate PRs (#39, #40).
4.  Move to `MyCities-Vue-Quasar` repository for frontend integration if required.

## Decision Log
*   **AI Workflow:** Using `DEV_NOTES.md` as the "context anchor" to preserve history across chat sessions.
*   **Billing Logic:** Storing `billing_type` directly on the `sites` table.

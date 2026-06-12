# Read-Only Reviewer Account

This document covers everything you need to know about the read-only reviewer account — what it can and cannot do, how the system enforces those restrictions, how to set it up, and how to test every control.

---

## 1. Credentials

| Field    | Value                        |
|----------|------------------------------|
| Email    | `reviewer@example.com`       |
| Password | `reviewer@example.` *(initial — **must** be changed before sharing)* |
| Role     | `reviewer` (read-only)       |

> **Security notice:** The seeder ships with a placeholder password. Change it immediately after running the seeder with the command in §3.

---

## 2. What the Reviewer Account Can Access

### Frontend (Advertiser Portal)

| Page | Route |
|------|-------|
| Advertiser dashboard (my listings) | `/my-listings` |
| Sample advertiser profile (edit view) | `/my-profiles` |
| Profile details step 1 | `/my-profiles/1/edit` |
| Credit wallet / balance | `/my-account` |
| Credit purchase page | `/purchase-credit` |
| Credit transaction history | `/credit-history` |
| Purchase transaction history | `/purchase-history` |
| Terms & policies | `/terms`, `/privacy`, `/age-consent-policy` |
| Public profile listing (frontend) | `/` and any listing page |

### Admin Panel (`/admin`)

The reviewer can log into `/admin/login` with the same credentials and view:

| Section | What they see |
|---------|--------------|
| Dashboard | Stats overview & charts |
| Provider listings | List & detail (read-only) |
| Listing approval queue | View pending/approved/rejected status |
| Moderation status | View reported content |
| Terms / Policy pages | View page content |
| Pricing pages | View credit package setup |
| FAQ pages | View FAQ content |

A prominent **amber banner** reading *"🔒 Read-Only Reviewer Mode — You have view-only access. All modifications are disabled."* is pinned to the top of every admin page.

---

## 3. What the Reviewer Account Cannot Do

### Frontend — All state-changing requests (POST / PUT / PATCH / DELETE) are blocked and return HTTP 403

- Edit or delete any profile
- Create a new profile
- Change account details (name, email, password, mobile)
- Upload or delete photos/videos
- Toggle online/availability status
- Purchase credits (checkout is blocked)
- Export any data

### Admin Panel — Access is denied (HTTP 403) for

- **Account Management** — no access to real user accounts/PII
- **Purchase Transactions** — financial data is hidden
- **Credit Logs** — hidden
- **Login Logs** — hidden
- **SMS / Email logs** — hidden
- **System Settings** (SMTP, S3, Twilio, reCAPTCHA)
- **User Reports / Purchase Complaints** — hidden
- **All create / edit / delete actions** in every resource — blocked globally via the Laravel Gate

---

## 4. How It Works (Architecture)

### 4.1 Middleware — `ReviewerMode`

`app/Http/Middleware/ReviewerMode.php`

Applied globally to **every web request** via `bootstrap/app.php`. When the authenticated user has `role = reviewer`:

1. Shares `$reviewerMode = true` with all Blade views (used to hide/disable buttons in the UI).
2. Passes admin-panel requests (`/admin/*`) straight through — Filament's own Gate/Policy layer handles access control there.
3. Blocks any `POST | PUT | PATCH | DELETE` request that is not the `logout` route, returning:
   - **JSON API callers:** `403 { "message": "Read-only access: …" }`
   - **Browser requests:** renders `resources/views/errors/reviewer-readonly.blade.php` (styled 403 page)
4. Logs every request (allowed or blocked) to `storage/logs/reviewer-access.log` with user ID, IP, agent, route, and block reason.

### 4.2 Gate — `AuthServiceProvider`

`app/Providers/AuthServiceProvider.php`

A `Gate::before` hook intercepts every ability check site-wide:

```
create | update | delete | forceDelete | restore | replicate
```

All return `false` for the reviewer role. This is what prevents Filament's *Create / Edit / Delete / Restore* buttons from appearing or functioning, without touching individual resource files.

### 4.3 Admin Panel — `AdminPanelProvider`

`app/Providers/Filament/AdminPanelProvider.php`

- `canAccessPanel()` on the `User` model returns `true` for `ROLE_REVIEWER`, so the reviewer can log in.
- The amber read-only banner is injected at `panels::body.start` for all reviewer sessions.
- Individual Filament resources that expose sensitive data override `canAccess()` to return `false` for the reviewer:
  - `AccountResource` (real user PII)
  - `PurchaseTransactionResource` (financial data)
  - `CreditLogResource`
  - `LoginLogResource`
  - `SmtpSettingResource`, `TwilioSettingResource`, `S3BucketSettingResource`, `GoogleRecaptchaSettingResource`
  - `SmsLogResource`, `EmailLogResource`, `RecaptchaLogResource`
  - `UserReportResource`, `PurchaseComplaintResource`

### 4.4 Frontend Views

All views receive `$reviewerMode` (boolean). Views use it to:

- Show a **"Read-Only Reviewer Mode"** banner in the site header.
- Disable or relabel action buttons (*"Create disabled (read-only)"*).
- Hide payment/checkout buttons on the purchase-credit page.
- Hide dispute/complaint actions on the purchase-history page.

---

## 5. Setup

### 5.1 Create the Account (first time)

```bash
php artisan db:seed --class=ReviewerAccountSeeder
```

This creates:
- The `reviewer@example.com` user account with role `reviewer`
- A demo provider profile (`Demo Profile (Reviewer)`) marked `is_blocked = true` so it never appears in public listings
- Sample credit transaction history (8 entries, ~AUD $50 balance)
- Sample purchase transactions (2 entries)

### 5.2 Change the Password

```bash
php artisan reviewer:reset-password
```

You will be prompted to enter and confirm a new password (minimum 12 characters). To set it non-interactively:

```bash
php artisan reviewer:reset-password --******
```

> Re-run this command any time you need to rotate the reviewer password.

### 5.3 Re-seed Without Losing Data

The seeder uses `firstOrCreate` on every record, so running it again is safe. It will not duplicate the account, profile, or transaction history.

---

## 6. Testing the Reviewer Account

### 6.1 Automated Tests

The full test suite for this feature lives in:

```
tests/Feature/Middleware/ReviewerModeTest.php
```

Run it with:

```bash
php artisan test tests/Feature/Middleware/ReviewerModeTest.php
```

| Test | What it verifies |
|------|-----------------|
| `test_reviewer_can_view_dashboard_pages_in_read_only_mode` | Dashboard loads (HTTP 200) and contains the reviewer banner |
| `test_reviewer_mutation_requests_are_blocked` | POST to create a profile returns 403; no new profile is created |
| `test_reviewer_cannot_update_account_details` | PUT to update account returns 403 |
| `test_reviewer_cannot_initiate_credit_purchase_checkout` | POST to checkout returns 403 |
| `test_reviewer_cannot_update_profile_settings` | POST to short-url update returns 403 |
| `test_reviewer_without_admin_guard_session_is_redirected_to_admin_login` | Web-guard session redirected to /admin/login |
| `test_reviewer_authenticated_on_admin_guard_can_access_admin_panel` | Admin guard session gets HTTP 200 on /admin |
| `test_reviewer_cannot_access_account_management_in_admin_panel` | /admin/account-management/account returns 403 |
| `test_reviewer_cannot_access_purchase_transactions_in_admin_panel` | /admin/purchase-transactions returns 403 |
| `test_reviewer_can_still_logout` | POST logout succeeds and session is cleared |

### 6.2 Manual Testing Checklist

Use the checklist below to manually verify every control before sharing credentials with a payment processor.

#### Step 1 — Seed the account

```bash
php artisan db:seed --class=ReviewerAccountSeeder
php artisan reviewer:reset-password
```

#### Step 2 — Log in to the frontend

1. Go to `/signin`.
2. Sign in with `reviewer@example.com` and your new password.
3. **Expected:** You are redirected to the advertiser dashboard.
4. **Expected:** An amber *"Read-Only Reviewer Mode"* banner appears at the top of the page.

#### Step 3 — Verify read access on frontend pages

| Page to visit | Expected result |
|--------------|----------------|
| `/my-listings` | Loads with demo listing visible |
| `/my-profiles` | Loads; *"Create New Profile"* button is labelled *"Create disabled (read-only)"* and is disabled |
| `/my-account` | Loads; edit buttons and save actions are hidden or disabled |
| `/purchase-credit` | Loads; payment form is not shown; Stripe checkout button is absent |
| `/credit-history` | Loads with sample transaction rows |
| `/purchase-history` | Loads with sample purchase rows; dispute buttons are hidden |
| Any public listing page | Loads normally |

#### Step 4 — Verify mutations are blocked on frontend

Try each of the following; every one must return the **"Read-Only Access — Action Not Permitted"** error page (HTTP 403):

| Action to attempt | How to trigger |
|------------------|----------------|
| Create profile | Submit the create-profile form |
| Edit account details | Submit the account update form |
| Upload a photo | Submit the photo upload form |
| Initiate credit purchase | Click any *"Buy Credits"* checkout button |

#### Step 5 — Log in to the admin panel

1. Go to `/admin/login`.
2. Sign in with `reviewer@example.com` and your password.
3. **Expected:** Admin dashboard loads.
4. **Expected:** Amber *"🔒 Read-Only Reviewer Mode"* banner is sticky at the top of every admin page.

#### Step 6 — Verify read access in admin panel

| Section to visit | Expected result |
|-----------------|----------------|
| `/admin` (dashboard) | Loads with stats & charts |
| `/admin/provider-management/provider` (listings) | Loads with listing rows; no Create/Edit/Delete buttons |
| `/admin/content-management/provider-listing` | Loads; approval status visible; no action buttons |
| `/admin/content-management/pricing-page` | Loads; content visible; no edit controls |

#### Step 7 — Verify blocked sections in admin panel

Each of the following must return **HTTP 403 Forbidden**:

| URL | Why blocked |
|-----|-------------|
| `/admin/account-management/account` | Real user PII |
| `/admin/purchase-transactions` | Financial data |
| `/admin/financial/credit-logs` | Financial data |
| `/admin/system/smtp-settings` | System settings |
| `/admin/system/twilio-settings` | System credentials |
| `/admin/system/s3-bucket-settings` | System credentials |
| `/admin/support/user-reports` | User-submitted reports |
| `/admin/support/purchase-complaints` | Dispute records |

#### Step 8 — Verify audit log

After completing the above steps, check that every request was recorded:

```bash
tail -n 50 storage/logs/reviewer-access.log
```

Each entry should include `user_id`, `email`, `method`, `url`, `ip`, `route`, `blocked` (true/false), and `reason`.

#### Step 9 — Logout

Click **Logout** from the frontend or admin panel.  
**Expected:** Session is cleared; you are redirected to the home page.

---

## 7. Rotating or Revoking the Reviewer Account

### Change the password

```bash
php artisan reviewer:reset-password
```

### Disable the account temporarily

```bash
# In the admin panel → Account Management → find reviewer@example.com → Block account
# Or via Tinker:
php artisan tinker
>>> \App\Models\User::where('email','reviewer@example.com')->update(['is_blocked' => true]);
```

### Re-enable the account

```bash
php artisan tinker
>>> \App\Models\User::where('email','reviewer@example.com')->update(['is_blocked' => false]);
```

---

## 8. Files Reference

| File | Purpose |
|------|---------|
| `app/Http/Middleware/ReviewerMode.php` | Core middleware — blocks mutations, shares `$reviewerMode`, logs access |
| `app/Providers/AuthServiceProvider.php` | Gate::before — denies all mutating abilities for reviewer |
| `app/Providers/Filament/AdminPanelProvider.php` | Admin banner injection; reviewer granted admin panel entry |
| `app/Models/User.php` | `ROLE_REVIEWER` constant, `isReviewer()` helper, `canAccessPanel()` |
| `database/seeders/ReviewerAccountSeeder.php` | Creates account, demo profile, sample credits & purchases |
| `app/Console/Commands/ResetReviewerPassword.php` | `php artisan reviewer:reset-password` |
| `resources/views/errors/reviewer-readonly.blade.php` | Styled 403 page shown when a mutation is attempted |
| `config/logging.php` — channel `reviewer` | Writes to `storage/logs/reviewer-access.log`, retained 90 days |
| `tests/Feature/Middleware/ReviewerModeTest.php` | Automated test suite for all reviewer controls |

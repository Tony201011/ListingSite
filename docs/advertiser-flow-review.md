# Advertiser Flow Review Guide

## Overview

Point 4 of the processor review checklist — **Make the advertiser flow reviewable** — is fully implemented.
A dedicated test advertiser account exists that lets any reviewer walk through the complete advertiser journey using sample data only.
No real advertiser data is exposed.

---

## Test Advertiser Credentials

| Field    | Value                          |
|----------|--------------------------------|
| Email    | `test-advertiser@example.com`  |
| Password | `Advertiser@12345`             |
| Role     | `test_advertiser`              |

The account is seeded by `database/seeders/TestAdvertiserAccountSeeder.php` and is called automatically from `DatabaseSeeder`.
All profile fields contain placeholder/sample text. The profile is marked `is_blocked = true` so it never appears in public listings.

---

## Seeder

```
php artisan db:seed --class=TestAdvertiserAccountSeeder
```

Or as part of the full seed:

```
php artisan db:seed
```

---

## Reviewer Info Panel

After logging in as the test advertiser, every page shows a **yellow "Advertiser review flow (sample data only)"** banner at the top of the dashboard (`/my-profile`). That panel contains direct links to every page listed below.

---

## Pages Available for Review

### 1. Advertiser Dashboard
- **URL:** `/my-profile`
- **Route name:** `my-profile`
- **What to check:** Profile summary card, online/offline toggle, credit balance widget, quick links to all sub-sections.

### 2. Profile Creation Page
- **URL:** `/my-profiles`
- **Route name:** `profiles.index`
- **What to check:** Create a new profile form, profile switcher, per-profile status badges.

### 3. Profile Edit Page
- **URL:** `/edit-profile`
- **Route name:** `edit-profile`
- **What to check:** Full profile text editor, category selectors, contact preferences, location fields.

### 4. Profile Status Page
- **URL:** `/status`
- **Route name:** `status`
- **What to check:** Three-tab status panel — Online Status, Profile Visibility, and Available Now toggles (rendered read-only for reviewer accounts).

### 5. Credit Balance / Wallet Page
- **URL:** `/profile-spending-history`
- **Route name:** `profile-spending-history`
- **What to check:** Summary cards for Total Spent, Daily Fees, Boost Spend, and current Wallet Balance. Filterable transaction list.

### 6. Credit Purchase Page
- **URL:** `/purchase-credit`
- **Route name:** `purchase-credit`
- **What to check:** Credit package selector, pricing table, balance display, Stripe payment flow (submission is blocked for reviewer accounts).

### 7. Credit Transaction History Page
- **URL:** `/credit-history`
- **Route name:** `credit-history`
- **What to check:** Itemised log of all debit/credit events — daily listing fees, boost charges, purchases.

### 8. Listing Visibility Status
- **URL:** `/my-listings`
- **Route name:** `my-listings`
- **What to check:** Per-listing status tabs (All / Online / Expiring / Expired / Offline), live/offline badge, expiry countdown.

### 9. Pause / Resume Listing Option
- **URL:** `/status` → **Profile Visibility** tab  
  Also accessible from `/hide-show-profile`
- **Route name:** `hide-show-profile`
- **What to check:** Toggle to hide or show the profile from the public listing without deleting it. The dashboard also links here as "Pause / resume options".

### 10. Upgrade / Featured / Boost Options
- **URL:** `/featured-listing`
- **Route name:** `featured`
- **What to check:** Four ad-placement tiers available for purchase:
  - 🏆 **Home Page Banner** — national banner strip at the top of the home page
  - 🌟 **Home Page Featured** — featured slot in the home listing grid
  - 📍 **Local Banner** — state-specific banner strip on the local area page
  - ⭐ **Featured Badge** — featured star badge on the profile card

---

## Reviewer Account Protections

The `ReviewerMode` middleware (`app/Http/Middleware/ReviewerMode.php`) is applied to the **reviewer** role.
It does **not** apply to the `test_advertiser` role — the test advertiser account is a fully functional account that can demonstrate the UI without restriction.

To prevent accidental writes during a review session, the reviewer should log in with the **reviewer** account credentials (separate from the test advertiser). When the reviewer logs in as `test_advertiser`, they see the real advertiser UI. When logged in as `reviewer`, all mutating requests (POST / PUT / PATCH / DELETE) are blocked and return a read-only error page, except for logout.

---

## Key Source Files

| Purpose | File |
|---|---|
| Test advertiser seeder | `database/seeders/TestAdvertiserAccountSeeder.php` |
| Reviewer mode middleware | `app/Http/Middleware/ReviewerMode.php` |
| Reviewer info panel (dashboard) | `resources/views/profile/my-profile-1.blade.php` |
| Profile creation view | `resources/views/profile/my-profiles.blade.php` |
| Profile edit view | `resources/views/profile/my-profile-1.blade.php`, `my-profile-2.blade.php` |
| Status & visibility view | `resources/views/profile/status-tabs.blade.php` |
| Wallet / spending history view | `resources/views/profile/spending-history.blade.php` |
| Credit purchase view | `resources/views/subscription/purchase-credit.blade.php` |
| Credit transaction history view | `resources/views/subscription/credit-history.blade.php` |
| Listings visibility view | `resources/views/profile/my-listings.blade.php` |
| Featured / boost view | `resources/views/profile/featured.blade.php` |
| Featured controller | `app/Http/Controllers/Profile/FeaturedController.php` |
| Provider profile policy | `app/Policies/ProviderProfilePolicy.php` |

---

## Checklist Summary

| Requirement | Status | URL |
|---|---|---|
| Advertiser dashboard | ✅ Done | `/my-profile` |
| Profile creation/edit page | ✅ Done | `/my-profiles`, `/edit-profile` |
| Profile status page | ✅ Done | `/status` |
| Credit balance / wallet page | ✅ Done | `/profile-spending-history` |
| Credit purchase page | ✅ Done | `/purchase-credit` |
| Credit transaction history page | ✅ Done | `/credit-history` |
| Listing visibility status | ✅ Done | `/my-listings` |
| Pause / resume listing option | ✅ Done | `/status` (Profile Visibility tab) |
| Upgrade / featured / boost options | ✅ Done | `/featured-listing` |
| Sample data only — no real advertiser data | ✅ Done | Seeder uses placeholder values; profile is blocked from public listings |

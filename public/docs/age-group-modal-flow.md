# Age Group Modal Flow

## Overview

The admin **Age Group** screen is powered by Filament and uses slide-over modals for create, edit, and delete actions.

## Entry Point

- Admin panel route slug: `age-group`
- Resource: `App\Filament\Resources\AgeGroupCategories\AgeGroupCategoryResource`
- Page: `App\Filament\Resources\AgeGroupCategories\Pages\ManageAgeGroupCategories`

## Modal Behavior

Global Filament action config in `AppServiceProvider::configureAdminSlideOverActions()` applies `->slideOver()` to actions in the admin panel, so the Age Group actions open as right-side slide-over modals.

## Create Flow

1. Admin opens **Categories → Age Group**.
2. Admin clicks **Create** (header action).
3. Create modal opens with fields: `name`, `slug`, `sort_order`, `is_active` (+ hidden `parent_id`, `website_type`).
4. If slug is blank, it auto-generates from the name on blur.
5. On submit:
   - `parent_id` is forced to the **Age Group** parent category id.
   - `website_type` is forced to `adult`.
   - Parent category (`slug = age-group`) is auto-created if missing.
6. Record is saved as a child under the Age Group parent and appears in the table.

## Edit Flow

1. Admin clicks **Edit** on a table row.
2. Edit slide-over modal opens with the same schema.
3. On save, `parent_id` and `website_type` are re-forced to keep records scoped correctly.
4. Updated record remains in the Age Group list (sorted by `sort_order`, then `id`).

## Delete Flow

1. Admin clicks **Delete** on a table row.
2. Confirmation modal appears (`requiresConfirmation()`).
3. After confirm, record is removed and no longer shown in the table.

## Data Scope and Safeguards

- Table only lists categories where `parent_id` matches the Age Group parent.
- If the parent does not exist during query, the list is intentionally empty.
- `slug` must be unique (ignores current record on edit).

## Source Files

- `app/Filament/Resources/AgeGroupCategories/AgeGroupCategoryResource.php`
- `app/Filament/Resources/AgeGroupCategories/Pages/ManageAgeGroupCategories.php`
- `app/Providers/AppServiceProvider.php`

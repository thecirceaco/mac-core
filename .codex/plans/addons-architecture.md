# Add-ons Architecture

## Goal

Leave MAC Core ready for future companion plugins without building a heavy add-on framework now.

## Current Approach

Use lightweight extension seams instead of discovery or SDK infrastructure:

- `mac_core_services`
- `mac_core_admin_tabs`
- `mac_core_settings_sections`

These are enough for future add-ons to:

- register their own services
- add admin views
- add settings modules/fields

## Future Add-on Naming

- plugin slugs should follow `mac-{addon}`
- persistent keys should follow `mac_{addon}_*`
- add-on settings added to `mac_core_settings` should live in their own module namespace

Example:

- add-on slug: `mac-seo`
- option prefix: `mac_seo_*`
- settings module inside `mac_core_settings`: `seo`

## What MAC Core Should Not Do Yet

- no plugin discovery UI
- no add-on marketplace UX
- no compatibility matrix or semantic dependency resolver
- no separate add-on SDK package

## Future Hardening Direction

If add-ons become real products, document:

- minimum MAC Core version requirements
- module namespace ownership
- settings schema merge rules
- migration ownership per add-on
- capability model for add-on admin tabs and actions

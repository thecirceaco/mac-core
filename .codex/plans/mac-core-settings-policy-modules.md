# MAC Core Settings And Policy Modules

## Goal

Keep the MAC Core settings-backed product surface clear and durable without overbuilding.

## Admin Structure

- Top-level page: `page=mac-core`
- Base route redirects to `tab=settings` until a real Welcome screen is needed.
- Explicit views:
  - `tab=settings`
  - `tab=helpers`
  - `tab=license`
  - `tab=support`
- `tab=support` should provide the current support path:
  - email link: `mihai@circea.co`
  - docs link: `https://docs.circea.co/`

Keep storage module-based, not tab-based. `core` and `media` render under `Settings`, while `utils` renders under `Helpers`.

## Settings Storage

- option name: `mac_core_settings`
- storage format: one WordPress option array
- structure: nested by module

Current modules:

- `core`
- `media`
- `utils`

Future add-ons should add their own module namespace through `mac_core_settings_sections`.

## Current Policy Coverage

### Core

- developer branding
- comments policy
- frontend admin bar policy
- automatic updates policy
- Site Health visibility
- dashboard cleanup
- excerpt length
- last login column

### Media

- custom image sizes and widths
- font uploads
- image quality override
- blocked video uploads
- removed stock image sizes

### Utils

- wrapper loading gate
- datetime formatting helper
- price formatting helper
- post type and taxonomy label helpers
- post terms helper
- array counting helper
- plugin and theme status helpers

## Implementation Rule

Each policy remains a service class, but the service reads from the settings repository instead of hardcoded class constants.

## Post-Settings Next Step

After this layer is stable, add lifecycle/migrations before any release that changes the structure of `mac_core_settings`.

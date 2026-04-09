# MAC Core Settings And Policy Modules

## Goal

Turn the current hardcoded Core and Media behavior into a real product surface without overbuilding.

## Admin Structure

- Top-level page: `page=mac-core`
- Default base page: Welcome
- Secondary views:
  - `tab=licensing`
  - `tab=settings`

Settings stay inside one `Settings` view with module sections, not many top-level tabs.

## Settings Storage

- option name: `mac_core_settings`
- storage format: one WordPress option array
- structure: nested by module

Current modules:

- `core`
- `media`

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

## Implementation Rule

Each policy remains a service class, but the service reads from the settings repository instead of hardcoded class constants.

## Post-Settings Next Step

After this layer is stable, add lifecycle/migrations before any release that changes the structure of `mac_core_settings`.

# SureCart Licensing Skill

Use this skill when maintaining MAC Core's SureCart licensing integration.

## Rules

- Keep MAC Core's own integration OOP and under the `MacCore` namespace.
- Do not initialize SureCart licensing from `mac-core.php`; use a service registered by `Kernel`.
- Keep existing MAC Core services available even when no license is active.
- Keep `DisableAutoUpdates` unchanged unless the user explicitly asks to revisit update policy.
- Treat the SureCart SDK under `licensing/` as vendored third-party code.
- Do not edit SDK files unless intentionally applying a documented vendor patch.
- Keep SDK provenance in `licensing/PROVENANCE.md`.

## Configuration

- Use `MAC_CORE_SURECART_PUBLIC_TOKEN` for the default public token.
- Always pass the token through `mac_core_surecart_public_token`.
- If the token is blank, skip SDK initialization and show an admin notice for users with `manage_options`.
- Use textdomain `mac-core`.
- Use top-level menu title `MAC Core`, page title `MAC Core License`, menu slug `mac-core-license`, and capability `manage_options`.

## Release Metadata

- Keep `release.json` slug set to `mac-core`.
- Keep `release.json` version and compatibility metadata synchronized with `mac-core.php`, `inc/constants.php`, and `readme.txt`.
- Release ZIPs must include `release.json` and `licensing/`.
- Release ZIPs must exclude `.codex/`, `AGENTS.md`, Composer/PHPCS/PHPUnit dev files, tests, caches, and local editor/dev-environment folders.

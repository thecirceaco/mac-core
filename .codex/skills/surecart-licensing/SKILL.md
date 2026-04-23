# SureCart Licensing Skill

Use this skill when maintaining MAC Core's SureCart licensing integration.

## Rules

- Keep MAC Core's own integration OOP and under the `MacCore` namespace.
- Do not initialize SureCart licensing from `mac-core.php`; use a service registered by `Kernel`.
- Keep existing MAC Core services available even when no license is active.
- Keep `DisableAutoUpdates` unchanged unless the user explicitly asks to revisit update policy.
- Treat the SureCart SDK under `inc/Vendor/SureCart/Licensing/` as vendored third-party code.
- MAC Core ships the SDK under the vendor-prefixed runtime namespace `MacCore\Vendor\SureCart\Licensing`.
- Do not edit SDK files unless intentionally applying a documented vendor patch.
- Keep SDK provenance in `.codex/context/surecart-sdk-provenance.md`.

## Configuration

- Use `MAC_CORE_SURECART_PUBLIC_TOKEN` for the default public token.
- Always pass the token through `mac_core_surecart_public_token`.
- If the token is blank, skip SDK initialization and show an admin notice for users with `manage_options`.
- Use textdomain `mac-core`.
- Use top-level menu title `MAC Core`, page title `MAC Core License`, menu slug `mac-core`, and capability `manage_options`.

## Release Metadata

- Keep `release.json` slug set to `mac-core`.
- Keep `release.json` version and compatibility metadata synchronized with `mac-core.php`, `inc/constants.php`, and `readme.txt`.
- Release ZIPs must include `release.json` and `inc/Vendor/SureCart/Licensing/`.
- Release ZIPs must publish a `.sha256` checksum file and a provenance JSON alongside the ZIP artifact.
- Release ZIPs must exclude `.codex/`, `AGENTS.md`, Composer/PHPCS/PHPUnit dev files, tests, caches, and local editor/dev-environment folders.

## Before Finishing

- If a licensing or update-flow change affects builder-visible behavior, release process, or local agent guidance, also run `D:\business\projects\mac-core\.codex\skills\ai-context-sync\SKILL.md`.
- Pair that with `D:\business\projects\mac-core\.codex\skills\mac-docs-sync\SKILL.md` when the change is public-facing.

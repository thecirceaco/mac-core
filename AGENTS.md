# MAC Core Agent Notes

These instructions apply to the entire `mac-core` repository.

`mac-core` is an installable WordPress plugin. Keep the current source tree limited to this root `AGENTS.md` AI entrypoint; do not restore repo-local `.codex/` context or skills unless the human explicitly asks for that exception.

Use `thecirceaco/mac` for shared governance and `thecirceaco/mac-codex` for installable WordPress product workflow guidance and compact repo profiles.

## Guardrails

- Work on `dev`.
- Do not merge, tag, release, upload a SureCart ZIP, bump versions, or add public changelog entries without fresh explicit approval for `mac-core`.
- AI-only cleanup stays on `dev` and does not trigger a product release.
- Preserve the OOP plugin architecture under the `MacCore` namespace.
- Keep `mac-core.php` as a minimal bootstrap that loads constants, autoloading, and `\MacCore\Kernel::boot()`.
- Keep hook-registering behavior in service classes implementing `MacCore\Contracts\Service`, registered from `src/Kernel.php`.
- Treat `inc/Vendor/SureCart/Licensing/` as vendored runtime code unless a vendor patch is explicitly requested.

Before editing, check `git status` and preserve user changes.

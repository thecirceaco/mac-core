# MAC Core

Company standard WordPress functionality plugin.

## Code structure

- `mac-core.php` is a minimal bootstrap. It loads `inc/constants.php` and `inc/autoload.php`, then boots `\MacCore\Kernel` at `plugins_loaded` priority 0, or right away if that action has already run, so add-ons that WordPress loads after MAC Core can still add services and settings sections.
- The classes live in `src/`, under the `MacCore` namespace. Every class that registers hooks implements `MacCore\Contracts\Service` and is listed in `src/Kernel.php`; add-ons add theirs through the `mac_core_services` filter.
- `inc/Vendor/SureCart/Licensing/` is a vendored copy of the SureCart licensing SDK. Its `README.md` lists the local changes and how to update the copy.

## Workflow

- `main` is the trunk and default work branch.
- Use short-lived branches only when a change needs isolation.
- Releases are created from version tags on `main`.
- Maintenance commits on `main` are not product releases by themselves.

## License

GPL v3. See the `LICENSE` file for details.

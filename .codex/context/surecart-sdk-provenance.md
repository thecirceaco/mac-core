# SureCart WordPress SDK Provenance

The bundled SDK files are vendored from the SureCart WordPress SDK.

- Source repository: https://github.com/surecart/wordpress-sdk
- Pinned tag: `v1.1.2`
- Runtime files copied: `src/Client.php`, `src/License.php`, `src/Activation.php`, `src/Settings.php`, and `src/Updater.php`
- Metadata copied: `README.md` and `composer.json`

The SDK tag's `composer.json` declares license `MIT`. During integration, the GitHub contents API did not expose a separate `LICENSE` file at tag `v1.1.2`.

MAC Core applies a deliberate local vendor patch to isolate the SDK under the `MacCore\Vendor\SureCart\Licensing` namespace. This avoids collisions with other plugins that may also ship the SureCart SDK under its original `SureCart\Licensing` namespace.

The local patch intentionally does not change:

- SureCart API/filter hook names such as `surecart_licensing_endpoint`
- option keys/transient keys used by the SDK
- release JSON shape or package URL handling

Treat files under `licensing/src/` as vendored third-party code. Do not edit them unless applying a deliberate, documented vendor patch.

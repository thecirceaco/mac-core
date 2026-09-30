# SureCart licensing SDK (vendored)

Bundled copy of the SureCart WordPress SDK. MAC Core uses it for license activation and update checks. `src/Licensing/LicensingService.php` loads `Client.php` on `init`.

- Upstream: <https://github.com/surecart/wordpress-sdk>
- Version: `v1.2.1` (tagged 2026-09-16)
- Commit: `c24515df17bc184686ca3c86761ce0541f60d7b5`
- Files: upstream `src/Activation.php`, `src/Client.php`, `src/License.php`, `src/Settings.php`, `src/Updater.php`
- License: MIT, as declared in the upstream `composer.json`

## Local changes

These are the only differences from upstream `src/`:

1. **Namespace.** Each file declares `namespace MacCore\Vendor\SureCart\Licensing;` instead of `namespace SureCart\Licensing;`, so this copy can't clash with another plugin's copy of the SDK. Only the `namespace` line changes. Docblocks still mention `SureCart\Licensing`.
2. **`register_menu` in `Settings::add_page()`** (`Settings.php`, lines 68-75). New argument, default `true`. MAC Core passes `false` because it shows the license form in its own License tab, so the SDK must not add a menu page. The same patch gives `deactivated_redirect` a `null` default.

```diff
 				'activated_redirect' => null,
+				'deactivated_redirect' => null,
 				'parent_slug'        => '',
+				'register_menu'      => true,
 			)
 		);
-		add_action( 'admin_menu', array( $this, 'admin_menu' ), 99 );
+		if ( ! empty( $this->menu_args['register_menu'] ) ) {
+			add_action( 'admin_menu', array( $this, 'admin_menu' ), 99 );
+		}
```

3. **Prefixed global names.** The four global names the SDK reads get MAC Core's prefix: `MAC_CORE_` on the constant, `mac_core_` on the filters. Upstream, every bundled copy of the SDK reads the same names, so a value set in `wp-config.php` or a filter for another plugin's copy would also change MAC Core's licensing, and the other way around. Only the names change.

| Upstream name | MAC Core name | Kind | Where | Effect |
| --- | --- | --- | --- | --- |
| `SURECART_LICENSING_ENDPOINT` | `MAC_CORE_SURECART_LICENSING_ENDPOINT` | constant | `Client::endpoint()`, `Client.php:212-213` | Replaces the API base URL for every licensing request. |
| `surecart_licensing_endpoint` | `mac_core_surecart_licensing_endpoint` | filter | `Client::endpoint()`, `Client.php:217` | Same, when the constant isn't defined. Default `https://api.surecart.com`. |
| `surecart_client_license_form_action` | `mac_core_surecart_client_license_form_action` | filter | `Settings::form_action_url()`, `Settings.php:82` | Sets the license form's `action` URL (`Settings.php:226`). |
| `surecart_licensing_is_local` | `mac_core_surecart_licensing_is_local` | filter | `Client::is_local_server()`, `Client.php:332` | None today. Nothing in the SDK or MAC Core calls `is_local_server()`. |

MAC Core itself doesn't define or hook any of them. Another MAC plugin that bundles the SDK prefixes the same names with its own prefix, for example `MAC_MEMBERS_SURECART_LICENSING_ENDPOINT` and `mac_members_surecart_licensing_endpoint` in MAC Members.

The option and transient keys the SDK builds from the plugin slug, like `surecart_<md5 of the slug>_version_info`, stay as they are: they already differ per plugin, and new names would lose what's stored under the old ones.

4. **Redirect after the license form** (`Settings::redirect()`, `Settings.php`, line 394). Upstream prints `esc_url( $url )` inside a `<script>`. For HTML, `esc_url()` turns `&` into `&#038;`, which a script doesn't decode, so the `tab=license` of the redirect ended up in the URL fragment: after activating or deactivating a license, the admin landed on the Settings tab instead of the License tab. The copy prints `wp_json_encode( esc_url_raw( $url ) )`, a JavaScript string of the raw URL; `esc_url_raw()` still drops the characters that could close the script. MAC Members' copy has the same change.

```diff
-			window.location.assign("<?php echo esc_url( $url ); ?>");
+			window.location.assign(<?php echo wp_json_encode( esc_url_raw( $url ) ); ?>);
```

## Updating

Keep this copy in step with MAC Members': update both to the same SDK version, with the same local changes.

1. Clone the upstream repository and check out the new tag.
2. Copy its `src/*.php` over the files here and reapply the local changes above.
3. Run `diff -u <upstream>/src/<File>.php <File>.php` for each file. Only the local changes should show.
4. Update the version and commit in this file.
5. Run `composer test`. `tests/Unit/SureCartSdkTest.php` runs this copy against stubbed SureCart API responses. `tests/Unit/PrefixedSureCartSdkTest.php` fails if a file keeps the upstream namespace, if the license form redirect goes back to upstream's `esc_url()`, or if a hook the SDK fires or a constant it reads has no MAC Core prefix, which also catches a global name that a new SDK version adds.

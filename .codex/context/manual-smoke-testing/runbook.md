# MAC Core Manual Smoke Testing

Use this runbook for fast single-site QA of two separate surfaces:

- frontend helper runtime
- admin settings and save behavior

The Bricks snippet is only for the frontend helper/runtime side. It supplements normal admin, licensing, and update-flow QA. It does not replace them.

For release-tier decisions and the minimum required QA scope, also read:

- `.codex/context/manual-smoke-testing/release-matrix.md`

## Scope

- `Settings`, `Helpers`, `License`, and `Support` tab routing.
- `Helpers` tab save behavior and persistence.
- `Helpers` tab gating and individual wrapper loading.
- Public helper wrappers:
  - `mac_core_count_array_items()`
  - `mac_core_format_datetime()`
  - `mac_core_format_price()`
  - `mac_core_get_plugin_status()`
  - `mac_core_get_post_terms()`
  - `mac_core_get_post_type_label()`
  - `mac_core_get_taxonomy_label()`
  - `mac_core_get_theme_status()`
- `FormatDatetime` preset, view, and config filters through child-theme code.
- Builder-side output sanity on the Home page with a Bricks Code element.

## What The Bricks Snippet Does Not Prove

- It does not prove admin tab routing.
- It does not prove settings save behavior or persistence after refresh.
- It does not prove non-helper settings behavior from the `Settings` tab.
- It does not prove licensing or update flow.

Use the Bricks snippet for frontend runtime verification only, and pair it with the admin checklist below when release QA requires settings or admin coverage.

## Gallery Datetime Fields Needed

For the recommended extension test, add these fields to the `galleries` post type and map them through a child-theme preset.

### Minimum

- `gallery_start_date`
  - Type: ACF Date Picker
  - Return format: `Y-m-d`
  - Required for any non-empty gallery preset output when combined datetime fields are empty.

### Full Coverage

- `gallery_start_date`
  - Type: ACF Date Picker
  - Return format: `Y-m-d`
- `gallery_end_date`
  - Type: ACF Date Picker
  - Return format: `Y-m-d`
  - Tests multi-day ranges.
- `gallery_start_time`
  - Type: ACF Time Picker
  - Return format: `H:i`
  - Tests time output on the start point.
- `gallery_end_time`
  - Type: ACF Time Picker
  - Return format: `H:i`
  - Tests same-day and end-time output.
- `gallery_timezone`
  - Type: ACF Text, Select, or Button Group
  - Return value: plain string is recommended for this smoke test
  - Tests timezone display output.
- `gallery_start_datetime`
  - Type: ACF Date Time Picker
  - Return format: `Y-m-d H:i:s`
  - Optional. When populated, it overrides `gallery_start_date` and `gallery_start_time`.
- `gallery_end_datetime`
  - Type: ACF Date Time Picker
  - Return format: `Y-m-d H:i:s`
  - Optional. When populated, it overrides `gallery_end_date` and `gallery_end_time`.

## Recommended Test Setup

- Use the `galleries` CPT.
- Use the `gallery-cat` taxonomy.
- Use the ACF gallery field `gallery_images`.
- Use a real gallery post with images and terms assigned.
- Current local example:
  - gallery post ID `39`
  - gallery field `gallery_images`
  - taxonomy `gallery-cat`

Populate the gallery post with values that exercise both single-day and range behavior. Example:

- `gallery_start_date`: `2026-05-01`
- `gallery_end_date`: `2026-05-03`
- `gallery_start_time`: `18:30`
- `gallery_end_time`: `21:00`
- `gallery_timezone`: a real button-group choice such as `UTC`
- `gallery_images`: at least 3 images
- `gallery-cat`: at least 1 assigned term

To test combined-datetime precedence, temporarily fill these too:

- `gallery_start_datetime`: `2026-05-01 18:30:00`
- `gallery_end_datetime`: `2026-05-03 21:00:00`

Run the two required passes, plus one optional mixed-source pass when `FormatDatetime` parsing or precedence changed:

- Pass 1: keep `gallery_start_datetime` and `gallery_end_datetime` empty, and test the separate date/time fields.
- Pass 2: populate `gallery_start_datetime` and `gallery_end_datetime`, then clear the separate date/time fields.
- Optional Pass 3: populate only one side with `*_datetime` and let the other side come from the separate date/time fields. This confirms mixed-source precedence and same-day range formatting.

## Stored ACF Import Fixture

To avoid rebuilding this ACF setup by hand, keep the current import fixture here:

- `.codex/context/manual-smoke-testing/acf-import.json`

That file currently includes:

- the `galleries` CPT
- the `gallery-cat` taxonomy
- the `Gallery` field group
- `gallery_images`
- all `gallery_*` datetime fields
- the final timezone choices:
  - `Eastern Time`
  - `Central Time`
  - `Mountain Time`
  - `Pacific Time`
  - `UTC`

If the smoke-test field names, return formats, or timezone choices change, update that JSON fixture in the same task.

## Child Theme Filter Snippet

Add this to the test site's child theme `functions.php`. It exercises all three `FormatDatetime` filter seams against the `gallery_*` fields.

```php
<?php
add_filter(
	'mac_core_format_datetime_presets',
	static function ( array $presets ): array {
		$presets['gallery_event'] = [
			'start_datetime'   => 'gallery_start_datetime',
			'end_datetime'     => 'gallery_end_datetime',
			'start_date'       => 'gallery_start_date',
			'end_date'         => 'gallery_end_date',
			'start_time'       => 'gallery_start_time',
			'end_time'         => 'gallery_end_time',
			'timezone'         => 'gallery_timezone',
			'timezone_display' => 'value',
		];

		return $presets;
	}
);

add_filter(
	'mac_core_format_datetime_views',
	static function ( array $views ): array {
		$views['gallery_plain_compact'] = [
			'return'             => 'plain',
			'show_timezone'      => true,
			'show_year_now'      => true,
			'output_date_format' => 'j M',
			'output_time_format' => 'H:i',
		];

		return $views;
	}
);

add_filter(
	'mac_core_format_datetime_config',
	static function ( array $config ): array {
		if ( function_exists( 'is_front_page' ) && is_front_page() ) {
			$config['default_preset'] = 'gallery_event';
			$config['default_view']   = 'gallery_plain_compact';
		}

		return $config;
	}
);
```

## Bricks Code Snippet

Add a Bricks Code element on the Home page, enable PHP execution, and paste this snippet.

```php
<?php
$test_post_id        = 39; // Replace if your test gallery uses a different post ID.
$test_post_type      = 'galleries';
$test_taxonomy       = 'gallery-cat';
$test_gallery_field  = 'gallery_images';
$custom_preset       = 'gallery_event';
$custom_view         = 'gallery_plain_compact';

$settings = get_option( 'mac_core_settings', [] );
$utils_settings = ( is_array( $settings ) && isset( $settings['utils'] ) && is_array( $settings['utils'] ) )
	? $settings['utils']
	: [];

$utils_master = ! empty( $utils_settings['utils_enabled'] );

$wrapper_map = [
	'count_array_items_enabled' => 'mac_core_count_array_items',
	'format_datetime_enabled'   => 'mac_core_format_datetime',
	'format_price_enabled'      => 'mac_core_format_price',
	'post_type_label_enabled'   => 'mac_core_get_post_type_label',
	'taxonomy_label_enabled'    => 'mac_core_get_taxonomy_label',
	'post_terms_enabled'        => 'mac_core_get_post_terms',
	'plugin_status_enabled'     => 'mac_core_get_plugin_status',
	'theme_status_enabled'      => 'mac_core_get_theme_status',
];

$gallery_datetime_fields = [
	'gallery_start_datetime',
	'gallery_end_datetime',
	'gallery_start_date',
	'gallery_end_date',
	'gallery_start_time',
	'gallery_end_time',
	'gallery_timezone',
];

// Read the saved MAC Core helper settings for the current site.
$bool = static function ( bool $value ): string {
	return $value ? 'true' : 'false';
};

$stringify = static function ( $value ): string {
	if ( is_bool( $value ) ) {
		return $value ? 'true' : 'false';
	}

	if ( null === $value ) {
		return 'null';
	}

	if ( is_scalar( $value ) ) {
		return (string) $value;
	}

	$json = function_exists( 'wp_json_encode' )
		? wp_json_encode( $value, JSON_UNESCAPED_SLASHES )
		: json_encode( $value );

	if ( is_string( $json ) ) {
		return $json;
	}

	ob_start();
	print_r( $value );

	return trim( (string) ob_get_clean() );
};

$call = static function ( callable $callback ) use ( $stringify ): string {
	try {
		return $stringify( $callback() );
	} catch ( \Throwable $e ) {
		return 'ERROR: ' . $e->getMessage();
	}
};

// Compare saved helper toggles to the wrappers currently loaded in PHP.
$audit_rows = [];

foreach ( $wrapper_map as $setting_key => $function_name ) {
	$toggle_enabled  = ! empty( $utils_settings[ $setting_key ] );
	$expected_loaded = $utils_master && $toggle_enabled;
	$actual_loaded   = function_exists( $function_name );

	$audit_rows[] = [
		'function' => $function_name . '()',
		'setting'  => $setting_key,
		'expected' => $expected_loaded,
		'actual'   => $actual_loaded,
		'pass'     => $expected_loaded === $actual_loaded,
	];
}

// Show the raw gallery datetime fields so bad ACF data is easy to spot.
$field_rows = [];

foreach ( $gallery_datetime_fields as $field_name ) {
	$acf_value = function_exists( 'get_field' )
		? get_field( $field_name, $test_post_id )
		: null;

	$meta_value = get_post_meta( $test_post_id, $field_name, true );

	$field_rows[] = [
		'field' => $field_name,
		'acf'   => $stringify( $acf_value ),
		'meta'  => $stringify( $meta_value ),
	];
}

$theme     = wp_get_theme();
$theme_name = $theme ? (string) $theme->get( 'Name' ) : 'unknown';
$post_title = get_the_title( $test_post_id );

// Run representative helper calls against the gallery test post.
$samples = [
	'MAC_CORE_VERSION' => defined( 'MAC_CORE_VERSION' ) ? MAC_CORE_VERSION : 'missing',
	'Test post ID' => (string) $test_post_id,
	'Test post title' => $post_title ? $post_title : 'missing',
	'Test post type (actual)' => (string) get_post_type( $test_post_id ),
	'Test taxonomy' => $test_taxonomy,
	'Current theme' => $theme_name,
	'Utils master enabled (settings)' => $bool( $utils_master ),

	'mac_core_get_post_type_label(galleries, singular)' => function_exists( 'mac_core_get_post_type_label' )
		? $call( fn() => mac_core_get_post_type_label( $test_post_type, 'singular' ) )
		: 'wrapper disabled',

	'mac_core_get_post_type_label(galleries, plural)' => function_exists( 'mac_core_get_post_type_label' )
		? $call( fn() => mac_core_get_post_type_label( $test_post_type, 'plural' ) )
		: 'wrapper disabled',

	'mac_core_get_taxonomy_label(gallery-cat, singular)' => function_exists( 'mac_core_get_taxonomy_label' )
		? $call( fn() => mac_core_get_taxonomy_label( $test_taxonomy, 'singular' ) )
		: 'wrapper disabled',

	'mac_core_get_taxonomy_label(gallery-cat, plural)' => function_exists( 'mac_core_get_taxonomy_label' )
		? $call( fn() => mac_core_get_taxonomy_label( $test_taxonomy, 'plural' ) )
		: 'wrapper disabled',

	'mac_core_get_post_terms(39, gallery-cat, plain)' => function_exists( 'mac_core_get_post_terms' )
		? $call( fn() => mac_core_get_post_terms( $test_post_id, $test_taxonomy, 'plain' ) )
		: 'wrapper disabled',

	'mac_core_get_post_terms(39, gallery-cat, links)' => function_exists( 'mac_core_get_post_terms' )
		? $call( fn() => mac_core_get_post_terms( $test_post_id, $test_taxonomy, 'links' ) )
		: 'wrapper disabled',

	'mac_core_get_post_terms(39, gallery-cat, links, name, term-list)' => function_exists( 'mac_core_get_post_terms' )
		? $call( fn() => mac_core_get_post_terms( $test_post_id, $test_taxonomy, 'links', 'name', 'term-list' ) )
		: 'wrapper disabled',

	'mac_core_get_post_terms(39, gallery-cat, spans)' => function_exists( 'mac_core_get_post_terms' )
		? $call( fn() => mac_core_get_post_terms( $test_post_id, $test_taxonomy, 'spans' ) )
		: 'wrapper disabled',

	'mac_core_count_array_items(gallery_images, 39)' => function_exists( 'mac_core_count_array_items' )
		? $call( fn() => mac_core_count_array_items( $test_gallery_field, $test_post_id ) )
		: 'wrapper disabled',

	'raw get_field(gallery_images, 39) count' => function_exists( 'get_field' )
		? $call(
			function () use ( $test_post_id, $test_gallery_field ) {
				$value = get_field( $test_gallery_field, $test_post_id );

				return is_array( $value ) ? count( $value ) : 'not-array';
			}
		)
		: 'ACF not available',

	'mac_core_format_price(1234.5, USD)' => function_exists( 'mac_core_format_price' )
		? $call( fn() => mac_core_format_price( 1234.5, 'USD' ) )
		: 'wrapper disabled',

	'mac_core_format_price(1000, RON, after)' => function_exists( 'mac_core_format_price' )
		? $call(
			fn() => mac_core_format_price(
				1000,
				'RON',
				[
					'symbol_position'      => 'after',
					'space_between'        => true,
					'strip_trailing_zeros' => true,
				]
			)
		)
		: 'wrapper disabled',

	'mac_core_format_price(1234.5, USD, html)' => function_exists( 'mac_core_format_price' )
		? $call(
			fn() => mac_core_format_price(
				1234.5,
				'USD',
				[
					'return' => 'html',
				]
			)
		)
		: 'wrapper disabled',

	'mac_core_format_price(12,524.00, USD, raw)' => function_exists( 'mac_core_format_price' )
		? $call(
			fn() => mac_core_format_price(
				'12,524.00',
				'USD',
				[
					'return' => 'raw',
				]
			)
		)
		: 'wrapper disabled',

	'mac_core_format_price(352,42, EUR, raw comma-decimal)' => function_exists( 'mac_core_format_price' )
		? $call(
			fn() => mac_core_format_price(
				'352,42',
				'EUR',
				[
					'return'              => 'raw',
					'decimal_separator'   => ',',
					'thousands_separator' => '',
				]
			)
		)
		: 'wrapper disabled',

	'mac_core_get_plugin_status(acf)' => function_exists( 'mac_core_get_plugin_status' )
		? $call( fn() => mac_core_get_plugin_status( 'acf' ) )
		: 'wrapper disabled',

	'mac_core_get_plugin_status(surecart)' => function_exists( 'mac_core_get_plugin_status' )
		? $call( fn() => mac_core_get_plugin_status( 'surecart' ) )
		: 'wrapper disabled',

	'mac_core_get_theme_status(bricks)' => function_exists( 'mac_core_get_theme_status' )
		? $call( fn() => mac_core_get_theme_status( 'bricks' ) )
		: 'wrapper disabled',

	'mac_core_get_theme_status(etch)' => function_exists( 'mac_core_get_theme_status' )
		? $call( fn() => mac_core_get_theme_status( 'etch' ) )
		: 'wrapper disabled',

	'mac_core_format_datetime(gallery_event, plain, 39)' => function_exists( 'mac_core_format_datetime' )
		? $call( fn() => mac_core_format_datetime( $custom_preset, 'plain', $test_post_id ) )
		: 'wrapper disabled',

	'mac_core_format_datetime(gallery_event, plain_relative, 39)' => function_exists( 'mac_core_format_datetime' )
		? $call( fn() => mac_core_format_datetime( $custom_preset, 'plain_relative', $test_post_id ) )
		: 'wrapper disabled',

	'mac_core_format_datetime(gallery_event, plain_with_timezone, 39)' => function_exists( 'mac_core_format_datetime' )
		? $call( fn() => mac_core_format_datetime( $custom_preset, 'plain_with_timezone', $test_post_id ) )
		: 'wrapper disabled',

	'mac_core_format_datetime(gallery_event, plain_relative_with_timezone, 39)' => function_exists( 'mac_core_format_datetime' )
		? $call( fn() => mac_core_format_datetime( $custom_preset, 'plain_relative_with_timezone', $test_post_id ) )
		: 'wrapper disabled',

	'mac_core_format_datetime(gallery_event, plain_diff, 39)' => function_exists( 'mac_core_format_datetime' )
		? $call( fn() => mac_core_format_datetime( $custom_preset, 'plain_diff', $test_post_id ) )
		: 'wrapper disabled',

	'mac_core_format_datetime(gallery_event, html, 39)' => function_exists( 'mac_core_format_datetime' )
		? $call( fn() => mac_core_format_datetime( $custom_preset, 'html', $test_post_id ) )
		: 'wrapper disabled',

	'mac_core_format_datetime(gallery_event, html_with_timezone, 39)' => function_exists( 'mac_core_format_datetime' )
		? $call( fn() => mac_core_format_datetime( $custom_preset, 'html_with_timezone', $test_post_id ) )
		: 'wrapper disabled',

	'mac_core_format_datetime(gallery_event, html_diff, 39)' => function_exists( 'mac_core_format_datetime' )
		? $call( fn() => mac_core_format_datetime( $custom_preset, 'html_diff', $test_post_id ) )
		: 'wrapper disabled',

	'mac_core_format_datetime(gallery_event, attr, 39)' => function_exists( 'mac_core_format_datetime' )
		? $call( fn() => mac_core_format_datetime( $custom_preset, 'attr', $test_post_id ) )
		: 'wrapper disabled',

	'mac_core_format_datetime(gallery_event, gallery_plain_compact, 39)' => function_exists( 'mac_core_format_datetime' )
		? $call( fn() => mac_core_format_datetime( $custom_preset, $custom_view, $test_post_id ) )
		: 'wrapper disabled',

	'mac_core_format_datetime(null, null, 39) on front page' => function_exists( 'mac_core_format_datetime' )
		? $call( fn() => mac_core_format_datetime( null, null, $test_post_id ) )
		: 'wrapper disabled',
];

$utils_json = function_exists( 'wp_json_encode' )
	? wp_json_encode( $utils_settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
	: json_encode( $utils_settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

ob_start();
?>
<style>
.mac-core-smoke-test {
	font: 14px/1.5 sans-serif;
	padding: 24px;
	border: 1px solid #d7dce1;
	background: #fff;
	color: #111827;
}
.mac-core-smoke-test h2,
.mac-core-smoke-test h3 {
	margin: 0 0 12px;
}
.mac-core-smoke-test p,
.mac-core-smoke-test pre {
	margin: 0 0 16px;
}
.mac-core-smoke-test table {
	width: 100%;
	border-collapse: collapse;
	margin: 0 0 20px;
}
.mac-core-smoke-test th,
.mac-core-smoke-test td {
	border: 1px solid #d7dce1;
	padding: 8px 10px;
	text-align: left;
	vertical-align: top;
}
.mac-core-smoke-test th {
	background: #f3f4f6;
}
.mac-core-smoke-test .pass {
	color: #0f7b35;
	font-weight: 700;
}
.mac-core-smoke-test .fail {
	color: #b42318;
	font-weight: 700;
}
.mac-core-smoke-test pre {
	background: #0f172a;
	color: #e5e7eb;
	padding: 12px;
	overflow: auto;
	white-space: pre-wrap;
}
</style>

<div class="mac-core-smoke-test">
	<h2>MAC Core Frontend Smoke Test</h2>
	<p>Gallery test target: post ID <strong><?php echo esc_html( (string) $test_post_id ); ?></strong>, post type <strong><?php echo esc_html( $test_post_type ); ?></strong>, taxonomy <strong><?php echo esc_html( $test_taxonomy ); ?></strong>.</p>

	<h3>Wrapper Audit</h3>
	<table>
		<thead>
			<tr>
				<th>Function</th>
				<th>Setting Key</th>
				<th>Expected Loaded</th>
				<th>Actually Loaded</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $audit_rows as $row ) : ?>
				<tr>
					<td><?php echo esc_html( $row['function'] ); ?></td>
					<td><?php echo esc_html( $row['setting'] ); ?></td>
					<td><?php echo esc_html( $bool( $row['expected'] ) ); ?></td>
					<td><?php echo esc_html( $bool( $row['actual'] ) ); ?></td>
					<td class="<?php echo esc_attr( $row['pass'] ? 'pass' : 'fail' ); ?>">
						<?php echo esc_html( $row['pass'] ? 'PASS' : 'FAIL' ); ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<h3>Gallery Datetime Field Inputs</h3>
	<table>
		<thead>
			<tr>
				<th>Field</th>
				<th>ACF Value</th>
				<th>Raw Meta</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $field_rows as $row ) : ?>
				<tr>
					<td><?php echo esc_html( $row['field'] ); ?></td>
					<td><?php echo esc_html( $row['acf'] ); ?></td>
					<td><?php echo esc_html( $row['meta'] ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<h3>Helper Samples</h3>
	<table>
		<thead>
			<tr>
				<th>Check</th>
				<th>Output</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $samples as $label => $value ) : ?>
				<tr>
					<td><?php echo esc_html( $label ); ?></td>
					<td><?php echo esc_html( (string) $value ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<h3>Raw Utils Settings</h3>
	<pre><?php echo esc_html( is_string( $utils_json ) ? $utils_json : '{}' ); ?></pre>
</div>
<?php
echo ob_get_clean();
?>
```

## WordPress Test Steps

1. Install and activate the current MAC Core dev ZIP on a single-site test install.
2. Open `MAC Core` and confirm the `Settings`, `Helpers`, `License`, and `Support` tabs all load.
3. In `MAC Core > Helpers`, enable:
   - `Utils`
   - `Count array items`
   - `Format datetime`
   - `Format price`
   - `Post type label`
   - `Taxonomy label`
   - `Post terms`
   - `Plugin status`
   - `Theme status`
4. Save the `Helpers` tab, refresh the page, and confirm those saved values persist.
5. Register or import the `galleries` CPT, the `gallery-cat` taxonomy, and the `gallery_images` ACF gallery field.
6. Add the `gallery_*` datetime fields above to the `galleries` post type with the exact field names and return formats listed in this runbook.
7. Edit the test gallery post and populate:
   - at least 3 gallery images
   - at least 1 `gallery-cat` term
   - gallery dates and times
   - a real `gallery_timezone` choice
8. Add the child-theme filter snippet to the active child theme `functions.php`.
9. Add the Bricks Code element to the Home page and enable PHP execution.
10. Load the Home page while logged in and confirm the output tables render.
11. Confirm the `Wrapper Audit` table shows `PASS` for every enabled helper.
12. Run the separate-field pass:
   - keep `gallery_start_datetime` and `gallery_end_datetime` empty
   - confirm the `gallery_event` outputs are non-empty
13. Run the combined-datetime pass:
   - populate `gallery_start_datetime` and `gallery_end_datetime`
   - clear `gallery_start_date`, `gallery_end_date`, `gallery_start_time`, and `gallery_end_time`
   - confirm the `gallery_event` outputs stay non-empty
14. Optional mixed-source pass when `FormatDatetime` parsing or precedence is in scope:
   - populate only `gallery_start_datetime`
   - keep `gallery_end_datetime` empty
   - fill `gallery_end_date` and `gallery_end_time`
   - confirm the output uses the combined datetime for the start and the separate fields for the end
   - for a same-day range, confirm the end side can collapse to time-only output such as `April 10, 2026 1:13 pm - 10:18 pm`
15. Confirm `mac_core_format_datetime(gallery_event, gallery_plain_compact, 39)` is non-empty. This proves the preset and view filters are active.
16. Confirm `mac_core_format_datetime(null, null, 39)` on the Home page matches the custom compact output. This proves the config filter is active on the front page.
17. Temporarily disable one helper toggle in `MAC Core > Helpers`, save, refresh the Home page, and confirm:
   - the wrapper audit for that function flips to disabled
   - the helper sample output changes to `wrapper disabled`
18. Re-enable the helper, save again, refresh, and confirm the wrapper audit returns to `PASS`.
19. In `MAC Core > Settings`, change one real `Settings`-tab field, save, refresh, and confirm the value persists.
20. For the current `1.0.0` candidate, also clear the frontend admin bar exempt-target field, save, refresh, and confirm it normalizes back to `administrator`.
21. Copy the full rendered page output from the Home page and paste it back into the working thread for review.
22. In the same message, also state which admin settings/toggle changes you tested so the frontend output can be interpreted against the right saved state.

## Expected Quick Wins

- `mac_core_get_post_type_label(galleries, singular)` returns `Gallery`.
- `mac_core_get_post_type_label(galleries, plural)` returns `Galleries`.
- `mac_core_get_taxonomy_label(gallery-cat, singular)` returns `Gallery Category`.
- `mac_core_get_taxonomy_label(gallery-cat, plural)` returns `Gallery Categories`.
- `mac_core_count_array_items(gallery_images, 39)` matches the raw ACF gallery count.
- `mac_core_get_post_terms(39, gallery-cat, plain)` returns at least one assigned term.
- `mac_core_get_post_terms(39, gallery-cat, links, name, term-list)` returns a wrapper `<ul>` that includes both `mac-core-terms` and `term-list`.
- When the taxonomy is not actually linkable, the `links` format falls back to the no-link HTML variant instead of returning empty output or fake links.
- `mac_core_format_datetime(gallery_event, plain, 39)` returns a readable date or date range.
- `mac_core_format_datetime(gallery_event, plain_relative, 39)` returns either relative labels when applicable or a normal date string when not.
- `mac_core_format_datetime(gallery_event, plain_with_timezone, 39)` appends the selected timezone value.
- `mac_core_format_datetime(gallery_event, plain_relative_with_timezone, 39)` still appends the timezone.
- `mac_core_format_datetime(gallery_event, plain_diff, 39)` returns lifecycle text such as `Starts in ...` or `Ended ... ago`.
- `mac_core_format_datetime(gallery_event, html, 39)` returns escaped HTML markup using `mac-core-datetime*` classes.
- `mac_core_format_datetime(gallery_event, html_with_timezone, 39)` returns escaped HTML markup including the timezone span and `mac-core-datetime*` classes.
- `mac_core_format_datetime(gallery_event, html_diff, 39)` returns escaped HTML markup for lifecycle text using `mac-core-datetime__diff`.
- `mac_core_format_price(1234.5, USD, html)` returns escaped HTML markup using `mac-core-price*` classes.
- `mac_core_format_price(12,524.00, USD, raw)` returns `12524`.
- `mac_core_format_price(352,42, EUR, raw comma-decimal)` returns `352.42`.
- `mac_core_format_datetime(gallery_event, gallery_plain_compact, 39)` returns a visibly custom format driven by the child-theme filter.
- In the optional mixed-source pass, start-side `*_datetime` data can combine cleanly with end-side separate date/time data.
- In the optional mixed-source pass, same-day output can legitimately collapse the end side to time-only text.

## Notes

- If `gallery_start_datetime` or `gallery_end_datetime` are populated, they override the separate date and time fields.
- For ACF choice fields, keep the default value as one real selected value, not a combined `key : label` string.
- The `class` argument on `mac_core_get_post_terms()` only affects the HTML formats (`links` and `spans`), not `plain`, and it is applied to the wrapper `<ul>`.
- If `mac_core_get_post_terms()` is called with `links` for a taxonomy that is not publicly/queryably linkable, or if a term link cannot be generated, it falls back to the no-link HTML variant for that term output.
- `mac_core_format_datetime()` HTML views always return classed `mac-core-datetime*` markup. There is no classless built-in HTML mode.
- `mac_core_format_price()` defaults to plain text. Use `return => 'html'` for classed `mac-core-price*` markup and `return => 'raw'` for normalized numeric-string output.
- The Bricks snippet reads saved helper settings and compares them to loaded wrappers, but it does not replace admin-side settings save checks.
- Blank `plain_diff` output usually means the start date is missing or invalid, not that the helper failed.
- The custom config filter in this runbook intentionally changes the default preset and view on the Home page only. Remove it after testing if you do not want that behavior on the test site.
- Keep this runbook current whenever helper names, helper toggles, `FormatDatetime` filters, or the recommended QA flow change.

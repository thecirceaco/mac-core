<?php
/**
 * Format preset-based date and time fields for Bricks or templates.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Utils {

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Stringable;

/**
 * Format preset-based date and time fields.
 */
final class FormatDatetime
{
    /**
     * Main defaults.
     *
     * These are used when format() is called with null or blank preset/view
     * arguments. If either default is blank and no argument is passed, output is
     * empty.
     *
     * @var array{default_preset:string,default_view:string}
     */
    public static array $config = [
        'default_preset' => 'event',
        'default_view'   => 'plain',
    ];

    /**
     * Shared output views.
     *
     * Return formats:
     * - html: styled span markup with <time datetime=""> elements.
     * - plain: plain text.
     * - attr: machine datetime for a <time datetime=""> attribute.
     *
     * Optional view settings:
     * - show_timezone: append the configured timezone field or site timezone.
     * - relative: use Today, Tomorrow, and Yesterday labels.
     * - show_year_now: force the year even when the output date format omits it.
     * - diff: return lifecycle text like "Starts in 3 days"; omits timezone labels.
     * - timezone_display: label or value for ACF choice-field arrays.
     * - output_datetime_format, output_date_format, output_time_format:
     *   override the preset output formats for this view.
     * - date_labels, diff_labels: override relative or lifecycle text.
     *
     * @var array<string,array<string,mixed>>
     */
    public static array $views = [
        'plain' => [
            'return' => 'plain',
        ],
        'plain_relative' => [
            'return'   => 'plain',
            'relative' => true,
        ],
        'plain_diff' => [
            'return' => 'plain',
            'diff'   => true,
        ],
        'plain_with_timezone' => [
            'return'        => 'plain',
            'show_timezone' => true,
        ],
        'plain_relative_with_timezone' => [
            'return'        => 'plain',
            'relative'      => true,
            'show_timezone' => true,
        ],
        'html' => [
            'return' => 'html',
        ],
        'html_relative' => [
            'return'   => 'html',
            'relative' => true,
        ],
        'html_with_timezone' => [
            'return'        => 'html',
            'show_timezone' => true,
        ],
        'html_relative_with_timezone' => [
            'return'        => 'html',
            'relative'      => true,
            'show_timezone' => true,
        ],
        'html_diff' => [
            'return' => 'html',
            'diff'   => true,
        ],
        'attr' => [
            'return' => 'attr',
        ],
    ];

    /**
     * Field presets.
     *
     * Add more presets for other CPTs. Blank fields are unused.
     * Combined datetime fields are checked before separate date/time fields.
     *
     * Input formats describe ACF or post meta return values.
     * Output formats describe display values; null uses WordPress General Settings.
     * Field names may be strings or ordered string arrays.
     * timezone accepts valid IANA timezone identifiers and affects parsing,
     * display, and machine attributes. Invalid or blank values fall back to the
     * site timezone.
     * timezone_display accepts label or value for ACF choice-field arrays.
     *
     * @var array<string,array<string,mixed>>
     */
    public static array $presets = [
        'event' => [
            'start_datetime'   => ['event_start', 'event_start_datetime'],
            'end_datetime'     => ['event_end', 'event_end_datetime'],
            'start_date'       => 'event_start_date',
            'end_date'         => 'event_end_date',
            'start_time'       => 'event_start_time',
            'end_time'         => 'event_end_time',
            'timezone'         => 'event_timezone',
            'timezone_display' => 'label',

            'input_datetime_format' => 'Y-m-d H:i:s',
            'input_date_format'     => 'Y-m-d',
            'input_time_format'     => 'H:i',

            'output_datetime_format' => null,
            'output_date_format'     => null,
            'output_time_format'     => null,

            'date_labels' => [],
            'diff_labels' => [],
        ],
    ];

    /** @var array<string,string> */
    private const CLASSES = [
        'wrapper'    => 'mac-core-datetime',
        'start'      => 'mac-core-datetime__start',
        'start_date' => 'mac-core-datetime__start-date',
        'start_time' => 'mac-core-datetime__start-time',
        'end'        => 'mac-core-datetime__end',
        'end_date'   => 'mac-core-datetime__end-date',
        'end_time'   => 'mac-core-datetime__end-time',
        'separator'  => 'mac-core-datetime__separator',
        'timezone'   => 'mac-core-datetime__timezone',
        'diff'       => 'mac-core-datetime__diff',
    ];

    /** @var array<string,string> */
    public static array $dateLabels = [
        'today'     => 'Today',
        'tomorrow'  => 'Tomorrow',
        'yesterday' => 'Yesterday',
    ];

    /** @var array<string,string> */
    public static array $diffLabels = [
        'starts_in' => 'Starts in',
        'ends_in'   => 'Ends in',
        'started'   => 'Started',
        'ended'     => 'Ended',
        'ago'       => 'ago',

        'minute'  => 'minute',
        'minutes' => 'minutes',
        'hour'    => 'hour',
        'hours'   => 'hours',
        'day'     => 'day',
        'days'    => 'days',
        'week'    => 'week',
        'weeks'   => 'weeks',
        'month'   => 'month',
        'months'  => 'months',
        'year'    => 'year',
        'years'   => 'years',
    ];

    /**
     * Format a preset date/time value.
     */
    public static function format(
        ?string $preset = null,
        ?string $view = null,
        int|string|null $postId = null
    ): string {
        $config = self::config($preset, $view, $postId);

        $preset = \strtolower(\trim((string) $preset));
        $preset = $preset !== ''
            ? $preset
            : \strtolower(\trim((string) ($config['default_preset'] ?? '')));

        $presets = self::presets($preset, $view, $postId);

        if (! isset($presets[$preset]) || ! \is_array($presets[$preset])) {
            return '';
        }

        $presetConfig = \array_merge(
            [
                'start_datetime'   => '',
                'end_datetime'     => '',
                'start_date'       => '',
                'end_date'         => '',
                'start_time'       => '',
                'end_time'         => '',
                'timezone'         => '',
                'timezone_display' => 'label',

                'input_datetime_format' => '',
                'input_date_format'     => '',
                'input_time_format'     => '',

                'output_datetime_format' => null,
                'output_date_format'     => null,
                'output_time_format'     => null,
                'date_labels'            => [],
                'diff_labels'            => [],
            ],
            $presets[$preset]
        );

        $view = \strtolower(\trim((string) $view));
        $view = $view !== ''
            ? $view
            : \strtolower(\trim((string) ($config['default_view'] ?? '')));

        $views = self::views($preset, $view, $postId);

        if (! isset($views[$view]) || ! \is_array($views[$view])) {
            return '';
        }

        $viewConfig = \array_merge(
            [
                'return'                 => 'plain',
                'show_timezone'          => false,
                'relative'               => false,
                'show_year_now'          => false,
                'diff'                   => false,
                'timezone_display'       => $presetConfig['timezone_display'],
                'output_datetime_format' => $presetConfig['output_datetime_format'],
                'output_date_format'     => $presetConfig['output_date_format'],
                'output_time_format'     => $presetConfig['output_time_format'],
                'date_labels'            => [],
                'diff_labels'            => [],
            ],
            $views[$view]
        );

        $postId = self::contextId($postId);

        if ($postId === null) {
            return '';
        }

        $showTimezone = self::bool($viewConfig['show_timezone']);
        $relative     = self::bool($viewConfig['relative']);
        $showYearNow  = self::bool($viewConfig['show_year_now']);
        $diff         = self::bool($viewConfig['diff']);
        $return       = \is_string($viewConfig['return']) ? \strtolower(\trim($viewConfig['return'])) : 'html';
        $return       = \in_array($return, ['html', 'plain', 'attr'], true) ? $return : 'html';
        $dateFormat   = self::outputFormat($viewConfig['output_date_format'], 'date_format', 'M j, Y');
        $timeFormat   = self::outputFormat($viewConfig['output_time_format'], 'time_format', 'g:i a');

        $dateFormat = $dateFormat !== '' ? $dateFormat : 'Y-m-d';
        $timeFormat = $timeFormat !== '' ? $timeFormat : 'H:i';
        $datetimeFormat = self::outputFormat($viewConfig['output_datetime_format'], '', '');
        $dateLabels     = \array_merge(
            \is_array($presetConfig['date_labels']) ? $presetConfig['date_labels'] : [],
            \is_array($viewConfig['date_labels']) ? $viewConfig['date_labels'] : []
        );
        $diffLabels     = \array_merge(
            \is_array($presetConfig['diff_labels']) ? $presetConfig['diff_labels'] : [],
            \is_array($viewConfig['diff_labels']) ? $viewConfig['diff_labels'] : []
        );
        $timezoneDisplay = \is_string($viewConfig['timezone_display'])
            ? \strtolower(\trim($viewConfig['timezone_display']))
            : 'label';
        $timezoneDisplay = \in_array($timezoneDisplay, ['label', 'value'], true) ? $timezoneDisplay : 'label';

        $timezoneValue  = self::getFirstFieldValue($presetConfig['timezone'], $postId);
        $eventTimezone  = self::validTimezone($timezoneValue);
        $renderTimezone = $eventTimezone ?? self::timezone();

        $start = self::resolvePoint($presetConfig, 'start', $postId, $renderTimezone);
        $end   = self::resolvePoint($presetConfig, 'end', $postId, $renderTimezone);

        if ($start['invalid'] || $end['invalid'] || ! $start['timestamp']) {
            return '';
        }

        if ($diff) {
            $diffText = self::humanDiff($start['timestamp'], $end['timestamp'], $diffLabels);

            if ($return === 'html' && $diffText !== '') {
                return '<span class="' . self::escAttr(self::className('wrapper')) . '">'
                    . '<span class="' . self::escAttr(self::className('diff')) . '">' . self::escHtml($diffText) . '</span>'
                    . '</span>';
            }

            return self::escHtml($diffText);
        }

        $startDateKey   = self::formatTimestamp('Y-m-d', $start['timestamp'], $renderTimezone);
        $endDateKey     = $end['timestamp'] ? self::formatTimestamp('Y-m-d', $end['timestamp'], $renderTimezone) : '';
        $sameDayRange   = $end['timestamp'] && $startDateKey === $endDateKey;
        $crossYearRange = $end['timestamp']
            && self::formatTimestamp('Y', $start['timestamp'], $renderTimezone) !== self::formatTimestamp('Y', $end['timestamp'], $renderTimezone);

        if ($return === 'attr') {
            return self::attrFromPoint($start, $renderTimezone);
        }

        $startDate = self::dateLabel(
            $start['timestamp'],
            $dateFormat,
            $relative,
            $showYearNow,
            $crossYearRange,
            $dateLabels,
            $renderTimezone
        );
        $endDate = $end['timestamp']
            ? self::dateLabel($end['timestamp'], $dateFormat, false, $showYearNow, $crossYearRange, $dateLabels, $renderTimezone)
            : '';
        $startTime = $start['has_time'] ? self::formatTimestamp($timeFormat, $start['timestamp'], $renderTimezone) : '';
        $endTime   = $end['timestamp'] && $end['has_time'] ? self::formatTimestamp($timeFormat, $end['timestamp'], $renderTimezone) : '';

        if (! $relative && $datetimeFormat !== '') {
            if ($startTime !== '') {
                $startDate = self::formatTimestamp($datetimeFormat, $start['timestamp'], $renderTimezone);
                $startTime = '';
            }

            if ($endTime !== '' && ! $sameDayRange) {
                $endDate = self::formatTimestamp($datetimeFormat, $end['timestamp'], $renderTimezone);
                $endTime = '';
            }
        }

        $timezoneLabel = '';

        if ($showTimezone) {
            $timezoneLabel = self::timezoneOutputLabel($timezoneValue, $timezoneDisplay, $eventTimezone, $renderTimezone);
        }

        if ($return === 'plain') {
            return self::plainOutput($startDate, $startTime, $end, $endDate, $endTime, $sameDayRange, $timezoneLabel);
        }

        return self::htmlOutput($start, $startDate, $startTime, $end, $endDate, $endTime, $sameDayRange, $timezoneLabel, $renderTimezone);
    }

    /**
     * Filterable default preset and view configuration.
     *
     * Hook: `mac_core_format_datetime_config`
     *
     * @return array{default_preset:string,default_view:string}
     */
    private static function config(
        ?string $preset = null,
        ?string $view = null,
        int|string|null $postId = null
    ): array {
        $config = \function_exists('apply_filters')
            ? \apply_filters('mac_core_format_datetime_config', self::$config, $preset, $view, $postId)
            : self::$config;

        if (! \is_array($config)) {
            return self::$config;
        }

        return \array_merge(self::$config, $config);
    }

    /**
     * Filterable preset definitions.
     *
     * Hook: `mac_core_format_datetime_presets`
     *
     * @return array<string,array<string,mixed>>
     */
    private static function presets(
        ?string $preset = null,
        ?string $view = null,
        int|string|null $postId = null
    ): array {
        $presets = \function_exists('apply_filters')
            ? \apply_filters('mac_core_format_datetime_presets', self::$presets, $preset, $view, $postId)
            : self::$presets;

        return \is_array($presets) ? $presets : self::$presets;
    }

    /**
     * Filterable view definitions.
     *
     * Hook: `mac_core_format_datetime_views`
     *
     * @return array<string,array<string,mixed>>
     */
    private static function views(
        ?string $preset = null,
        ?string $view = null,
        int|string|null $postId = null
    ): array {
        $views = \function_exists('apply_filters')
            ? \apply_filters('mac_core_format_datetime_views', self::$views, $preset, $view, $postId)
            : self::$views;

        return \is_array($views) ? $views : self::$views;
    }

    private static function bool(mixed $value): bool
    {
        if (\is_bool($value)) {
            return $value;
        }

        if (\is_int($value)) {
            return $value !== 0;
        }

        if (! \is_string($value)) {
            return false;
        }

        $value = \strtolower(\trim((string) $value));

        return \in_array($value, ['1', 'true', 'yes', 'y', 'on'], true);
    }

    private static function escHtml(string $value): string
    {
        return \function_exists('esc_html')
            ? \esc_html($value)
            : \htmlspecialchars($value, \ENT_QUOTES, 'UTF-8');
    }

    private static function escAttr(string $value): string
    {
        return \function_exists('esc_attr')
            ? \esc_attr($value)
            : \htmlspecialchars($value, \ENT_QUOTES, 'UTF-8');
    }

    private static function timezone(): DateTimeZone
    {
        if (\function_exists('wp_timezone')) {
            return \wp_timezone();
        }

        if (\function_exists('wp_timezone_string')) {
            $timezoneString = (string) \wp_timezone_string();

            if ($timezoneString !== '') {
                try {
                    return new DateTimeZone($timezoneString);
                } catch (Exception $exception) {
                    $timezoneString = '';
                }
            }
        }

        return new DateTimeZone(\date_default_timezone_get());
    }

    private static function timezoneLabel(?DateTimeZone $timezone = null): string
    {
        if ($timezone instanceof DateTimeZone) {
            return $timezone->getName();
        }

        if (\function_exists('wp_timezone_string')) {
            $timezoneString = (string) \wp_timezone_string();

            if ($timezoneString !== '') {
                return $timezoneString;
            }
        }

        return \date_default_timezone_get();
    }

    private static function now(): int
    {
        return \time();
    }

    private static function formatTimestamp(string $format, int $timestamp, ?DateTimeZone $timezone = null): string
    {
        $timezone ??= self::timezone();

        return \function_exists('wp_date')
            ? \wp_date($format, $timestamp, $timezone)
            : (new DateTimeImmutable('@' . $timestamp))->setTimezone($timezone)->format($format);
    }

    private static function wpOptionFormat(string $optionName, string $fallback): string
    {
        if (\function_exists('get_option')) {
            $format = \get_option($optionName);

            if (\is_string($format) && \trim($format) !== '') {
                return $format;
            }
        }

        return $fallback;
    }

    private static function outputFormat(mixed $format, string $optionName, string $fallback): string
    {
        if (\is_string($format) && \trim($format) !== '') {
            return $format;
        }

        return $optionName !== ''
            ? self::wpOptionFormat($optionName, $fallback)
            : $fallback;
    }

    private static function isBlank(mixed $value): bool
    {
        return $value === null
            || $value === false
            || (\is_string($value) && \trim($value) === '');
    }

    private static function contextId(int|string|null $postId): int|string|null
    {
        if ($postId === null || $postId === '') {
            $postId = \function_exists('get_the_ID') ? \get_the_ID() : null;
        }

        if (\is_numeric($postId)) {
            $postId = (int) $postId;
        }

        if ($postId === 0 || $postId === false) {
            return null;
        }

        return $postId;
    }

    private static function getFieldValue(string $fieldName, int|string $postId): mixed
    {
        if ($fieldName === '') {
            return null;
        }

        if (\function_exists('get_field')) {
            $value = \get_field($fieldName, $postId);

            if (! self::isBlank($value)) {
                return $value;
            }
        }

        if (\is_int($postId) && $postId > 0 && \function_exists('get_post_meta')) {
            $value = \get_post_meta($postId, $fieldName, true);

            if (! self::isBlank($value)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    private static function fieldNames(mixed $fieldNames): array
    {
        if (\is_string($fieldNames) || $fieldNames instanceof Stringable || \is_int($fieldNames)) {
            $fieldName = \trim((string) $fieldNames);

            return $fieldName !== '' ? [$fieldName] : [];
        }

        if (! \is_array($fieldNames)) {
            return [];
        }

        $names = [];

        foreach ($fieldNames as $fieldName) {
            if (! \is_string($fieldName) && ! $fieldName instanceof Stringable && ! \is_int($fieldName)) {
                continue;
            }

            $fieldName = \trim((string) $fieldName);

            if ($fieldName !== '' && ! \in_array($fieldName, $names, true)) {
                $names[] = $fieldName;
            }
        }

        return $names;
    }

    private static function getFirstFieldValue(mixed $fieldNames, int|string $postId): mixed
    {
        foreach (self::fieldNames($fieldNames) as $fieldName) {
            $value = self::getFieldValue($fieldName, $postId);

            if (! self::isBlank($value)) {
                return $value;
            }
        }

        return null;
    }

    private static function displayValue(mixed $value, string $preferredKey = 'label'): string
    {
        if (self::isBlank($value)) {
            return '';
        }

        $preferredKey = \strtolower(\trim($preferredKey));
        $preferredKey = \in_array($preferredKey, ['label', 'value'], true) ? $preferredKey : 'label';
        $fallbackKey  = $preferredKey === 'label' ? 'value' : 'label';

        if (\is_array($value)) {
            foreach ([$preferredKey, $fallbackKey] as $key) {
                if (isset($value[$key]) && \is_scalar($value[$key])) {
                    return \trim((string) $value[$key]);
                }
            }

            $items = [];

            foreach ($value as $item) {
                $item = self::displayValue($item, $preferredKey);

                if ($item !== '') {
                    $items[] = $item;
                }
            }

            return \implode(', ', \array_unique($items));
        }

        if (\is_scalar($value) || $value instanceof Stringable) {
            return \trim((string) $value);
        }

        return '';
    }

    private static function timezoneIdentifier(mixed $value): string
    {
        if (self::isBlank($value)) {
            return '';
        }

        if (\is_array($value)) {
            foreach (['value', 'label'] as $key) {
                if (isset($value[$key]) && (\is_scalar($value[$key]) || $value[$key] instanceof Stringable)) {
                    $identifier = \trim((string) $value[$key]);

                    if ($identifier !== '') {
                        return $identifier;
                    }
                }
            }

            return '';
        }

        if (\is_scalar($value) || $value instanceof Stringable) {
            return \trim((string) $value);
        }

        return '';
    }

    /**
     * @return string[]
     */
    private static function validTimezoneIdentifiers(): array
    {
        static $identifiers = null;

        if ($identifiers === null) {
            $identifiers = \array_fill_keys(DateTimeZone::listIdentifiers(), true);
        }

        return \array_keys($identifiers);
    }

    private static function validTimezone(mixed $value): ?DateTimeZone
    {
        $identifier = self::timezoneIdentifier($value);

        if ($identifier === '' || ! \in_array($identifier, self::validTimezoneIdentifiers(), true)) {
            return null;
        }

        try {
            return new DateTimeZone($identifier);
        } catch (Exception $exception) {
            return null;
        }
    }

    private static function timezoneOutputLabel(
        mixed $value,
        string $preferredKey,
        ?DateTimeZone $eventTimezone,
        DateTimeZone $fallbackTimezone
    ): string {
        if ($eventTimezone instanceof DateTimeZone) {
            $displayValue = self::displayValue($value, $preferredKey);

            return $displayValue !== ''
                ? $displayValue
                : self::timezoneLabel($eventTimezone);
        }

        return self::timezoneLabel($fallbackTimezone);
    }

    /**
     * @param array<string,mixed> $preset
     * @param string[]            $fallback
     * @return string[]
     */
    private static function formatList(array $preset, string $key, array $fallback): array
    {
        $formats = [];
        $format  = \trim((string) ($preset[$key] ?? ''));

        if ($format !== '') {
            $formats[] = $format;
        }

        foreach ($fallback as $fallbackFormat) {
            if (! \in_array($fallbackFormat, $formats, true)) {
                $formats[] = $fallbackFormat;
            }
        }

        return $formats;
    }

    private static function parseByFormat(string $value, string $format, DateTimeZone $timezone): int|false
    {
        if ($format === '') {
            return false;
        }

        $parseFormat = $format;

        if ($parseFormat[0] !== '!' && $parseFormat[0] !== '|') {
            $parseFormat = '!' . $parseFormat;
        }

        $datetime = DateTimeImmutable::createFromFormat(
            $parseFormat,
            $value,
            $timezone
        );

        $errors = DateTimeImmutable::getLastErrors();

        if (
            ! $datetime instanceof DateTimeImmutable ||
            (
                \is_array($errors) &&
                ($errors['warning_count'] > 0 || $errors['error_count'] > 0)
            )
        ) {
            return false;
        }

        return $datetime->getTimestamp();
    }

    /**
     * @param string[] $formats
     */
    private static function parseValue(mixed $value, array $formats, DateTimeZone $timezone): int|false|null
    {
        if (self::isBlank($value)) {
            return null;
        }

        if (\is_int($value)) {
            return $value > 0 ? $value : false;
        }

        if (\is_float($value)) {
            return $value > 0 ? (int) $value : false;
        }

        if (! \is_string($value)) {
            return false;
        }

        $value = \trim((string) $value);

        foreach ($formats as $format) {
            $timestamp = self::parseByFormat($value, $format, $timezone);

            if ($timestamp !== false) {
                return $timestamp;
            }
        }

        if (\preg_match('/^\d{10,}$/', $value)) {
            $timestamp = (int) $value;
            return $timestamp > 0 ? $timestamp : false;
        }

        try {
            $datetime = new DateTimeImmutable($value, $timezone);
        } catch (Exception $exception) {
            return false;
        }

        $timestamp = $datetime->getTimestamp();

        return $timestamp > 0 ? $timestamp : false;
    }

    private static function formatHasTime(string $format): bool
    {
        return (bool) \preg_match('/(?<!\\\\)[HhGgisuaAB]/', $format);
    }

    private static function valueLooksDateOnly(string $value): bool
    {
        return (bool) \preg_match('/^[A-Za-z]{3,9}\s+\d{1,2},\s+\d{4}$/', $value)
            || (bool) \preg_match('/^\d{4}[-\/]?\d{2}[-\/]?\d{2}$/', $value)
            || (bool) \preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value);
    }

    /**
     * @param string[] $datetimeFormats
     * @param string[] $dateFormats
     * @return array{timestamp:int|null,has_time:bool,invalid:bool}
     */
    private static function parsePointValue(
        mixed $value,
        array $datetimeFormats,
        array $dateFormats,
        DateTimeZone $timezone
    ): array {
        if (self::isBlank($value)) {
            return [
                'timestamp' => null,
                'has_time'  => false,
                'invalid'   => false,
            ];
        }

        if (\is_int($value)) {
            return [
                'timestamp' => $value > 0 ? $value : null,
                'has_time'  => $value > 0,
                'invalid'   => $value <= 0,
            ];
        }

        if (\is_float($value)) {
            $timestamp = (int) $value;

            return [
                'timestamp' => $timestamp > 0 ? $timestamp : null,
                'has_time'  => $timestamp > 0,
                'invalid'   => $timestamp <= 0,
            ];
        }

        if (! \is_string($value)) {
            return [
                'timestamp' => null,
                'has_time'  => false,
                'invalid'   => true,
            ];
        }

        $value = \trim((string) $value);

        foreach (\array_merge($datetimeFormats, $dateFormats) as $format) {
            $timestamp = self::parseByFormat($value, $format, $timezone);

            if ($timestamp !== false) {
                return [
                    'timestamp' => $timestamp,
                    'has_time'  => self::formatHasTime($format),
                    'invalid'   => false,
                ];
            }
        }

        if (\preg_match('/^\d{10,}$/', $value)) {
            $timestamp = (int) $value;

            return [
                'timestamp' => $timestamp > 0 ? $timestamp : null,
                'has_time'  => $timestamp > 0,
                'invalid'   => $timestamp <= 0,
            ];
        }

        try {
            $datetime = new DateTimeImmutable($value, $timezone);
        } catch (Exception $exception) {
            return [
                'timestamp' => null,
                'has_time'  => false,
                'invalid'   => true,
            ];
        }

        $timestamp = $datetime->getTimestamp();

        return [
            'timestamp' => $timestamp > 0 ? $timestamp : null,
            'has_time'  => $timestamp > 0 && ! self::valueLooksDateOnly($value),
            'invalid'   => $timestamp <= 0,
        ];
    }

    /**
     * @param string[] $dateFormats
     * @param string[] $timeFormats
     */
    private static function parseSeparate(
        mixed $dateValue,
        mixed $timeValue,
        array $dateFormats,
        array $timeFormats,
        DateTimeZone $timezone
    ): array {
        if (! \is_int($dateValue) && ! \is_string($dateValue)) {
            return [
                'timestamp' => null,
                'has_time'  => false,
                'invalid'   => true,
            ];
        }

        if (! self::isBlank($timeValue) && ! \is_int($timeValue) && ! \is_string($timeValue)) {
            return [
                'timestamp' => null,
                'has_time'  => false,
                'invalid'   => true,
            ];
        }

        if (self::isBlank($timeValue)) {
            $timestamp = self::parseValue($dateValue, $dateFormats, $timezone);

            return [
                'timestamp' => \is_int($timestamp) ? $timestamp : null,
                'has_time'  => false,
                'invalid'   => $timestamp === false,
            ];
        }

        $datetimeValue = \trim((string) $dateValue) . ' ' . \trim((string) $timeValue);

        foreach ($dateFormats as $dateFormat) {
            foreach ($timeFormats as $timeFormat) {
                $timestamp = self::parseByFormat(
                    $datetimeValue,
                    $dateFormat . ' ' . $timeFormat,
                    $timezone
                );

                if ($timestamp !== false) {
                    return [
                        'timestamp' => $timestamp,
                        'has_time'  => true,
                        'invalid'   => false,
                    ];
                }
            }
        }

        return [
            'timestamp' => null,
            'has_time'  => false,
            'invalid'   => true,
        ];
    }

    /**
     * @param array<string,mixed> $preset
     * @return array{timestamp:int|null,has_time:bool,invalid:bool}
     */
    private static function resolvePoint(
        array $preset,
        string $prefix,
        int|string $postId,
        DateTimeZone $timezone
    ): array {
        $datetimeFormats = self::formatList(
            $preset,
            'input_datetime_format',
            ['Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d\TH:i:s', 'Y-m-d\TH:i', 'd/m/Y g:i a', 'd/m/Y H:i', 'M j, Y g:i a', 'F j, Y g:i a']
        );
        $dateFormats = self::formatList(
            $preset,
            'input_date_format',
            ['Y-m-d', 'Ymd', 'd/m/Y', 'm/d/Y', 'M j, Y', 'F j, Y']
        );
        $timeFormats = self::formatList(
            $preset,
            'input_time_format',
            ['H:i', 'H:i:s', 'g:i a', 'h:i a', 'g:i A', 'h:i A']
        );

        $datetimeField = $preset[$prefix . '_datetime'] ?? '';

        foreach (self::fieldNames($datetimeField) as $fieldName) {
            $datetimeValue = self::getFieldValue($fieldName, $postId);

            if (! self::isBlank($datetimeValue)) {
                return self::parsePointValue($datetimeValue, $datetimeFormats, $dateFormats, $timezone);
            }
        }

        $dateField = $preset[$prefix . '_date'] ?? '';
        $dateValue = self::getFirstFieldValue($dateField, $postId);

        if (self::isBlank($dateValue)) {
            return [
                'timestamp' => null,
                'has_time'  => false,
                'invalid'   => false,
            ];
        }

        $timeField = $preset[$prefix . '_time'] ?? '';
        $timeValue = self::getFirstFieldValue($timeField, $postId);

        return self::parseSeparate($dateValue, $timeValue, $dateFormats, $timeFormats, $timezone);
    }

    private static function formatHasYear(string $format): bool
    {
        return (bool) \preg_match('/(?<!\\\\)[Yyo]/', $format);
    }

    /**
     * @param array<string,string> $labelOverrides
     */
    private static function dateLabel(
        int $timestamp,
        string $dateFormat,
        bool $relative,
        bool $forceYear,
        bool $rangeCrossYear,
        array $labelOverrides = [],
        ?DateTimeZone $timezone = null
    ): string {
        $timezone ??= self::timezone();
        $labels = \array_merge(self::$dateLabels, $labelOverrides);

        if ($relative) {
            $now   = self::now();
            $day   = \defined('DAY_IN_SECONDS') ? \DAY_IN_SECONDS : 86400;
            $today = self::formatTimestamp('Ymd', $now, $timezone);
            $date  = self::formatTimestamp('Ymd', $timestamp, $timezone);

            if ($date === $today) {
                return (string) $labels['today'];
            }

            if ($date === self::formatTimestamp('Ymd', $now + $day, $timezone)) {
                return (string) $labels['tomorrow'];
            }

            if ($date === self::formatTimestamp('Ymd', $now - $day, $timezone)) {
                return (string) $labels['yesterday'];
            }
        }

        $format = $dateFormat !== '' ? $dateFormat : 'Y-m-d';

        if (
            ! self::formatHasYear($format) &&
            (
                $forceYear ||
                $rangeCrossYear ||
                self::formatTimestamp('Y', $timestamp, $timezone) !== self::formatTimestamp('Y', self::now(), $timezone)
            )
        ) {
            $format .= ' Y';
        }

        return self::formatTimestamp($format, $timestamp, $timezone);
    }

    /**
     * @param array<string,string> $labelOverrides
     */
    private static function duration(int $seconds, array $labelOverrides = []): string
    {
        $labels = \array_merge(self::$diffLabels, $labelOverrides);

        $seconds = \abs($seconds);
        $minute  = \defined('MINUTE_IN_SECONDS') ? \MINUTE_IN_SECONDS : 60;
        $hour    = \defined('HOUR_IN_SECONDS') ? \HOUR_IN_SECONDS : 3600;
        $day     = \defined('DAY_IN_SECONDS') ? \DAY_IN_SECONDS : 86400;
        $week    = \defined('WEEK_IN_SECONDS') ? \WEEK_IN_SECONDS : 604800;
        $month   = \defined('MONTH_IN_SECONDS') ? \MONTH_IN_SECONDS : 2592000;
        $year    = \defined('YEAR_IN_SECONDS') ? \YEAR_IN_SECONDS : 31536000;

        if ($seconds < $hour) {
            $value = \max(1, (int) \floor($seconds / $minute));
            $unit  = $value === 1 ? $labels['minute'] : $labels['minutes'];
        } elseif ($seconds < $day) {
            $value = \max(1, (int) \floor($seconds / $hour));
            $unit  = $value === 1 ? $labels['hour'] : $labels['hours'];
        } elseif ($seconds < $week) {
            $value = \max(1, (int) \floor($seconds / $day));
            $unit  = $value === 1 ? $labels['day'] : $labels['days'];
        } elseif ($seconds < $month) {
            $value = \max(1, (int) \floor($seconds / $week));
            $unit  = $value === 1 ? $labels['week'] : $labels['weeks'];
        } elseif ($seconds < $year) {
            $value = \max(1, (int) \floor($seconds / $month));
            $unit  = $value === 1 ? $labels['month'] : $labels['months'];
        } else {
            $value = \max(1, (int) \floor($seconds / $year));
            $unit  = $value === 1 ? $labels['year'] : $labels['years'];
        }

        return $value . ' ' . $unit;
    }

    /**
     * @param array<string,string> $labelOverrides
     */
    private static function humanDiff(?int $startTimestamp, ?int $endTimestamp = null, array $labelOverrides = []): string
    {
        $labels = \array_merge(self::$diffLabels, $labelOverrides);

        if (! $startTimestamp) {
            return '';
        }

        $now = self::now();

        if ($now < $startTimestamp) {
            return $labels['starts_in'] . ' ' . self::duration($startTimestamp - $now, $labelOverrides);
        }

        if ($endTimestamp && $now <= $endTimestamp) {
            return $labels['ends_in'] . ' ' . self::duration($endTimestamp - $now, $labelOverrides);
        }

        if ($endTimestamp) {
            return $labels['ended'] . ' ' . self::duration($now - $endTimestamp, $labelOverrides) . ' ' . $labels['ago'];
        }

        return $labels['started'] . ' ' . self::duration($now - $startTimestamp, $labelOverrides) . ' ' . $labels['ago'];
    }

    /**
     * @param array{timestamp:int|null,has_time:bool,invalid:bool} $point
     */
    private static function attrFromPoint(array $point, DateTimeZone $timezone): string
    {
        if (! $point['timestamp']) {
            return '';
        }

        $format = $point['has_time'] ? 'c' : 'Y-m-d';

        return self::escAttr(self::formatTimestamp($format, $point['timestamp'], $timezone));
    }

    /**
     * @param array{timestamp:int|null,has_time:bool,invalid:bool} $point
     */
    private static function timeHtml(
        array $point,
        string $timeClass,
        string $dateClass,
        string $timePartClass,
        string $dateLabel,
        string $timeLabel,
        DateTimeZone $timezone
    ): string {
        $datetime = self::attrFromPoint($point, $timezone);

        if ($datetime === '' || ($dateLabel === '' && $timeLabel === '')) {
            return '';
        }

        $out = '<time class="' . self::escAttr($timeClass) . '" datetime="' . $datetime . '">';

        if ($dateLabel !== '') {
            $out .= '<span class="' . self::escAttr($dateClass) . '">' . self::escHtml($dateLabel) . '</span>';
        }

        if ($timeLabel !== '') {
            $out .= $dateLabel !== '' ? ' ' : '';
            $out .= '<span class="' . self::escAttr($timePartClass) . '">' . self::escHtml($timeLabel) . '</span>';
        }

        $out .= '</time>';

        return $out;
    }

    /**
     * @param array{timestamp:int|null,has_time:bool,invalid:bool} $end
     */
    private static function plainOutput(
        string $startDate,
        string $startTime,
        array $end,
        string $endDate,
        string $endTime,
        bool $sameDayRange,
        string $timezone
    ): string {
        $parts = [$startDate];

        if ($startTime !== '') {
            $parts[] = $startTime;
        }

        if ($end['timestamp']) {
            if ($sameDayRange) {
                if ($endTime !== '') {
                    $parts[] = '-';
                    $parts[] = $endTime;
                }
            } else {
                $parts[] = '-';
                $parts[] = $endDate;

                if ($endTime !== '') {
                    $parts[] = $endTime;
                }
            }
        }

        if ($timezone !== '') {
            $parts[] = $timezone;
        }

        return \implode(
            ' ',
            \array_map(
                static fn (string $part): string => self::escHtml($part),
                $parts
            )
        );
    }

    /**
     * @param array{timestamp:int|null,has_time:bool,invalid:bool} $start
     * @param array{timestamp:int|null,has_time:bool,invalid:bool} $end
     */
    private static function htmlOutput(
        array $start,
        string $startDate,
        string $startTime,
        array $end,
        string $endDate,
        string $endTime,
        bool $sameDayRange,
        string $timezone,
        DateTimeZone $renderTimezone
    ): string {
        $out  = '<span class="' . self::escAttr(self::className('wrapper')) . '">';
        $out .= self::timeHtml(
            $start,
            self::className('start'),
            self::className('start_date'),
            self::className('start_time'),
            $startDate,
            $startTime,
            $renderTimezone
        );

        if ($end['timestamp']) {
            if ($sameDayRange) {
                if ($endTime !== '') {
                    $out .= '<span class="' . self::escAttr(self::className('separator')) . '"> - </span>';
                    $out .= self::timeHtml(
                        $end,
                        self::className('end'),
                        self::className('end_date'),
                        self::className('end_time'),
                        '',
                        $endTime,
                        $renderTimezone
                    );
                }
            } else {
                $out .= '<span class="' . self::escAttr(self::className('separator')) . '"> - </span>';
                $out .= self::timeHtml(
                    $end,
                    self::className('end'),
                    self::className('end_date'),
                    self::className('end_time'),
                    $endDate,
                    $endTime,
                    $renderTimezone
                );
            }
        }

        if ($timezone !== '') {
            $out .= ' <span class="' . self::escAttr(self::className('timezone')) . '">' . self::escHtml($timezone) . '</span>';
        }

        $out .= '</span>';

        return $out;
    }

    private static function className(string $key): string
    {
        return isset(self::CLASSES[$key]) && \is_string(self::CLASSES[$key])
            ? self::CLASSES[$key]
            : '';
    }
}

}

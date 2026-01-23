<?php
/**
 * Control comment behavior and related admin UI.
 *
 * Hard-coded defaults:
 * - Comments disabled globally
 * - Disabled for posts and pages
 * - CPTs only allowed if they explicitly support comments (none by default)
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Services\Core;

use MacCore\Contracts\Service;
use WP_Admin_Bar;
use WP_Post;

final class ControlComments implements Service
{
    /**
     * Master switch.
     */
    private const ENABLED = false;

    /**
     * Per-type switches.
     */
    private const ENABLE_POSTS = false;
    private const ENABLE_PAGES = false;

    public function register(): void
    {
        // Enforce open/closed state.
        \add_filter('comments_open', [$this, 'filter_comments_open'], 10, 2);
        \add_filter('pings_open',    [$this, 'filter_comments_open'], 10, 2);

        // Hide existing comments output when disallowed.
        \add_filter('comments_array', [$this, 'filter_comments_array'], 10, 2);

        // Remove comment support from post types.
        \add_action('init', [$this, 'enforce_post_type_support'], 20);

        // Admin UI cleanup.
        \add_action('admin_menu', [$this, 'cleanup_admin_menu'], 99);
        \add_action('network_admin_menu', [$this, 'cleanup_admin_menu'], 99);
        \add_action('wp_dashboard_setup', [$this, 'cleanup_dashboard'], 20);
        \add_action('admin_bar_menu', [$this, 'cleanup_admin_bar'], 100);

        // Block direct access to comments screens when disabled.
        \add_action('load-edit-comments.php', [$this, 'block_comments_screen']);
    }

    public function filter_comments_open(bool $open, int|WP_Post|null $post): bool
    {
        if (! self::ENABLED) {
            return false;
        }

        $post_id = \is_object($post) ? (int) $post->ID : (int) $post;
        if ($post_id <= 0) {
            return false;
        }

        return $this->is_allowed_for_post($post_id);
    }

    public function filter_comments_array(array $comments, int $post_id): array
    {
        if (! self::ENABLED || ! $this->is_allowed_for_post($post_id)) {
            return [];
        }

        return $comments;
    }

    public function enforce_post_type_support(): void
    {
        foreach (\get_post_types(['public' => true], 'names') as $post_type) {
            $allow = match ($post_type) {
                'post' => self::ENABLED && self::ENABLE_POSTS,
                'page' => self::ENABLED && self::ENABLE_PAGES,
                default => self::ENABLED && \post_type_supports($post_type, 'comments'),
            };

            if (! $allow) {
                \remove_post_type_support($post_type, 'comments');
                \remove_post_type_support($post_type, 'trackbacks');
            }
        }
    }

    public function cleanup_admin_menu(): void
    {
        if (! self::ENABLED || ! $this->any_comments_supported()) {
            \remove_menu_page('edit-comments.php');
        }
    }

    public function cleanup_dashboard(): void
    {
        if (! self::ENABLED || ! $this->any_comments_supported()) {
            \remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
        }
    }

    public function cleanup_admin_bar(WP_Admin_Bar $bar): void
    {
        if (! \is_admin_bar_showing()) {
            return;
        }

        if (! self::ENABLED || ! $this->any_comments_supported()) {
            $bar->remove_node('comments');
        }
    }

    public function block_comments_screen(): void
    {
        if (! self::ENABLED || ! $this->any_comments_supported()) {
            \wp_safe_redirect(\admin_url('index.php'));
            exit;
        }
    }

    private function is_allowed_for_post(int $post_id): bool
    {
        if (! self::ENABLED) {
            return false;
        }

        $post_type = (string) \get_post_type($post_id);

        return match ($post_type) {
            'post' => self::ENABLE_POSTS,
            'page' => self::ENABLE_PAGES,
            default => \post_type_supports($post_type, 'comments'),
        };
    }

    private function any_comments_supported(): bool
    {
        if (self::ENABLED && (self::ENABLE_POSTS || self::ENABLE_PAGES)) {
            return true;
        }

        foreach (\get_post_types(['public' => true], 'names') as $post_type) {
            if (! \in_array($post_type, ['post', 'page'], true)
                && \post_type_supports($post_type, 'comments')
            ) {
                return true;
            }
        }

        return false;
    }
}

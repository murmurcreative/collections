<?php

namespace Murmur\Collections;

/**
 * Plugin Name:  📄 Collections
 * Plugin URI:   https://murmurcreative.com
 * Description:  This plugin does not provide any userspace functionality on its own; its purpose is to provide tools
 * for creating custom post types. Version:      1.0.0 Author:       Murmur Creative Author URI:
 * https://murmurcreative.com License:      Proprietary
 */
abstract class Collection
{
    protected $type;
    protected $label;
    protected $arguments;
    protected $names;

    /**
     * Returns the name of this post type, i.e. `sandwich-fixing`.
     * Should be singular.
     *
     * @return string
     */
    protected abstract function type(): string;

    /**
     * Returns the human name of this post type, i.e. `Sandwich Fixing`.
     * Should be singular.
     *
     * @return string
     */
    protected abstract function label(): string;

    /**
     * Returns array of arguments for post type.
     *
     * Can be overridden by extending class to provide custom arguments;
     * otherwise returns an empty array (which will apply only default values).
     *
     * @return array
     */
    protected function arguments(): array
    {
        return [];
    }

    /**
     * Returns array of argument for post type names.
     *
     * Can be overridden by extending class to provide custom arguments;
     * otherwise returns an empty array (which will apply only default values.)
     *
     * @return array
     */
    protected function names(): array
    {
        return [];
    }

    /**
     * Provides an opportunity to run startup tasks.
     *
     * By default this does nothing, but it can be used in extended classes
     * to run various startup tasks, such as modifying post type settings via
     * methods.
     */
    protected function setup(): void
    {
        /** Do something */
    }

    public function __construct()
    {
        $this->type  = $this->type();
        $this->label = $this->label();
        $this->names = array_merge([
            'singular' => $this->label,
            'slug'     => $this->type,
        ], $this->names());

        $this->arguments = array_merge([
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
        ], $this->arguments());

        $this->setup();
    }

    /**
     * Enable Gutenberg on this post type.
     *
     * @param bool $enable
     *
     * @return $this
     */
    protected function supportsGutenberg(bool $enable)
    {
        if ( ! isset($this->arguments['show_in_rest'])) {
            $this->arguments['show_in_rest'] = true;
        }

        $this->arguments['block_editor'] = $enable;

        return $this;
    }

    /**
     * Disable pagination/show all posts on the archive page
     *
     * @return $this
     */
    protected function noPagination()
    {
        $this->safelySetArrayArgument('archive', true, 'nopaging');

        return $this;
    }

    /**
     * Set the icon.
     *
     * For supported values, see: https://developer.wordpress.org/reference/functions/register_post_type/#menu_icon
     *
     * @param string $string
     *
     * @return $this
     */
    protected function icon(string $string)
    {
        $this->arguments['menu_icon'] = $string;

        return $this;
    }

    /**
     * Enable or disable title field
     *
     * @param bool $enabled
     *
     * @return Collection
     */
    protected function supportsTitle(bool $enabled = true)
    {
        if ($enabled) {
            $this->safelySetArrayArgument('supports', 'title');
        } else {
            $this->safelyUnsetArrayValue('supports', 'title');
        }

        return $this;
    }

    /**
     * Enable or disable editor field
     *
     * @param bool $enabled
     *
     * @return Collection
     */
    protected function supportsEditor(bool $enabled = true)
    {
        if ($enabled) {
            $this->safelySetArrayArgument('supports', 'editor');
        } else {
            $this->safelyUnsetArrayValue('supports', 'editor');
        }

        return $this;
    }

    /**
     * Enable or disable excerpt field
     *
     * @param bool $enabled
     *
     * @return Collection
     */
    protected function supportsExcerpt(bool $enabled = true)
    {
        if ($enabled) {
            $this->safelySetArrayArgument('supports', 'excerpt');
        } else {
            $this->safelyUnsetArrayValue('supports', 'excerpt');
        }

        return $this;
    }

    /**
     * Enable or disable thumbnail field
     *
     * @param bool $enabled
     *
     * @return Collection
     */
    protected function supportsThumbnail(bool $enabled = true)
    {
        if ($enabled) {
            $this->safelySetArrayArgument('supports', 'thumbnail');
        } else {
            $this->safelyUnsetArrayValue('supports', 'thumbnail');
        }

        return $this;
    }

    /**
     * Set whether or not this post type supports a featured image.
     *
     * @param bool $enabled
     *
     * @return $this
     */
    protected function supportsFeaturedImage(bool $enabled = true)
    {
        return $this->supportsThumbnail($enabled);
    }

    /**
     * Sets the number of items per paginated page.
     *
     * If noPagination() is active, this will do nothing.
     *
     * @param int $number
     *
     * @return $this
     */
    protected function perPage(int $number)
    {
        if (isset($this->arguments['archive']) && isset($this->arguments['archive']['nopaging']) && true === $this->arguments['archive']['nopaging']) {
            return $this;
        }

        add_action('pre_get_posts', function (\WP_Query $query) use ($number) {
            if ($query->is_post_type_archive([$this->type]) && ! is_admin()) {
                $query->set('posts_per_page', $number);
            }
        });

        return $this;
    }

    /**
     * Set labels in bulk as an array.
     *
     * For options see: https://developer.wordpress.org/reference/functions/get_post_type_labels/
     *
     * @param array $labels
     *
     * @return Collection
     */
    protected function labels(array $labels)
    {
        $currentLabels = [];
        if (isset($this->arguments['labels'])) {
            $currentLabels = $this->arguments['labels'];
        }

        return $this->safelySetArrayArgument('labels', array_merge($currentLabels, $labels));
    }

    /**
     * Set the menu name.
     *
     * @param string $label
     *
     * @return Collection
     */
    protected function labelMenu(string $label)
    {
        return $this->safelySetArrayArgument('labels', $label, 'menu_name');
    }

    /**
     * Change the name of the "Featured Image" field
     *
     * @param string $label
     *
     * @return $this
     */
    protected function featuredImageName(string $label)
    {
        $this->arguments['featured_image'] = $label;

        return $this;
    }

    /**
     * Change the "Enter title here" text
     *
     * @param string $label
     *
     * @return $this
     */
    protected function enterTitleString(string $label)
    {
        $this->arguments['enter_title_here'] = $label;

        return $this;
    }

    /**
     * Enable/disable Quick Edit (default true)
     *
     * @param bool $enable
     *
     * @return $this
     */
    protected function enableQuickEdit(bool $enable)
    {
        $this->arguments['quick_edit'] = $enable;

        return $this;
    }

    /**
     * Add/remove from Dashboard's "At a Glance" (default true)
     *
     * @param bool $enable
     *
     * @return $this
     */
    protected function atAGlance(bool $enable)
    {
        $this->arguments['dashboard_glance'] = $enable;

        return $this;
    }

    /**
     * Add/remove from Dashboard's "Recently Published" (default true)
     *
     * @param bool $enable
     *
     * @return $this
     */
    protected function recentlyPublished(bool $enable)
    {
        $this->arguments['dashboard_activity'] = $enable;

        return $this;
    }

    /**
     * Register the post type
     *
     * The post type will not actually exist until you call this method.
     */
    public function register()
    {
        if ( ! function_exists('\\register_extended_post_type')) {
            $this->installEcptsWarning();

            return false;
        }

        add_action('init', function () {
            register_extended_post_type($this->type, $this->arguments, $this->names);
        });
    }

    /**
     * Check for the existence of an array-type argument, and create it if it doesn't exist before setting a value
     * inside it.
     *
     * @param string      $arrayName
     * @param mixed       $argument
     * @param string|null $key
     *
     * @return Collection
     */
    protected function safelySetArrayArgument(string $arrayName, $argument, string $key = null)
    {
        if ( ! isset($this->arguments[$arrayName])) {
            $this->arguments[$arrayName] = [];
        }
        if (null !== $key) {
            $this->arguments[$arrayName][$key] = $argument;
        } else {
            $this->arguments[$arrayName][] = $argument;
        }

        return $this;
    }

    /**
     * Remove something from an array by the value
     *
     * @param string $arrayName
     * @param        $value
     *
     * @return $this
     */
    protected function safelyUnsetArrayValue(string $arrayName, $value)
    {
        if ( ! isset($this->arguments[$arrayName])) {
            return $this; // The array doesn't exist, so nothing to unset
        }

        $this->arguments[$arrayName] = array_filter($this->arguments[$arrayName], function ($row) use ($value) {
            return $row !== $value;
        });

        return $this;
    }

    /**
     * Remove something from an array by key
     *
     * @param string $arrayName
     * @param string $key
     *
     * @return $this
     */
    protected function safelyUnsetArrayKey(string $arrayName, string $key)
    {
        if ( ! isset($this->arguments[$arrayName])) {
            return $this; // The array doesn't exist, so nothing to unset
        }

        unset($this->arguments[$arrayName][$key]);

        return $this;
    }

    protected function installEcptsWarning()
    {
        add_action('admin_notices', function () {
            echo <<<EOT
<div class="notice notice-error">
    <p>You must install <a href="https://github.com/johnbillion/extended-cpts" target="_blank" rel="noopener noreferrer">Extended CPTs</a> in order to use the <strong>{$this->label}</strong> post type!</p>
</div>
EOT;
        });
    }

    /**
     * Prefix a string w/ the post type string.
     * Optionally pass a second argument to override the default "_" separator.
     *
     * This is especially useful for things like field naming.
     *
     * @param string $string
     * @param string $separator
     *
     * @return string
     */
    public function prefix(string $string, string $separator = "_"): string
    {
        return sprintf("%s%s%s", $this->type, $separator, $string);
    }
}

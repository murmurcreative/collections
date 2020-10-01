<?php

namespace Murmur\Collections;

/**
 * Plugin Name:  📄 Collections
 * Plugin URI:   https://murmurcreative.com
 * Description:  This plugin does not provide any userspace functionality on its own; its purpose is to provide tools
 * for creating custom post types. Version:      1.0.0 Author:       Murmur Creative Author URI:
 * https://murmurcreative.com License:      Proprietary
 */
class Collection
{
    private $type;
    private $label;
    private $arguments;
    private $names;

    public function __construct($type, $label, $arguments = [], $names = [])
    {
        $names = array_merge([
            'singular' => $label,
            'slug'     => $type,
        ], $names);

        $arguments = array_merge([
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
        ], $arguments);

        $this->type      = $type;
        $this->label     = $label;
        $this->arguments = $arguments;
        $this->names     = $names;
    }

    /**
     * Enable Gutenberg on this post type.
     *
     * @param bool $enable
     *
     * @return $this
     */
    public function supportsGutenberg(bool $enable)
    {
        if (!isset($this->arguments['show_in_rest'])) {
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
    public function noPagination()
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
    public function icon(string $string)
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
    public function supportsTitle(bool $enabled = true)
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
    public function supportsEditor(bool $enabled = true)
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
    public function supportsExcerpt(bool $enabled = true)
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
    public function supportsThumbnail(bool $enabled = true)
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
    public function supportsFeaturedImage(bool $enabled = true)
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
    public function perPage(int $number)
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
    public function labels(array $labels)
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
    public function labelMenu(string $label)
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
    public function featuredImageName(string $label)
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
    public function enterTitleString(string $label)
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
    public function enableQuickEdit(bool $enable)
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
    public function atAGlance(bool $enable)
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
    public function recentlyPublished(bool $enable)
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
    private function safelySetArrayArgument(string $arrayName, $argument, string $key = null)
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
    private function safelyUnsetArrayValue(string $arrayName, $value)
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
    private function safelyUnsetArrayKey(string $arrayName, string $key)
    {
        if ( ! isset($this->arguments[$arrayName])) {
            return $this; // The array doesn't exist, so nothing to unset
        }

        unset($this->arguments[$arrayName][$key]);

        return $this;
    }

    private function installEcptsWarning()
    {
        add_action('admin_notices', function () {
            echo <<<EOT
<div class="notice notice-error">
    <p>You must install <a href="https://github.com/johnbillion/extended-cpts" target="_blank" rel="noopener noreferrer">Extended CPTs</a> in order to use the <strong>{$this->label}</strong> post type!</p>
</div>
EOT;
        });
    }
}

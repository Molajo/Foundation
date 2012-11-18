<?php
/**
 * @package    Molajo
 * @copyright  2012 Individual Molajo Contributors. All rights reserved.
 * @license    GNU GPL v 2, or later and MIT, see License folder
 */
namespace Molajo\Helper;

use Molajo\Service\Services;
use Molajo\Helpers;

defined('MOLAJO') or die;

/**
 * ThemeHelper
 *
 * @package       Molajo
 * @subpackage    Helper
 * @since         1.0
 */
Class ThemeHelper
{
    /**
     * Static instance
     *
     * @var    object
     * @since  1.0
     */
    protected static $instance;

    /**
     * getInstance
     *
     * @static
     * @return bool|object
     * @since  1.0
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new ThemeHelper();
        }

        return self::$instance;
    }

    /**
     * Get requested theme data
     *
     * @param int $theme_id
     *
     * @return boolean
     * @since   1.0
     */
    public function get($theme_id = 0)
    {

        if ((int) $theme_id == 0) {
            $theme_id = Services::Registry()->get('Configuration', 'application_default_theme_id');
        }

        Services::Registry()->set('Parameters', 'theme_id', (int) $theme_id);

        $node = Helpers::Extension()->getExtensionNode((int) $theme_id);

        Services::Registry()->set('Parameters', 'theme_path_node', $node);

        Services::Registry()->set('Parameters', 'theme_path', $this->getPath($node));
        Services::Registry()->set('Parameters', 'theme_namespace', $this->getNamespace($node));
        Services::Registry()->set('Parameters', 'theme_path_include', $this->getPath($node) . '/index.php');
        Services::Registry()->set('Parameters', 'theme_path_url', $this->getPathURL($node));
        Services::Registry()->set('Parameters', 'theme_favicon', $this->getFavicon($node));

        /** Retrieve the query results */
        $item = Helpers::Extension()->get($theme_id, 'Theme', $node, 1);

        /** Not found: get system default */
        if (count($item) == 0) {

            /** System Default */
            if ($theme_id == Helpers::Extension()->getInstanceID(CATALOG_TYPE_THEME, 'System')) {
                // 500 error
                Services::Error()->set(500, 'System Theme not found');

                return false;
            }

            /** System default */
            $theme_id = Helpers::Extension()->getInstanceID(CATALOG_TYPE_THEME, 'System');

            Services::Registry()->set('Parameters', 'theme_id', (int) $theme_id);

            $node = Helpers::Extension()->getExtensionNode((int) $theme_id);

            Services::Registry()->set('Parameters', 'theme_path_node', $node);

            Services::Registry()->set('Parameters', 'theme_path', $this->getPath($node));
            Services::Registry()->set('Parameters', 'theme_namespace', $this->getNamespace($node));
            Services::Registry()->set('Parameters', 'theme_path_include', $this->getPath($node) . '/index.php');
            Services::Registry()->set('Parameters', 'theme_path_url', $this->getPathURL($node));
            Services::Registry()->set('Parameters', 'theme_favicon', $this->getFavicon($node));

            $item = Helpers::Extension()->get($theme_id, 'Theme', $node, 1);

            if (count($item) == 0) {
                Services::Error()->set(500, 'Theme not found');

                return false;
            }
        }

        Services::Registry()->set('Parameters', 'theme_title', $item->title);
        Services::Registry()->set('Parameters', 'theme_translation_of_id', (int) $item->translation_of_id);
        Services::Registry()->set('Parameters', 'theme_language', $item->language);
        Services::Registry()->set('Parameters', 'theme_view_group_id', $item->catalog_view_group_id);
        Services::Registry()->set('Parameters', 'theme_catalog_id', $item->catalog_id);
        Services::Registry()->set('Parameters', 'theme_catalog_type_id', (int) $item->catalog_view_group_id);
        Services::Registry()->set('Parameters', 'theme_catalog_type_title', $item->catalog_types_title);

        Services::Registry()->set('Parameters', 'theme_model_registry', $item->model_registry);

        /** Merge in each custom field namespace  */
        $customFieldTypes = Services::Registry()->get($item->model_registry, 'CustomFieldGroups');

        if (count($customFieldTypes) > 0) {
            foreach ($customFieldTypes as $customFieldName) {
                $customFieldName = ucfirst(strtolower($customFieldName));
                Services::Registry()->merge($item->model_registry . $customFieldName, $customFieldName);
                Services::Registry()->deleteRegistry($item->model_registry . $customFieldName);
            }
        }

        return true;
    }

    /**
     * getPath - Return path for selected Theme
     *
     * @param   $node
     *
     * @return bool|string
     * @since   1.0
     */
    public function getPath($node)
    {
        if (file_exists(EXTENSIONS_THEMES . '/' . ucfirst(strtolower($node)) . '/' . 'index.php')) {
            return EXTENSIONS_THEMES . '/' . ucfirst(strtolower($node));
        }

        if (file_exists(CORE_THEMES . '/' . ucfirst(strtolower($node)) . '/' . 'index.php')) {
            return CORE_THEMES . '/' . ucfirst(strtolower($node));
        }

        return false;
    }

    /**
     * getPathURL - Return path for selected Theme
     *
     * @param   $node
     *
     * @return bool|string
     * @since   1.0
     */
    public function getPathURL($node)
    {
        if (file_exists(EXTENSIONS_THEMES . '/' . ucfirst(strtolower($node)) . '/' . 'index.php')) {
            return EXTENSIONS_THEMES_URL . '/' . ucfirst(strtolower($node));
        }

        if (file_exists(CORE_THEMES . '/' . ucfirst(strtolower($node)) . '/' . 'index.php')) {
            return CORE_THEMES_URL . '/' . ucfirst(strtolower($node));
        }

        return false;
    }

    /**
     * getNamespace - Return path for selected Theme
     *
     * @param   $node
     *
     * @return bool|string
     * @since   1.0
     */
    public function getNamespace($node)
    {
        if (file_exists(EXTENSIONS_THEMES . '/' . ucfirst(strtolower($node)) . '/' . 'index.php')) {
            return 'Extension\\Theme\\' . ucfirst(strtolower($node));
        }

        if (file_exists(CORE_THEMES . '/' . ucfirst(strtolower($node)) . '/' . 'index.php')) {
            return 'Molajo\\Theme\\' . ucfirst(strtolower($node));
        }

        return false;
    }

    /**
     * getFavicon - Retrieve Favicon Path
     *
     * Can be located in:
     *  - Themes/images/ folder (priority 1)
     *  - Root of the website (priority 2)
     *
     * @param  $node
     *
     * @return mixed
     * @since   1.0
     */
    public function getFavicon($node)
    {
        $path = EXTENSIONS_THEMES . '/' . ucfirst(strtolower($node)) . '/images/';
        if (file_exists($path . 'favicon.ico')) {
            return EXTENSIONS_THEMES_URL . '/' . ucfirst(strtolower($node)) . '/images/favicon.ico';
        }

        $path = BASE_FOLDER;
        if (file_exists($path . 'favicon.ico')) {
            return BASE_URL . '/favicon.ico';
        }

        return false;
    }
}

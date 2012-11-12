<?php
/**
 * @package    Molajo
 * @copyright  2012 Individual Molajo Contributors. All rights reserved.
 * @license    GNU GPL v 2, or later and MIT, see License folder
 */
namespace Molajo\Plugin\Pagetypes;

use Molajo\Plugin\Plugin\Plugin;
use Molajo\Service\Services;

defined('MOLAJO') or die;

/**
 * @package     Molajo
 * @subpackage  Plugin
 * @since       1.0
 */
class PagetypesPlugin extends Plugin
{
    /**
     * Generates list of Pagetypes
     *
     * This can be moved to onBeforeParse when Plugin ordering is in place
     *
     * @return boolean
     * @since   1.0
     */
    public function onAfterRoute()
    {
        $folders = Services::Filesystem()->folderFolders(
            EXTENSIONS . '/Menuitem'
        );

        if (count($folders) === 0 || $folders === false) {
            $menuitemLists = array();
        } else {
            $page_type_list = $folders;
        }


		$folders = Services::Filesystem()->folderFolders(
			MOLAJO_FOLDER . '/Menuitem'
		);

		if (count($folders) === 0 || $folders === false) {
		} else {
			$new = array_merge($page_type_list, $folders);
			$page_type_list = $new;
		}

        $resourceFolders = Services::Filesystem()->folderFolders(
            Services::Registry()->get('Parameters', 'extension_path') . '/Menuitem'
        );

        if (count($resourceFolders) === 0 || $resourceFolders === false) {
            $resourceLists = array();
        } else {
            $resourceLists = $resourceFolders;
        }

        $new = array_merge($page_type_list, $resourceLists);

        $newer = array_unique($new);
        sort($newer);

        $menuitems = array();
        foreach ($newer as $item) {
            $row = new \stdClass();
            $row->value = $item;
            $row->id = $item;
            $menuitems[] = $row;
        }

        Services::Registry()->set('Datalist', 'Pagetypes', $menuitems);

        return true;
    }
}
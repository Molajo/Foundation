<?php
/**
 * @package    Molajo
 * @copyright  2012 Individual Molajo Contributors. All rights reserved.
 * @license    GNU GPL v 2, or later and MIT, see License folder
 */
namespace Molajo\Plugin\Edit;

use Molajo\Plugin\Content\ContentPlugin;
use Molajo\Service\Services;
use Molajo\Helpers;

defined('MOLAJO') or die;

/**
 * @package     Molajo
 * @subpackage  Plugin
 * @since       1.0
 */
class EditPlugin extends ContentPlugin
{
	/**
	 * Prepares data for Edit
	 *
	 * @return boolean
	 * @since   1.0
	 */
	public function onBeforeParse()
	{
		if (APPLICATION_ID == 2) {
		} else {
			return true;
		}

		if (strtolower($this->get('template_view_path_node')) == 'edit') {
		} else {
			return true;
		}

		$resource_table_registry = ucfirst(strtolower($this->get('model_name')))
			. ucfirst(strtolower($this->get('model_type')));

		/** Retrieve Resource Field List */
		$fieldArray = Services::Form()->getFieldlist($this->get('model_type'), $this->get('model_name'));
		Services::Registry()->set('Plugindata', $resource_table_registry . 'Fields', $fieldArray);

		/** Get Actual Data for matching to Fields */
		$controllerClass = 'Molajo\\MVC\\Controller\\Controller';
		$connect = new $controllerClass();
		$results = $connect->connect($this->get('model_type'), $this->get('model_name'));
		if ($results === false) {
			return false;
		}

		$connect->set('get_customfields', 2);
		$connect->set('use_special_joins', 1);
		$connect->set('process_plugins', 1);
		$primary_prefix = $connect->get('primary_prefix');
		$primary_key = $connect->get('primary_key');
		$id = $this->get('content_id');

		$connect->model->query->where($connect->model->db->qn($primary_prefix)
				. '.' . $connect->model->db->qn($primary_key) . ' = ' . (int) $id);

		$item = $connect->getData('item');

		$this->table_registry_name = ucfirst(strtolower($this->get('model_name')))
			. ucfirst(strtolower($this->get('model_type')));

		/** Get configuration menuitem settings for this resource */
		$menuitem = Helpers::Content()->getResourceMenuitemParameters(
			'Configuration',
			$this->get('criteria_extension_instance_id')
		);

		/** Tab Group Class */
		$tab_class = Services::Registry()->get('ConfigurationMenuitemParameters', 'configuration_tab_class');

		/** Create Tabs */
		$namespace = 'Edit';

		$tab_array = Services::Registry()->get('ConfigurationMenuitemParameters', 'editor_tab_array');

		$tabs = Services::Form()->setTabArray(
			$this->get('model_type'),
			$this->get('model_name'),
			$namespace,
			$tab_array,
			'editor_tab_',
			'Edit',
			'Edittab',
			$tab_class,
			$this->get('extension_instance_id'),
			$item
		);

		$this->set('model_name', 'Plugindata');
		$this->set('model_type', 'dbo');
		$this->set('model_query_object', 'getPlugindata');
		$this->set('model_parameter', 'Edit');

		$this->parameters['model_name'] = 'Plugindata';
		$this->parameters['model_type'] = 'dbo';

		Services::Registry()->set('Plugindata', 'Edit', $tabs);


		/**
		echo '<pre>';
		var_dump($tabs);
		echo '</pre>';



		echo '<pre>';
		var_dump(Services::Registry()->get('Plugindata', 'EditEditmain'));
		echo '</pre>';

		echo '<pre>';
		var_dump(Services::Registry()->get('Plugindata', 'EditEditpublish'));
		echo '</pre>';

		echo '<pre>';
		var_dump(Services::Registry()->get('Plugindata', 'EditEditpermissions'));
		echo '</pre>';

		echo '<pre>';
		var_dump(Services::Registry()->get('Plugindata', 'EditEditseo'));
		echo '</pre>';
		*/
		return true;
	}
}

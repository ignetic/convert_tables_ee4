<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Convert Tables EE4 Control Panel File
 *
 * @package		Convert_tables_ee4
 * @category	Module
 * @author		Simon Andersohn
 * @link			
 */

require_once PATH_THIRD.'convert_tables_ee4/config.php';


class Convert_tables_ee4_mcp {

	public $class_name = CONVERT_TABLES_EE4_CLASS_NAME; 
	public $version = CONVERT_TABLES_EE4_VERSION;

	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$alert = ee('CP/Alert')->makeInline($this->class_name)
			->asWarning()
			->withTitle(lang('warning'))
			->addToBody(lang('warning_desc'))
			->now();
	}
	
	// ----------------------------------------------------------------

	/**
	 * Index Function
	 *
	 * @return 	void
	 */
	public function index()
	{
		
		if (defined('APP_VER') && version_compare(APP_VER, '4.0.0', '<'))
		{
			// Final view variables we need to render the form
			$vars = array(
			  'sections' => array(),
			  'base_url' => ee('CP/URL', 'addons/settings/convert_tables_ee4'),
			  'cp_page_title' => lang('convert_tables_only_for_ee4'),
			  'save_btn_text' => 'cancel',
			  'save_btn_text_working' => 'cancel',
			);

			return ee('View')->make('ee:_shared/form')->render($vars);
		}
		
		$ee4_fields = array();
		$legacy_fields = array();
		$disabled_fields = array();
		
		
		// exclude fluid related fields
		if (ee()->db->table_exists('fluid_field_data'))
		{
			$fluid_data = ee()->db
				->select('field_id')
				->group_by('field_id')
				->get('fluid_field_data')
				->result_array();
				
			$disabled_fields = array_column($fluid_data, 'field_id');
		}

		// get field info
		$fields = ee()->db
			->select('field_id, field_name, field_label, field_type')
			->order_by('field_id')
			->get('channel_fields')
			->result_array();

		foreach ($fields as $field)
		{
			if (ee()->db->table_exists('channel_data_field_'.$field['field_id']))
			{
				$ee4_fields[$field['field_id']] = '['.$field['field_id'].'] '.$field['field_label'].': '.$field['field_name'].' - ['.$field['field_type'].']';
				if ($field['field_type'] == 'fluid_field')
				{
					$disabled_fields[] = $field['field_id'];
				}
			}
			/*
			else
			{
				$legacy_fields[$field['field_id']] = '['.$field['field_id'].'] '.$field['field_label'].': '.$field['field_name'].' - ['.$field['field_type'].']';
			}
			*/
			if (ee()->db->field_exists('field_id_'.$field['field_id'], 'exp_channel_data'))
			{
				$legacy_fields[$field['field_id']] = '['.$field['field_id'].'] '.$field['field_label'].': '.$field['field_name'].' - ['.$field['field_type'].']';
			}
		}

		// Form definition array
		$vars['alerts_name'] = $this->class_name;
		$vars['sections'] = array(
			array(
				array(
					'title' => 'legacy_fields',
					'desc' => 'legacy_fields_desc',
					'fields' => array(
						'legacy_fields' => array(
							'type' => 'checkbox',
							'choices' => $legacy_fields
						)
					)
				),
				array(
					'title' => 'ee4_fields',
					'desc' => 'ee4_fields_desc',
					'fields' => array(
						'ee4_fields' => array(
						'type' => 'checkbox',
						'choices' => $ee4_fields,
							'disabled_choices' => $disabled_fields
					)
				  )
				)
			)
		);

		// Final view variables we need to render the form
		$vars += array(
			'base_url' => ee('CP/URL', 'addons/settings/convert_tables_ee4/convert_channels'),
			'cp_page_title' => lang('convert_channel_fields'),
			'save_btn_text' => 'convert_channel_fields',
			'save_btn_text_working' => 'btn_converting'
		);

		$this->create_sidebar();

		return ee('View')->make('ee:_shared/form')->render($vars);
	}


	public function categories()
	{
		
		$ee4_fields = array();
		$legacy_fields = array();

		$fields = ee()->db
			->select('field_id, field_name, field_label, field_type')
			->get('category_fields')
			->result_array();

		foreach ($fields as $field)
		{
			if (ee()->db->table_exists('category_field_data_field_'.$field['field_id']))
			{
				$ee4_fields[$field['field_id']] = '['.$field['field_id'].'] '.$field['field_label'].': '.$field['field_name'].' - ['.$field['field_type'].']';
			}
			else
			{
				$legacy_fields[$field['field_id']] = '['.$field['field_id'].'] '.$field['field_label'].': '.$field['field_name'].' - ['.$field['field_type'].']';
			}
		}

		// Form definition array
		$vars['alerts_name'] = $this->class_name;
		$vars['sections'] = array(
			array(
				array(
					'title' => 'legacy_fields',
					'desc' => 'legacy_fields_desc',
					'fields' => array(
						'legacy_fields' => array(
							'type' => 'checkbox',
							'choices' => $legacy_fields,
							//'value' => $checked_values
						)
					)
				),
				array(
					'title' => 'ee4_fields',
					'desc' => 'ee4_fields_desc',
					'fields' => array(
						'ee4_fields' => array(
							'type' => 'checkbox',
							'choices' => $ee4_fields,
							//'value' => $checked_values
						)
					)
				)
			)
		);

		// Final view variables we need to render the form
		$vars += array(
			'base_url' => ee('CP/URL', 'addons/settings/convert_tables_ee4/convert_categories'),
			'cp_page_title' => lang('convert_category_fields'),
			'save_btn_text' => 'convert_category_fields',
			'save_btn_text_working' => 'btn_converting'
		);

		$this->create_sidebar();

		return ee('View')->make('ee:_shared/form')->render($vars);
	}
	
	
	public function members()
	{
		$ee4_fields = array();
		$legacy_fields = array();

		$fields = ee()->db
			->select('m_field_id, m_field_name, m_field_label, m_field_type')
			->get('member_fields')
			->result_array();

		foreach ($fields as $field)
		{
			if (ee()->db->table_exists('member_data_field_'.$field['m_field_id']))
			{
				$ee4_fields[$field['m_field_id']] = '['.$field['m_field_id'].'] '.$field['m_field_label'].': '.$field['m_field_name'].' - ['.$field['m_field_type'].']';
			}
			else
			{
				$legacy_fields[$field['m_field_id']] = '['.$field['m_field_id'].'] '.$field['m_field_label'].': '.$field['m_field_name'].' - ['.$field['m_field_type'].']';
			}
		}

		// Form definition array
		$vars['alerts_name'] = $this->class_name;
		$vars['sections'] = array(
			array(
				array(
					'title' => 'legacy_fields',
					'desc' => 'legacy_fields_desc',
					'fields' => array(
						'legacy_fields' => array(
							'type' => 'checkbox',
							'choices' => $legacy_fields,
							//'value' => $checked_values
						)
					)
				),
				array(
					'title' => 'ee4_fields',
					'desc' => 'ee4_fields_desc',
					'fields' => array(
						'ee4_fields' => array(
							'type' => 'checkbox',
							'choices' => $ee4_fields,
							//'value' => $checked_values
						)
					)
				)
			)
		);

		// Final view variables we need to render the form
		$vars += array(
			'base_url' => ee('CP/URL', 'addons/settings/convert_tables_ee4/convert_members'),
			'cp_page_title' => lang('convert_member_fields'),
			'save_btn_text' => 'convert_member_fields',
			'save_btn_text_working' => 'btn_converting'
		);

		$this->create_sidebar();

		return ee('View')->make('ee:_shared/form')->render($vars);
	}
	

	/**
	 * Convert Channels Function
	 *
	 * @return 	void
	 */
	public function convert_channels()
	{
		$this->convert('channels');

		ee()->functions->redirect(ee('CP/URL', 'addons/settings/convert_tables_ee4'));
	}

	/**
	 * Convert Categories Function
	 *
	 * @return 	void
	 */
	public function convert_categories()
	{
		$this->convert('categories');

		ee()->functions->redirect(ee('CP/URL', 'addons/settings/convert_tables_ee4/categories'));
	}

	/**
	 * Convert Members Function
	 *
	 * @return 	void
	 */
	public function convert_members()
	{
		$this->convert('members');

		ee()->functions->redirect(ee('CP/URL', 'addons/settings/convert_tables_ee4/members'));
	}
	
	
	/**
	 * Convert Tables Function
	 *
	 * @return 	bool
	 */
	public function convert($type)
	{
		ini_set('memory_limit', -1);
		set_time_limit(0);
		
		ee()->load->dbforge();
		ee()->load->dbutil();
		//ee()->load->library('smartforge');
		
		$warning = FALSE;
		$success = FALSE;
		$field_prefix = '';

		switch ($type) {
			case "channels":
				$legacy_fields_table = 'channel_fields';
				$legacy_data_table = 'channel_data';
				$data_table_prefix = 'channel_data_field_';
				$field_key = 'entry_id';
				break;
			case "categories":
				$legacy_fields_table = 'category_fields';
				$legacy_data_table = 'category_field_data';
				$data_table_prefix = 'category_field_data_field_';
				$field_key = 'cat_id';
				break;
			case "members":
				$legacy_fields_table = 'member_fields';
				$legacy_data_table = 'member_data';
				$data_table_prefix = 'member_data_field_';
				$field_key = 'member_id';
				$field_prefix = 'm_';
				break;
			default:
				return FALSE;
		}
		
		$legacy_fields = ee()->input->post('legacy_fields');
		$ee4_fields = ee()->input->post('ee4_fields');
		
		$success_count = 0;
		$fail_count = 0;
	
		
		// Convert legacy fields to EE4
		if ( ! empty($legacy_fields))
		{
			// get field types
			
			$legacy_field_types = $this->get_field_types($legacy_data_table);

			foreach ($legacy_fields as $field_id)
			{
				if (empty($field_id)) continue;

				// check that field exists
				$results = ee()->db
					->select($field_prefix.'field_id')
					->where($field_prefix.'field_id', $field_id)
					->get($legacy_fields_table);
				
				if ($results->num_rows() === 0)
				{
					continue;
				}
				
				$table_name = $data_table_prefix.$field_id;
				
				// check external table exists (allow for existing tables e.g. where additional fluid fields have been created)
				if ( ! ee()->db->table_exists($table_name))
				{
					$field_id_name = $field_prefix.'field_id_'.$field_id;
					$field_ft_name = $field_prefix.'field_ft_'.$field_id;
					$field_dt_name = $field_prefix.'field_dt_'.$field_id;
				
					// create table
					$fields = array(
						'id' => array(
							'type'           => 'int',
							'constraint'     => 10,
							'null'           => FALSE,
							'unsigned'       => TRUE,
							'auto_increment' => TRUE
						)
					);
					
					$fields[$field_key] = array(
						'type'           => 'int',
						'constraint'     => 10,
						'null'           => FALSE,
						'unsigned'       => TRUE,
					);
					
					
					$fields[$field_id_name] = array(
						'type' => (isset($legacy_field_types[$field_id_name]['type']) ? $legacy_field_types[$field_id_name]['type'] : 'text')
					);
					if (isset($legacy_field_types[$field_id_name]['max_length']))
					{
						$fields[$field_id_name]['constraint'] = $legacy_field_types[$field_id_name]['max_length'];
					}
					$fields[$field_ft_name] = array(
						'type' => 'tinytext'
					);
					
					if (ee()->db->field_exists($field_dt_name, $legacy_data_table))
					{
						$fields[$field_dt_name]['type'] = (isset($legacy_field_types[$field_dt_name]['type']) ? $legacy_field_types[$field_dt_name]['type'] : 'tinytext');
						if (isset($legacy_field_types[$field_dt_name]['max_length'])) 
						{
							$fields[$field_dt_name]['constraint'] = $legacy_field_types[$field_dt_name]['max_length'];
						}
					}

					ee()->dbforge->add_field($fields);
					ee()->dbforge->add_key('id', TRUE);	
					ee()->dbforge->add_key($field_key);	
					ee()->dbforge->create_table($table_name, TRUE);
				}
				
				// copy table rows
				$sql = "INSERT INTO ".ee()->db->dbprefix($table_name)." ({$field_key}, {$field_id_name}, {$field_ft_name})
					SELECT {$field_key}, {$field_id_name}, {$field_ft_name} 
					FROM ".ee()->db->dbprefix($legacy_data_table)."
					WHERE {$field_id_name} IS NOT NULL AND {$field_id_name} != '' AND {$field_ft_name} IS NOT NULL";

				$query = ee()->db->query($sql);

				// Warning
				if ($query)
				{
		
					// remove old table columns
					ee()->dbforge->drop_column($legacy_data_table, $field_id_name);
					ee()->dbforge->drop_column($legacy_data_table, $field_ft_name);
					if (ee()->db->field_exists($field_dt_name, $legacy_data_table))
					{
						ee()->dbforge->drop_column($legacy_data_table, $field_dt_name);
					}
	
					$where[$field_prefix.'field_id'] = $field_id;

					// update field legacy settings
					$legacy_field_data = array($field_prefix.'legacy_field_data' => 'n');
					ee()->db->update(
						$legacy_fields_table,
						$legacy_field_data,
						$where
					);
					
					$success_count++;
					
				}
				else
				{
					$warning = TRUE;
					$fail_count++;
				}
				
				$success = TRUE;
			}
		}


		// Convert EE4 fields to legacy
		if ( ! empty($ee4_fields))
		{
			foreach ($ee4_fields as $field_id)
			{
				if (empty($field_id)) continue;
				
				// check that field exists
				$results = ee()->db
					->select($field_prefix.'field_id')
					->where($field_prefix.'field_id', $field_id)
					->get($legacy_fields_table);
				
				if ($results->num_rows() === 0)
				{
					continue;
				}
				
				$table_name = $data_table_prefix.$field_id;
				
				// check external table exists
				if ( ! ee()->db->table_exists($table_name))
				{
					continue;
				}
				
				$field_id_name = $field_prefix.'field_id_'.$field_id;
				$field_ft_name = $field_prefix.'field_ft_'.$field_id;
				$field_dt_name = $field_prefix.'field_dt_'.$field_id;
				
				if ( ! ee()->db->field_exists($field_id_name, $legacy_data_table))
				{

					// Find where to insert field after					
					$field_data = ee()->db->field_data($legacy_data_table);

					$after_field = '';
					$insert_after_field = NULL;
					foreach ($field_data as $fkey => $fval)
					{
						if ( strpos($fval->name, 'field_id_') === FALSE )
						{
							if ( (int) str_replace('field_ft_', '', $fval->name) > (int) $field_id)
							{
								$after_field = $insert_after_field;
								break;
							}
							$insert_after_field = $fval->name;
						}
					}

					
					// get field types
					$field_types = $this->get_field_types($table_name);

					// create table
					$fields = array();
					$fields[$field_id_name] = array(
						'type' => (isset($field_types[$field_id_name]['type']) ? $field_types[$field_id_name]['type'] : 'text')
					);
					if (isset($field_types[$field_id_name]['max_length']))
					{
						$fields[$field_id_name]['constraint'] = $field_types[$field_id_name]['max_length'];
					}
					
					$fields[$field_ft_name] = array(
						'type' => 'tinytext'
					);
					
					// date field
					if (ee()->db->field_exists($field_dt_name, $table_name))
					{
						$fields[$field_dt_name]['type'] = (isset($field_types[$field_dt_name]['type']) ? $field_types[$field_dt_name]['type'] : 'tinytext');
						if (isset($field_types[$field_dt_name]['max_length'])) 
						{
							$fields[$field_dt_name]['constraint'] = $field_types[$field_dt_name]['max_length'];
						}
					}

					// CREATE COLUMNS
					if ( ! empty($after_field))
					{
						ee()->dbforge->add_column($legacy_data_table, array($field_id_name => $fields[$field_id_name]), $after_field);
						if (isset($fields[$field_dt_name]))
						{
							ee()->dbforge->add_column($legacy_data_table, array($field_dt_name => $fields[$field_dt_name]), $field_id_name);
						}
						ee()->dbforge->add_column($legacy_data_table, array($field_ft_name => $fields[$field_ft_name]), $field_id_name);
					}
					else
					{
						ee()->dbforge->add_column($legacy_data_table, $fields);
					}
				}
							
				// copy table rows
				if (ee()->db->field_exists($field_dt_name, $table_name))
				{
					// date field type
					$sql = "
						INSERT INTO ".ee()->db->dbprefix($legacy_data_table)." ({$field_key}, {$field_id_name}, {$field_ft_name}, {$field_dt_name})
							SELECT t.{$field_key}, t.{$field_id_name}, t.{$field_ft_name}, t.{$field_dt_name} 
							FROM ".ee()->db->dbprefix($table_name)." t
						ON DUPLICATE KEY UPDATE {$field_id_name}=t.{$field_id_name}, {$field_ft_name}=t.{$field_ft_name}, {$field_dt_name}=t.{$field_dt_name}
					";
				}
				else
				{
					// all others
					$sql = "
						INSERT INTO ".ee()->db->dbprefix($legacy_data_table)." ({$field_key}, {$field_id_name}, {$field_ft_name})
							SELECT t.{$field_key}, t.{$field_id_name}, t.{$field_ft_name} 
							FROM ".ee()->db->dbprefix($table_name)." t
						ON DUPLICATE KEY UPDATE {$field_id_name}=t.{$field_id_name}, {$field_ft_name}=t.{$field_ft_name}
					";
				}

				$query = ee()->db->query($sql);

				// Warning
				if ($query)
				{
					// remove ee4 table
					if (ee()->dbforge->drop_table($table_name))
					{
						$where[$field_prefix.'field_id'] = $field_id;
						
						// update field legacy settings
						$legacy_field_data = array($field_prefix.'legacy_field_data' => 'y');
						ee()->db->update(
							$legacy_fields_table,
							$legacy_field_data,
							$where
						);
					}
					$success_count++;
				}
				else
				{
					$warning = TRUE;
					$fail_count++;
				}

				$success = TRUE;
			}
		}
	
		// clear up old table rows
		ee()->dbutil->optimize_table($legacy_data_table);
		
		if ($success && ! $warning)
		{
			ee('CP/Alert')->makeBanner('convert-tables-ee4')
			  ->asSuccess()
			  ->withTitle(lang('convert_tables_success'))
			  ->addToBody(lang('convert_tables_success_desc')." - Success: $success_count, Fail: $fail_count")
			  ->defer();
		}
		else
		{
			ee('CP/Alert')->makeBanner('convert-tables-ee4')
			  ->asWarning()
			  ->withTitle(lang('convert_tables_warning'))
			  ->addToBody(lang('convert_tables_warning_desc')." - Success: $success_count, Fail: $fail_count")
			  ->defer();
		}
		
		return TRUE;
	}
	
	
	/**
	 * Tables Schema
	 *
	 * @return 	void
	 */
	public function tables_engine() 
	{
		
		$db = ee()->db->database;
		
		$innodb_tables = array();
		$myisam_tables = array();
		
		$result = ee()->db->query("
			SELECT TABLE_NAME, TABLE_SCHEMA, ENGINE
			FROM information_schema.TABLES
			WHERE TABLE_SCHEMA = '$db'
		");

		foreach($result->result_array() as $row) 
		{
			if ($row['ENGINE'] === 'InnoDB')
			{
				$innodb_tables[$row['TABLE_NAME']] = $row['TABLE_NAME'];
			}
			else
			{
				$myisam_tables[$row['TABLE_NAME']] = $row['TABLE_NAME'];
			}
		}
	
		// Form definition array
		$vars['alerts_name'] = $this->class_name;
		$vars['sections'] = array(
		  array(
		  		  
			array(
			  'title' => 'myisam_tables',
			  'desc' => 'myisam_tables_desc',
			  'fields' => array(
				'myisam_tables' => array(
				  'type' => 'checkbox',
				  'choices' => $myisam_tables,
				  //'value' => $checked_values
				)
			  )
			),
			array(
			  'title' => 'innodb_tables',
			  'desc' => 'innodb_tables_desc',
			  'fields' => array(
				'innodb_tables' => array(
				  'type' => 'checkbox',
				  'choices' => $innodb_tables,
				  //'value' => $checked_values
				)
			  )
			),

		  )
		);

		// Final view variables we need to render the form
		$vars += array(
		  'base_url' => ee('CP/URL', 'addons/settings/convert_tables_ee4/convert_tables_engine'),
		  'cp_page_title' => lang('convert_tables_engine'),
		  'save_btn_text' => 'convert_tables_engine',
		  'save_btn_text_working' => 'btn_converting'
		);

		$this->create_sidebar();

		return ee('View')->make('ee:_shared/form')->render($vars);
	}
	
	
	
	/**
	 * Convert Tables Schema action
	 *
	 * @return 	bool
	 */
	public function convert_tables_engine() 
	{
		$myisam_tables = ee()->input->post('myisam_tables');
		$innodb_tables = ee()->input->post('innodb_tables');
		
		$success_count = 0;
		$fail_count = 0;
		
		// Convert to InnoDB
		if ( ! empty($myisam_tables))
		{
			foreach($myisam_tables as $table_name) 
			{
				if ( ! empty($table_name))
				{
					$success = @ee()->db->query("ALTER TABLE {$table_name} ENGINE=InnoDB");
					if($success) 
					{
						$success_count++;
					}
					else 
					{
						$fail_count++;
					}
				}
			}
		}
		
		// Convert to MyISAM
		if ( ! empty($innodb_tables))
		{
			foreach($innodb_tables as $table_name) 
			{
				if ( ! empty($table_name))
				{
					$success = @ee()->db->query("ALTER TABLE {$table_name} ENGINE=MyISAM");
					if($success) 
					{
						$success_count++;
					}
					else 
					{
						$fail_count++;
					}
				}
			}
		}
		
		if ($fail_count === 0)
		{
			ee('CP/Alert')->makeBanner('convert-tables-ee4')
			  ->asSuccess()
			  ->withTitle(lang('convert_engine_success'))
			  ->addToBody(lang('convert_engine_success_desc')." - Success: $success_count, Fail: $fail_count")
			  ->defer();
		}
		else
		{
			ee('CP/Alert')->makeBanner('convert-tables-ee4')
			  ->asWarning()
			  ->withTitle(lang('convert_engine_warning'))
			  ->addToBody(lang('convert_engine_warning_desc')." - Success: $success_count, Fail: $fail_count")
			  ->defer();
		}

		ee()->functions->redirect(ee('CP/URL', 'addons/settings/convert_tables_ee4/tables_engine'));
	}
	
	
    // ----------------------------------------------------------------
	
	private function get_field_types($table)
	{
		$field_types = array();
		$field_data = ee()->db->field_data($table);
		foreach ($field_data as $field)
		{
			$field_types[$field->name]['type'] = $field->type;
			if (isset($field->max_length) 
				&& $field->type != 'text'
				&& $field->type != 'tinytext'
				&& $field->type != 'mediumtext'
				&& $field->type != 'longtext'
				&& $field->type != 'blob'
				&& $field->type != 'tinyblob'
				&& $field->type != 'mediumblob'
				&& $field->type != 'longblob'
			)
			{
				$field_types[$field->name]['max_length'] = $field->max_length;
			}
		}
		return $field_types;
	}
	

    private function create_sidebar()
    {
		$sidebar = ee('CP/Sidebar')->make();

		$module_sidebar = $sidebar->addHeader(lang('convert_tables_title'));

		$module_list = $module_sidebar->addBasicList();
		$module_list->addItem(lang('convert_channel_fields'), ee('CP/URL', 'addons/settings/convert_tables_ee4'));
		$module_list->addItem(lang('convert_category_fields'), ee('CP/URL', 'addons/settings/convert_tables_ee4/categories'));
		$module_list->addItem(lang('convert_member_fields'), ee('CP/URL', 'addons/settings/convert_tables_ee4/members'));
		$module_list->addItem(lang('convert_tables_engine'), ee('CP/URL', 'addons/settings/convert_tables_ee4/tables_engine'));
    }	
	
}
// End of file
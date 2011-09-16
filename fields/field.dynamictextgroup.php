<?php

	/* * * 	@package dynamictextgroup 																				* * */
	/* * * 	This field provides a method to dynamically add a text field or text field groups to a section entry 	* * */
	
	if(!defined('__IN_SYMPHONY__')) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');

	require_once(EXTENSIONS . '/dynamictextgroup/lib/class.textgroup.php');
	if(!class_exists('Stage')) {
		require_once(EXTENSIONS . '/dynamictextgroup/lib/stage/class.stage.php');
	}

	Class fielddynamictextgroup extends Field {

		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#__construct * * */
		function __construct(&$parent) {	
			parent::__construct($parent);
			$this->_name = __('Dynamic Text Group');
			$this->_required = true;
			$this->set('required', 'no');
		}
	
	
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#canFilter * * */
		function canFilter() {
			return true;
		}
	
	
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#isSortable * * */
		function isSortable() {
			return true;
		}
	
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#canPrePopulate * * */
		function canPrePopulate() {
			return false;
		}
	
	
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#allowDatasourceOutputGrouping * * */
		function allowDatasourceOutputGrouping() {
			return false;
		}
	
	
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#allowDatasourceParamOutput * * */
		function allowDatasourceParamOutput() {
			return false;
		}
	
	
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#displaySettingsPanel * * */
		function displaySettingsPanel(&$wrapper, $errors=NULL) {
			
			// Initialize field settings based on class defaults (name, placement)
			parent::displaySettingsPanel($wrapper, $errors);
			
			// Field Editor
			if ($this->get('id')) {
				Administration::instance()->Page->addScriptToHead(URL . '/extensions/dynamictextgroup/assets/jquery-ui-1.8.16.custom.min.js', 101, false);
				Administration::instance()->Page->addScriptToHead(URL . '/extensions/dynamictextgroup/assets/json2.js', 102, false);
				Administration::instance()->Page->addScriptToHead(URL . '/extensions/dynamictextgroup/assets/jquery.ui.resizable.js', 103, false);
				Administration::instance()->Page->addScriptToHead(URL . '/extensions/dynamictextgroup/assets/dynamictextgroup.fieldeditor.js', 104, false);
				Administration::instance()->Page->addStylesheetToHead(URL . '/extensions/dynamictextgroup/assets/dynamictextgroup.fieldeditor.css', 'screen', 105, false);
				
				$tblocks = '<input type="hidden" id="fieldschema" name="fields['.$this->get('sortorder').'][schema]" value=\''.$this->get('schema').'\' />';
				$tblocks .= '<input type="hidden" id="addfields" name="fields['.$this->get('sortorder').'][addfields]" value="" />';
				$tblocks .= '<input type="hidden" id="delfields" name="fields['.$this->get('sortorder').'][delfields]" value="" />';
				$tblocks .= '<input type="hidden" id="renfields" name="fields['.$this->get('sortorder').'][renfields]" value="" />';
		
				$fieldset = new XMLElement('fieldset', '<legend>Field Editor</legend><div id="stageHolder"><div id="stage"></div><div id="messages"></div><a class="dtgButton" id="add">Add Field</a><br clear="all" /></div>'.$tblocks);
				$wrapper->appendChild($fieldset);
			} else {
				$fieldset = new XMLElement('fieldset', '<legend>Field Editor</legend>Please save the section to enable the Field Editor.<br /><br />');
				$wrapper->appendChild($fieldset);
			}

			// Behaviour
			$fieldset = Stage::displaySettings(
				$this->get('id'), 
				$this->get('sortorder'), 
				__('Behaviour'),
				array('constructable', 'draggable')
			);
			$group = $fieldset->getChildren();
			$wrapper->appendChild($fieldset);

			// General
			$fieldset = new XMLElement('fieldset');
			$group = new XMLElement('div', NULL, array('class' => 'group'));
			$this->appendRequiredCheckbox($group);
			$this->appendShowColumnCheckbox($group);
			$fieldset->appendChild($group);
			$wrapper->appendChild($fieldset);
		}
		
	
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#checkFields * * */
		function checkFields(&$errors, $checkForDuplicates=true) {
			parent::checkFields($errors, $checkForDuplicates);
		}
	
	
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#commit * * */
		function commit() {
			// Prepare commit
			if(!parent::commit()) return false;
			$id = $this->get('id');
			if($id === false) return false;
			
			// Set up fields
			$fields = array();
			$fields['field_id'] = $id;
			
			// Parse schema
			if ($this->get('schema') != '') {
				$schema = json_decode($this->get('schema'));
				$ct = count($schema);
				$totalW = 100;
				foreach ($schema as $i=>&$field) {
					$field->handle = Lang::createHandle($field->label);
					$totalW -= $field->width;
				}
				$schema[$ct-1]->width += $totalW;
				
				$fields['fieldcount'] = $ct;
				$fields['schema'] = json_encode($schema);
			}
			
			// Parse rename data
			if ($this->get('renfields') != '') {
				$renfields = json_decode($this->get('renfields'));
				foreach ($renfields->handles as $key=>$rename) self::__alterTable(2, $rename, Lang::createHandle($renfields->labels[$key]));
			}
			// Parse delete data
			if ($this->get('delfields') != '') {
				$delfields = json_decode($this->get('delfields'));
				foreach ($delfields->handles as $deletion) self::__alterTable(0, $deletion);
			}
			// Parse add data
			if ($this->get('addfields') != '') {
				$addfields = json_decode($this->get('addfields'));
				foreach ($addfields->labels as $addition) self::__alterTable(1, Lang::createHandle($addition));
			}
	
			// Save new stage settings for this field
			$stage = $this->get('stage');
			$stage['destructable'] = 1;
			Stage::saveSettings($this->get('id'), $stage, 'dynamictextgroup');

			// Delete old field settings
			Symphony::Database()->query(
				"DELETE FROM `tbl_fields_" . $this->handle() . "` WHERE `field_id` = '$id' LIMIT 1"
			);
			
			// Save new field setting
			return Symphony::Database()->insert($fields, 'tbl_fields_' . $this->handle());
			
		}
		
		function __alterTable($mode, $col, $rename=NULL) {
			// Function $mode options:
			// 0 = Delete column; 	e.g.  __alterTable(0, 'badcolumn');
			// 1 = Add column; 		e.g.  __alterTable(1, 'newcolumn');
			// 2 = Rename column;	e.g.  __alterTable(2, 'newcolumnname', 'oldcolumnname');
			switch ($mode) {
				case 0:
					// Delete column
					Symphony::Database()->query("ALTER TABLE `tbl_entries_data_" . $this->get('id') . "` DROP COLUMN `". $col ."`");
					break;
				case 1:
					// Add column
					Symphony::Database()->query("ALTER TABLE `tbl_entries_data_" . $this->get('id') . "` ADD COLUMN `". $col ."` varchar(255) null");
					break;
				case 2:
					// Rename column
					Symphony::Database()->query("ALTER TABLE `tbl_entries_data_" . $this->get('id') . "` CHANGE `". $col ."` `". $rename ."` varchar(255) null");
					break;
				default:
					return false;
			}
		}
	
	
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#displayPublishPanel * * */
		function displayPublishPanel(&$wrapper, $data=NULL, $flagWithError=NULL, $fieldnamePrefix=NULL, $fieldnamePostfix=NULL) {
	
			// Append assets
			Administration::instance()->Page->addScriptToHead(URL . '/extensions/dynamictextgroup/lib/stage/stage.publish.js', 101, false);
			Administration::instance()->Page->addStylesheetToHead(URL . '/extensions/dynamictextgroup/lib/stage/stage.publish.css', 'screen', 102, false);
			Administration::instance()->Page->addScriptToHead(URL . '/extensions/dynamictextgroup/assets/dynamictextgroup.publish.js', 103, false);
			Administration::instance()->Page->addStylesheetToHead(URL . '/extensions/dynamictextgroup/assets/dynamictextgroup.publish.css', 'screen', 104, false);
			
			// Get settings
			$settings = array();
			$stage = Stage::getComponents($this->get('id'));
			if(in_array('constructable', $stage)) {
				$settings[] = 'multiple';
			} else {
				$settings[] = 'single';
			}
			
			$schema = json_decode($this->get('schema'));
			$fieldCount = $this->get('fieldcount');
			
			
			// Populate existing entries
			$content = array();
			if(is_array($data)) {
				$entryCount = 1;
				foreach ($data as &$row) {
					if (!is_array($row)) $row = array($row);
					if (count($row) > $entryCount) $entryCount = count($row);
				}
				
				for($i=0; $i<$entryCount; $i++) {
					foreach ($schema as $field) {
						$entryValues[$i][] = $data[$field->handle][$i];
					}
					$content[] = Textgroup::createNewTextGroup($this->get('element_name'), $fieldCount, $entryValues[$i], null, $schema);
				}
			}
			// Blank entry
			else {
				$content[] = Textgroup::createNewTextGroup($this->get('element_name'), $fieldCount, NULL, NULL, $schema);
			}
			
			// Add template
			$content[] = Textgroup::createNewTextGroup($this->get('element_name'), $fieldCount, NULL, 'template empty create', $schema);
		
			// Create stage
			$stage = Stage::create('dynamictextgroup', $this->get('id'), implode($settings, ' '), $content);
			
			// Field label
			$holder = new XMLElement('div');
			$label = new XMLElement('label', $this->get('label') . '<i>' . __('Help') . '</i>');
			$holder->appendChild($label);
			
			// Append Stage
			if($stage) $holder->appendChild($stage);
			
			if($flagWithError != NULL) $wrapper->appendChild(Widget::wrapFormElementWithError($holder, $flagWithError));
			else $wrapper->appendChild($holder);
		}
		
		
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#checkPostFieldData * * */
		public function checkPostFieldData($data, &$message, $entry_id=NULL){
			$message = __("'%s' is a required field.", array($this->get('label')));
			
			$schema = json_decode($this->get('schema'));
			
			$sampling = $schema[0]->handle;
			$entryCount = count($data[$sampling]);
			
			$empty = true;
			$badValidate = false;
			$badValidateItems = array();
			
			for($i=0; $i<$entryCount; $i++) {
				$emptyRow = true;
				$emptyReq = false;
				foreach ($schema as $f=>$field) {
					// Get/set required option
					$req = $schema[$f]->options->required ? true : false;
					// Get/set validation option
					if ($schema[$f]->options->type == 'text') $rule = $schema[$f]->options->validationRule != '' ? $schema[$f]->options->validationRule : false;
					else $rule = false;
					// Check if matches validation rule
					if ($rule && !General::validateString($data[$field->handle][$i], $rule)){
						$badValidateItems[] = array('handle' => 'field-'.$field->handle, 'index' => $i);
						$badValidate = true;
					}
					// Check if required subfield is empty
					if ($req && $data[$field->handle][$i] == '')	$emptyReq = true;
					else if ($data[$field->handle][$i] != '')		$emptyRow = false;
				}
				if (!$emptyRow && $emptyReq) {
					$message = __("'%s' contains required fields that are empty.", array($this->get('label')));
					return self::__MISSING_FIELDS__;
				}
			}
			if ($badValidate){
				$badValidateItems = json_encode($badValidateItems);
				$message = __("'%s' contains invalid data. Please check the contents.<input type='hidden' id='badItems' value='%s' />", array($this->get('label'), $badValidateItems));
				return self::__INVALID_FIELDS__;
			}
			
			if ($empty && $this->get('required') == 'yes') return self::__MISSING_FIELDS__;
			
			return self::__OK__;
		}
		
		
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#processRawFieldData * * */
		function processRawFieldData($data, &$status, $simulate=false, $entry_id=NULL) {
			$status = self::__OK__;
			if(!is_array($data)) return NULL;
			
			$result = array();
			$count = $this->get('fieldcount');
			
			// Check for the field with the most values
			$entryCount = 0;
			foreach ($data as $row) if (count($row) > $entryCount) $entryCount = count($row);
			
			// Check for empties
			$empty = true;
			
			for($i=0; $i < $entryCount; $i++) {
				$emptyEntry = true;
				foreach ($data as &$field) {
					if (!empty($field[$i])) {
						$empty = false;	
						$emptyEntry = false;	
					} else {
						$field[$i] = ' ';
					}
				}
				if ($emptyEntry) {
					foreach ($data as &$field) {
						unset($field[$i]);
					}
				}
			}

			if ($empty) {
				return null;
			} else {
				return $data;
			}

		}
	
	
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#createTable * * */
		function createTable() {
			return Symphony::Database()->query(
				"CREATE TABLE IF NOT EXISTS `tbl_entries_data_" . $this->get('id') . "` (
				`id` int(11) unsigned NOT NULL auto_increment,
				`entry_id` int(11) unsigned NOT NULL,
				PRIMARY KEY (`id`),
				KEY `entry_id` (`entry_id`)
				);"
			);
		}
	
	
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#prepareTableValue * * */
		function prepareTableValue($data, XMLElement $link=NULL) {
			if (is_array($data)) {
				$keys = array_keys($data);
				$key = $keys[0];
				
				if(!is_array($data[$key])) $data[$key] = array($data[$key]);
				if ($data[$key][0] != null) {
					$strung = count($data[$key]) == 1 ? count($data[$key]) . ' item' : count($data[$key]) . ' items';
				} else {
					$strung = null;
				}
			} else {
				$strung = null;
			}
			return $strung;
		}
	
	
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#buildSortingSQL * * */
		/*
		function buildSortingSQL(&$joins, &$where, &$sort, $order='ASC') {
		}
		*/
	
	
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#buildDSRetrivalSQL * * */
		/*
		**	Accepted filter:
		**	handle:value	(e.g. first-name:Brock)
		**  Where 'handle' is equal to the handle of a subfield, and 'value' is equal to the input of said subfield. All entries with a matching value in this subfield will be returned.
		*/
		public function buildDSRetrivalSQL($data, &$joins, &$where, $andOperation = false) {
			$field_id = $this->get('id');
			
			if (preg_match('/.*:.*/', $data[0])) {
				$this->_key++;
				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
					ON
						(e.id = t{$field_id}_{$this->_key}.entry_id)
				";
				
				$data[0] = explode(':', trim($this->cleanValue($data[0])));
				$handle = $data[0][0];
				$value = $data[0][1];
				
				$where .= "
					AND (
						`t{$field_id}_{$this->_key}`.`{$handle}` IN ('{$value}')
					)
				";
			}

			return true;
		}
	

		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#groupRecords * * */
		/*
		public function groupRecords($records) {
		}
		*/
	
	
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#appendFormattedElement * * */
		public function appendFormattedElement(&$wrapper, $data, $encode = false, $mode = null, $entry_id) {
			// Get field properties and decode schema
			$fieldCount = $this->get('fieldcount');
			$schema = json_decode($this->get('schema'));
			$sampling = $schema[0]->handle;
			$entryCount = count($data[$sampling]);
				
			// Parse data
			$textgroup = new XMLElement($this->get('element_name'));
			foreach ($data as &$row) { if (!is_array($row)) $row = array($row); }
			for($i=0; $i<$entryCount; $i++) {
				$item = new XMLElement('item');
				$empty = true;
				foreach ($schema as $field) {
					$val = $data[$field->handle][$i] != ' ' ? General::sanitize($data[$field->handle][$i]) : '';
					$item->appendChild(new XMLElement($field->handle, $val));
				}
				$textgroup->appendChild($item);
			}
	
			// Append to data source
			$wrapper->appendChild($textgroup);
		}
	
	
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#getParameterPoolValue * * */
		/*
		public function getParameterPoolValue($data) {
		}
		*/
	
	
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#getExampleFormMarkup * * */
		public function getExampleFormMarkup() {
			$label = Widget::Label($this->get('label'));
			$label->appendChild(Widget::Input('fields['.$this->get('element_name').'][samplefield][]'));
			
			return $label;
		}
	}
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
			
			Administration::instance()->Page->addScriptToHead(URL . '/extensions/dynamictextgroup/assets/jquery-ui-1.8.16.custom.min.js', 101, false);
			Administration::instance()->Page->addScriptToHead(URL . '/extensions/dynamictextgroup/assets/jquery.ui.resizable.js', 101, false);
			Administration::instance()->Page->addScriptToHead(URL . '/extensions/dynamictextgroup/assets/dynamictextgroup.fieldeditor.js', 103, false);
			Administration::instance()->Page->addStylesheetToHead(URL . '/extensions/dynamictextgroup/assets/dynamictextgroup.fieldeditor.css', 'screen', 104, false);
	
			// Initialize field settings based on class defaults (name, placement)
			parent::displaySettingsPanel($wrapper, $errors);
			
			// Field Number Chooser
			$tblocks = '<input type="hidden" id="fieldschema" name="fields['.$this->get('sortorder').'][schema]" value="'.$this->get('schema').'" />';
			$tblocks .= '<input type="hidden" id="addfields" name="fields['.$this->get('sortorder').'][addfields]" value="" />';
			$tblocks .= '<input type="hidden" id="delfields" name="fields['.$this->get('sortorder').'][delfields]" value="" />';
			$tblocks .= '<input type="hidden" id="renfields" name="fields['.$this->get('sortorder').'][renfields]" value="" />';
	
			$fieldset = new XMLElement('fieldset', '<legend>Field Editor</legend><div id="stageHolder"><div id="stage"></div><button value="addfield" id="add">Add Field</button><br clear="all" /></div>'.$tblocks);

			$wrapper->appendChild($fieldset);

			// Behaviour
			$fieldset = Stage::displaySettings(
				$this->get('id'), 
				$this->get('sortorder'), 
				__('Behaviour'),
				array('constructable', 'draggable')
			);
			$group = $fieldset->getChildren();
			$wrapper->appendChild($fieldset);
			
			// Add Custom Schema option
			/*$fieldset = new XMLElement('fieldset', '<legend>Advanced</legend>');
			$group = new XMLElement('label', 'Custom Schema<i>Optional. Read the documentation before putting anything here!</i>');
			$group->appendChild(Widget::Input('fields['.$this->get('sortorder').'][schema]', $this->get('schema')));
			$fieldset->appendChild($group);
			$wrapper->appendChild($fieldset);*/

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
			$schema = explode('||', $this->get('schema'));
			$fields['fieldcount'] = count($schema);
			
			for ($i=0; $i<count($schema); $i++) {
				$row = explode('//', $schema[$i]);
				array_splice($row, 1, 0, Lang::createHandle($row[0]));
				$schema[$i] = implode('//', $row);
			}
			$fields['schema'] = implode('||', $schema);
			
			// Parse rename data
			if ($this->get('renfields') != '') {
				$renfields = explode('||', $this->get('renfields'));
				for ($r=0; $r<count($renfields); $r++) {
					$singlet = explode('//', $renfields[$r]);
					self::__alterTable(2, $singlet[0], Lang::createHandle($singlet[1]));
				}
			}
			// Parse delete data
			if ($this->get('delfields') != '') {
				$delfields = explode('||', $this->get('delfields'));
				for ($d=0; $d<count($delfields); $d++) {
					self::__alterTable(0, $delfields[$d]);
				}
			}
			// Parse add data
			if ($this->get('addfields') != '') {
				$addfields = explode('||', $this->get('addfields'));
				for ($a=0; $a<count($addfields); $a++) {
					self::__alterTable(1, Lang::createHandle($addfields[$a]));
				}
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
			
			// Parse schema
			$fieldLabels = array();
			$fieldHandles = array();
			$fieldWidths = array();
			$fieldReqs = array();
			$schema = explode('||', $this->get('schema'));
			for ($i=0; $i<count($schema); $i++) {
				$field = explode('//', $schema[$i]);
				array_push($fieldLabels, $field[0]);
				array_push($fieldHandles, $field[1]);
				array_push($fieldWidths, $field[2]);
				array_push($fieldReqs, $field[3]);
			}
			$sampling = $fieldHandles[0];
			$entryCount = count($data[$sampling]);
			$fieldCount = $this->get('fieldcount');
			
			// Populate existing entries
			$content = array();
			if(is_array($data)) {
				$fieldValues = array();
				
				// Make sure data is an array; if it isn't (single entries), put it in one
				/*for ($i=0; $i<$fieldCount; $i++) {
					$fieldName = $fieldHandles[$i];
					if(!is_array($data[$fieldName])) $data[$fieldName] = array($data[$fieldName]);
				}*/
				
				foreach ($data as &$row) {
					if (!is_array($row)) $row = array($row);
				}
				
				for($i=0; $i<$entryCount; $i++) {
					for ($n=0; $n<$fieldCount; $n++) {
						$fieldName = $fieldHandles[$n];
						$entryValues[$i][$n] = $data[$fieldName][$i];
					}
					$content[] = Textgroup::createNewTextGroup($this->get('element_name'), $fieldCount, $entryValues[$i], null, $fieldLabels, $fieldHandles, $fieldWidths);
				}
			}
			
			// Blank entry
			else {
				$content[] = Textgroup::createNewTextGroup($this->get('element_name'), $fieldCount, NULL, NULL, $fieldLabels, $fieldHandles, $fieldWidths);
			}
			
			// Add template
			$content[] = Textgroup::createNewTextGroup($this->get('element_name'), $fieldCount, NULL, 'template empty create', $fieldLabels, $fieldHandles, $fieldWidths);
		
			// Create stage
			$stage = Stage::create('dynamictextgroup', $this->get('id'), implode($settings, ' '), $content);
			
			// Field label
			$holder = new XMLElement('div');
			$label = new XMLElement('label', $this->get('label') . '<i>' . __('Help') . '</i>');
			$holder->appendChild($label);
			
			// Append Stage
			if($stage) {
				$holder->appendChild($stage);
			}
			
			if($flagWithError != NULL) $wrapper->appendChild(Widget::wrapFormElementWithError($holder, $flagWithError));
			else $wrapper->appendChild($holder);
		}
		
		
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#checkPostFieldData * * */
		public function checkPostFieldData($data, &$message, $entry_id=NULL){
			/*$message = NULL;
			
			$count = $this->get('fieldcount');
			$entryCount = 1;
			for ($i=1; $i<=$count; $i++) {
				$targ = 'f'.$i;
				if (count($data[$targ]) > $entryCount) $entryCount = count($data[$targ]);
			}

			if($this->get('required') == 'yes'){
				$empty = true;
				for ($i=1; $i<=$count; $i++) {
					for ($d=0; $d<$entryCount; $d++) {
						$targ = 'f'.$i;
						if ($data[$targ][$d] != '') $empty = false;
					}
				}
				
				if($empty) {
					$message = __("'%s' is a required field.", array($this->get('label')));
					return self::__MISSING_FIELDS__;
				}
			}*/
			return self::__OK__;
		}
		
		
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#processRawFieldData * * */
		function processRawFieldData($data, &$status, $simulate=false, $entry_id=NULL) {
			$status = self::__OK__;
			if(!is_array($data)) return NULL;
			
			$result = array();
			$count = $this->get('fieldcount');
			
			// Check for the field with the most values
			$entryCount = 1;
			$keys = array_keys($data);
			
			// Cycle through data and find accurate entry count
			foreach ($data as $field) {
				if (count($field) > $entryCount) $entryCount = count($field);
			}
			
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
			$count = $this->get('fieldcount');
			$make = '';
			for ($i=1; $i <= $count; $i++) {
				$make .= '`f'. $i .'` varchar(255) NULL,';
			}
			$this->set('make', $make);
			return Symphony::Database()->query(
				"CREATE TABLE IF NOT EXISTS `tbl_entries_data_" . $this->get('id') . "` (
				`id` int(11) unsigned NOT NULL auto_increment,
				`entry_id` int(11) unsigned NOT NULL,
				". $this->get('make') ."
				PRIMARY KEY (`id`),
				KEY `entry_id` (`entry_id`)
				);"
			);
			
		}
	
	
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#prepareTableValue * * */
		function prepareTableValue($data, XMLElement $link=NULL) {
			if(!is_array($data['f1'])) $data['f1'] = array($data['f1']);
			if ($data['f1'][0] != null) {
				$strung = count($data['f1']) == 1 ? count($data['f1']) . ' item' : count($data['f1']) . ' items';
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
		**	Accepted Filter options at this stage:
		**
		**	colour			Key
		**	value: red		Value
		**	key-equals: 	colour=red	Key Equals
		*/
		public function buildDSRetrivalSQL($data, &$joins, &$where, $andOperation = false) {
			$id = $this->get('id');
			$this->_key++;
			$fieldCount = $this->get('fieldcount');
			
			// Match label
			if (preg_match('/:/', $data[0])) {
				$filterField = explode(':', $data[0]);
				if (!is_array($data)) $data = array($data);
				$data = implode("', '", $data);
				
				// User has not declared a custom label (e.g. 'textfield2')
				if (preg_match('/textfield/', $filterField[0])) {
					$joins .= "
						LEFT JOIN
							`tbl_entries_data_{$id}` AS t{$id}_{$this->_key}
						ON
							(e.id = t{$id}_{$this->_key}.entry_id)";
					$where .= "
						AND	(
							t{$id}_{$this->_key}.{$filterField[0]} IN ('{$data}')
						)";
					
				// User has declared a custom label
				} else {
					$schema = $this->get('schema');
					if ($schema && $schema != '') {
						$fieldLabels = array();
						$fieldWidths = array();
						
						$splitMe = explode('|',$schema);
						
						foreach ($splitMe as $i => $field) {
							$index = $i+1;
							$splitAgain = explode(',', $field);
							$fieldLabels[$i] = ($splitAgain[0] && $splitAgain[0] != null) ? General::sanitize($splitAgain[0]) : null;
						}
					} else {	
						$fieldLabels = null;
					}
					
				}
			}
			
			// Match any value
			else {
				if (!is_array($data)) $data = array($data);
				$data = implode("', '", $data);

				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$id}` AS t{$id}_{$this->_key}
					ON
						(e.id = t{$id}_{$this->_key}.entry_id)";
				$where .= "
					AND	(
						t{$id}_{$this->_key}.textfield1 IN ('{$data}')
						OR
						t{$id}_{$this->_key}.textfield2 IN ('{$data}')
						OR
						t{$id}_{$this->_key}.textfield3 IN ('{$data}')
					)";
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
			$fieldCount = $this->get('fieldcount');
			
			// Check if schema exists and compile info
			$schema = $this->get('schema');
			if ($schema && $schema != '') {
				$fieldLabels = array();
				$fieldWidths = array();
				
				$splitMe = explode('|',$schema);
				
				foreach ($splitMe as $i => $field) {
					$index = $i+1;
					$splitAgain = explode(',', $field);
					$fieldLabels[$i] = ($splitAgain[0] && $splitAgain[0] != null) ? General::sanitize($splitAgain[0]) : null;
				}
			} else {	
				$fieldLabels = null;
			}
			
			// Parse result data
			$entryCount = count($data['f1']);
						
			$textgroup = new XMLElement($this->get('element_name'));
			
			for($i=0; $i<$entryCount; $i++) {
				$item = new XMLElement('item');
				$empty = true;
				
				for ($f=1; $f<=$fieldCount; $f++) {
					$targ = 'f'.$f;
					
					if(!is_array($data[$targ])) $data[$targ] = array($data[$targ]);
				
					$label = ($fieldLabels != null && $fieldLabels[$f-1] != null) ? Lang::createHandle($fieldLabels[$f-1]) : 'f'.$f;
					$val = $data[$targ][$i] != ' ' ? General::sanitize($data[$targ][$i]) : '';
					$item->appendChild(
						${'f'.$f} = new XMLElement($label, $val)
					);
				}
				
				$textgroup->appendChild($item);
			}
	
			// append to data source
			$wrapper->appendChild($textgroup);
			
		}
	
	
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#getParameterPoolValue * * */
		public function getParameterPoolValue($data) {
			$start = array();
			foreach($data['f1'] as $item) {
				$start[] = General::sanitize($item);
			}
			return implode(',', $start);
		}
	
	
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#getExampleFormMarkup * * */
		public function getExampleFormMarkup() {
			$label = Widget::Label($this->get('label'));
			$label->appendChild(Widget::Input('fields['.$this->get('element_name').'][f1][]'));
			
			return $label;
		}
	}
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
			return false;
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
			
			// Field Number Chooser
			$fieldset = new XMLElement('fieldset');
			if (!$this->get('id') && $errors == NULL) {
				$this->set('fieldcount', 2);
				$group = new XMLElement('label', 'Number of text inputs per row<i>IMPORTANT: This cannot be changed once you save!</i><input name="fields[' . $this->get('sortorder') . '][fieldcount]" type="text" value="' . $this->get('fieldcount') . '" />');
			} else {
				$this->get('fieldcount');
				$group = new XMLElement('label', 'Number of text inputs per row<i>Database table created. This value can no longer be changed.</i><input name="fields[' . $this->get('sortorder') . '][fielddisplay]" type="text" disabled="disabled" value="' . $this->get('fieldcount') . '" /><input name="fields[' . $this->get('sortorder') . '][fieldcount]" type="hidden" value="' . $this->get('fieldcount') . '" />');
			}
			$fieldset->appendChild($group);
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
			$fieldset = new XMLElement('fieldset', '<legend>Advanced</legend>');
			$group = new XMLElement('label', 'Custom Schema<i>Optional. Read the documentation before putting anything here!</i>');
			$group->appendChild(Widget::Input('fields['.$this->get('sortorder').'][schema]', $this->get('schema')));
			$fieldset->appendChild($group);
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
			$fields['fieldcount'] = $this->get('fieldcount');
			$fields['schema'] = $this->get('schema');
	
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
			
			$fieldCount = $this->get('fieldcount');
			$entryCount = count($data['textfield1']);
			
			// Check if schema exists and compile info
			$schema = $this->get('schema');
			if ($schema && $schema != '') {
				$fieldLabels = array();
				$fieldWidths = array();
				
				$splitMe = explode('|',$schema);
				
				foreach ($splitMe as $i => $field) {
					$index = $i+1;
					$splitAgain = explode(',', $field);
					$fieldLabels[$i] = ($splitAgain[0] && $splitAgain[0] != null) ? $splitAgain[0] : null;
					$fieldWidths[$i] = ($splitAgain[1] && $splitAgain[1] != null) ? (int)$splitAgain[1] : null;
				}
			} else {	
				$fieldLabels = null;
				$fieldWidths = null;
			}
			$fieldWidths = Textgroup::getWidths($fieldCount, $fieldWidths);
			
			// Populate existing entries
			$content = array();
			if(is_array($data)) {
				$fieldValues = array();
				for ($i=0; $i<$fieldCount; $i++) {
					if(!is_array($data['textfield'.($i+1)])) $data['textfield'.($i+1)] = array($data['textfield'.($i+1)]);
				}
				
				for($i=0; $i<$entryCount; $i++) {
					for ($n=0; $n<$fieldCount; $n++) {
						$entryValues[$i][$n] = $data['textfield'.($n+1)][$i];
					}
					$content[] = Textgroup::createNewTextGroup($this->get('element_name'), $fieldCount, $entryValues[$i], null, $fieldLabels, $fieldWidths);
				}
			}
			
			// Blank entry
			else {
				$content[] = Textgroup::createNewTextGroup($this->get('element_name'), $fieldCount, NULL, NULL, $fieldLabels, $fieldWidths);
			}
			
			// Add template
			$content[] = Textgroup::createNewTextGroup($this->get('element_name'), $fieldCount, NULL, 'template empty create', $fieldLabels, $fieldWidths);
		
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
			$message = NULL;
			
			$count = $this->get('fieldcount');
			$entryCount = 1;
			for ($i=1; $i<=$count; $i++) {
				$targ = 'textfield'.$i;
				if (count($data[$targ]) > $entryCount) $entryCount = count($data[$targ]);
			}

			if($this->get('required') == 'yes'){
				$empty = true;
				for ($i=1; $i<=$count; $i++) {
					for ($d=0; $d<$entryCount; $d++) {
						$targ = 'textfield'.$i;
						if ($data[$targ][$d] != '') $empty = false;
					}
				}
				
				if($empty) {
					$message = __("'%s' is a required field.", array($this->get('label')));
					return self::__MISSING_FIELDS__;
				}
			}
			return self::__OK__;
		}
		
		
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#processRawFieldData * * */
		function processRawFieldData($data, &$status, $simulate=false, $entry_id=NULL) {
			$status = self::__OK__;
			if(!is_array($data)) return NULL;
		
			// Clean up dates
			$result = array();
			
			$count = $this->get('fieldcount');
			
			// Check for the field with the most values
			$entryCount = 1;
			for ($i=1; $i<=$count; $i++) {
				$targ = 'textfield'.$i;
				if (count($data[$targ]) > $entryCount) $entryCount = count($data[$targ]);
			}
			
			$empty = true;
			for($i=0; $i < $entryCount; $i++) {
				$emptyEntry = true;
				for($f=1; $f<=$count; $f++) {
					$targ = 'textfield'.$f;
					if (!empty($data[$targ][$i])) {
						$result[$targ][$i] = $data[$targ][$i];
						$empty = false;	
						$emptyEntry = false;	
					} else {
						$result[$targ][$i] = ' ';	
					}
				}
				if ($emptyEntry) {
					for($f=1; $f<=$count; $f++) {
						$targ = 'textfield'.$f;
						unset($result[$targ][$i]);
					}
				}
			}

			if ($empty) {
				return null;
			} else {
				return $result;
			}
		}
	
	
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#createTable * * */
		function createTable() {
			$count = $this->get('fieldcount');
			$make = '';
			for ($i=1; $i <= $count; $i++) {
				$make .= '`textfield'. $i .'` varchar(255) NULL,';
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
			if(!is_array($data['textfield1'])) $data['textfield1'] = array($data['textfield1']);
			if ($data['textfield1'][0] != null) {
				$strung = count($data['textfield1']) == 1 ? count($data['textfield1']) . ' item' : count($data['textfield1']) . ' items';
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
		function buildDSRetrivalSQL($data, &$joins, &$where, $andOperation = false) {
		}
		*/
	

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
			$entryCount = count($data['textfield1']);
						
			$textgroup = new XMLElement($this->get('element_name'));
			
			for($i=0; $i<$entryCount; $i++) {
				$item = new XMLElement('item');
				$empty = true;
				
				for ($f=1; $f<=$fieldCount; $f++) {
					$targ = 'textfield'.$f;
					
					if(!is_array($data[$targ])) $data[$targ] = array($data[$targ]);
				
					$label = ($fieldLabels != null && $fieldLabels[$f-1] != null) ? Lang::createHandle($fieldLabels[$f-1]) : 'textfield'.$f;
					$val = $data[$targ][$i] != ' ' ? General::sanitize($data[$targ][$i]) : '';
					$item->appendChild(
						${'textfield'.$f} = new XMLElement($label, $val)
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
			foreach($data['textfield1'] as $item) {
				$start[] = General::sanitize($item);
			}
			return implode(',', $start);
		}
	
	
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/field/#getExampleFormMarkup * * */
		public function getExampleFormMarkup() {
			$label = Widget::Label($this->get('label'));
			$label->appendChild(Widget::Input('fields['.$this->get('element_name').'][textfield1][]'));
			
			return $label;
		}
	}

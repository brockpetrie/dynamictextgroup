<?php

	/* * * 	@package dynamictextgroup 	* * */
	/* * * 	Dynamic Text Group 			* * */

	Class extension_dynamictextgroup extends Extension {

		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/extension/#install * * */
		public function install() {
			$status = array();
			
			// Create database field table
			$status[] = Symphony::Database()->query(
				"CREATE TABLE `tbl_fields_dynamictextgroup` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`field_id` int(11) unsigned NOT NULL,
					`allow_multiple` tinyint(1),
					`fieldcount` tinyint(1),
					`schema` text,
        	  		PRIMARY KEY  (`id`),
			  		KEY `field_id` (`field_id`)
				)"
			);

			// Report status
			if(in_array(false, $status, true)) {
				return false;
			}
			else {
				return true;
			}
		}


		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/extension/#uninstall * * */
		public function uninstall() {

			// Drop date and time table
			Symphony::Database()->query("DROP TABLE `tbl_fields_dynamictextgroup`");
		}

		/*------------------------------------------------------------------------------------------------*/
		/*  Delegates  */
		/*------------------------------------------------------------------------------------------------*/

		public function getSubscribedDelegates(){
			return array(
				array(
					'page' => '/extensions/frontend_localisation/',
					'delegate' => 'FLSavePreferences',
					'callback' => 'dFLSavePreferences'
				),
			);
		}

		/*------------------------------------------------------------------------------------------------*/
		/*  System preferences  */
		/*------------------------------------------------------------------------------------------------*/


		/**
		 * Save options from Preferences page
		 *
		 * @param array $context
		 */
		public function dFLSavePreferences($context){
			$fieldTable = "tbl_fields_dynamictextgroup";
			$fields = Symphony::Database()->fetch(sprintf('SELECT `field_id` FROM `%s`', $fieldTable));

			if( $fields ){
				// Foreach field check multilanguage values foreach language
				foreach( $fields as $field ){
					$entries_table = 'tbl_entries_data_'.$field["field_id"];

					try{
						$columns = Symphony::Database()->fetchCol('Field',"SHOW COLUMNS FROM `{$entries_table}`;");
					}
					catch( DatabaseException $dbe ){
						// Field doesn't exist. Better remove it's settings
						Symphony::Database()->query(sprintf(
								"DELETE FROM `%s` WHERE `field_id` = %s;",
								$fieldTable, $field["field_id"])
						);
						continue;
					}

					$fieldObject = FieldManager::fetch($field["field_id"]);
					$schema = json_decode($fieldObject->get('schema'));
					foreach ($schema as $key => $schemaField) {
						if ($schemaField->options->type=='multilingual'){
							// var_dump($schemaField->handle);die;


							foreach( $context['new_langs'] as $lc ){
								// If column lang_code dosen't exist in the laguange drop columns

								if( !in_array($schemaField->handle . '-'.$lc, $columns) ){
									$fieldObject->__alterTable(1, $schemaField->handle . '-' . $lc ,$schemaField->options->multiline, $schemaField->options->formatter,$schemaField);
								}
							}
						}
					}

				}
			}
		}

	}

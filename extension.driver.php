<?php

	/* * * 	@package dynamictextgroup 	* * */
	/* * * 	Dynamic Text Group 			* * */
	
	Class extension_dynamictextgroup extends Extension {
		
		
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/extension/#__construct * * */
		public function __construct(Array $args){
			parent::__construct($args);
			
			// Include Stage
			if(!class_exists('Stage')) {
				try {
					if((include_once(EXTENSIONS . '/dynamictextgroup/lib/stage/class.stage.php')) === FALSE) {
						throw new Exception();
					}
				}
				catch(Exception $e) {
				    throw new SymphonyErrorPage(__('Please make sure that the Stage submodule is initialised and available at %s.', array('<code>' . EXTENSIONS . '/dynamictextgroup/lib/stage/</code>')) . '<br/><br/>' . __('It\'s available at %s.', array('<a href="https://github.com/nilshoerrmann/stage">github.com/nilshoerrmann/stage</a>')), __('Stage not found'));
				}
			}
		}
		
		
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/extension/#about * * */
		public function about() {
			return array(
				'name' => 'Dynamic Text Group',
				'version' => '2.0dev3',
				'release-date' => NULL,
				'author' => array(
					'name' => 'Brock Petrie',
					'website' => 'http://www.brockpetrie.com',
					'email' => 'brockpetrie@gmail.com'
				),
				'description'   => 'A field for dynamically adding text fields to a section.'
			);
		}
		
		
		/* * * @see http://symphony-cms.com/learn/api/2.2/toolkit/extension/#install * * */
		public function install() {
			$status = array();
			
			// Create database field table
			$status[] = Symphony::Database()->query(
				"CREATE TABLE `tbl_fields_dynamictextgroup` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`field_id` int(11) unsigned NOT NULL,
					`fieldcount` tinyint(1),
					`schema` varchar(255),
        	  		PRIMARY KEY  (`id`),
			  		KEY `field_id` (`field_id`)
				)"
			);

			// Create stage
			$status[] = Stage::install();

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
		
			// Drop related entries from stage tables
			Symphony::Database()->query("DELETE FROM `tbl_fields_stage` WHERE `context` = 'dynamictextgroup'");
			Symphony::Database()->query("DELETE FROM `tbl_fields_stage_sorting` WHERE `context` = 'dynamictextgroup'");

			// Drop date and time table
			Symphony::Database()->query("DROP TABLE `tbl_fields_dynamictextgroup`");
		}

	}

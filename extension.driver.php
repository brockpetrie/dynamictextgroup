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

	}

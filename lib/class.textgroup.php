<?php

	class Textgroup {
		
		public static function createNewTextGroup($element, $fieldCount=2, $values=NULL, $class=NULL, $labels=NULL, $handles, $widths=NULL) {
			// Additional classes
			$classes = array();
			if($class) {
				$classes[] = $class;
			}
			
			// Field creator
			$fields = '';
			for ($i=0; $i<$fieldCount; $i++) {
				$fieldVal = ($values != NULL && $values[$i] != ' ') ? $values[$i] : NULL;
				$fields .= self::__createTextField($element, $handles[$i], $fieldVal, $labels[$i], $widths[$i]);
			}
			
			// Create element
			return new XMLElement(
				'li', 
				'<span>
					<span class="fields">' . $fields . '<div class="clear"></span>
				</span>', 
				array('class' => implode($classes, ' '))
			);
		}
		
		private static function __createTextField($element, $handle, $textvalue, $label=NULL, $width=NULL) {
			// Generate field
			$width = 'style="width:'. $width*100 .'% !important;"';
			$label = '<label style="display:none;" for="fields[' . $element . '][' . $handle . '][]">'.$label.'</label>';
			return '<span class="fieldHolder '. $handle .'-holder" '. $width .'>'. $label .'<input type="text" name="fields['. $element .']['. $handle .'][]" value="'. $textvalue .'" class="'. $handle .' '. $class .'" /></span>';
		}
	}
	
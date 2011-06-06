<?php

	class Textgroup {
		
		public static function getWidths($fieldCount, $widths=NULL) {
			$computedWidths = array();
			
			if ($widths != null) {
				$offset = 100;
				//array for fields awaiting width computation
				$waiting = array();
				
				for ($i=0; $i<$fieldCount; $i++) {
					if ($widths[$i] != null) {
						$offset -= $widths[$i];
						$computedWidths[$i] = $widths[$i];
					} else {
						$waiting[] = $i;
						$computedWidths[$i] = null;
					}
				}
				for ($n=0; $n<count($waiting); $n++) {
					$targ = $waiting[$n];
					$computedWidths[$targ] = floor($offset/count($waiting));
				}
			} else {
				for ($i=0; $i<$fieldCount; $i++) {
					$computedWidths[$i] = floor(100/$fieldCount);
				}
			}
			
			return $computedWidths;
		}
		
		public static function createNewTextGroup($element, $fieldCount=2, $values=NULL, $class=NULL, $labels=NULL, $widths=NULL) {
			// Additional classes
			$classes = array();
			if($class) {
				$classes[] = $class;
			}
			
			// Field creator
			$fields = '';
			for ($i=0; $i<$fieldCount; $i++) {
				$fieldLabel = ($labels != null) ? $labels[$i] : null;
				$fieldVal = ($values != null && $values[$i] != ' ') ? $values[$i] : null;
				$fieldWidth = ($widths != null) ? $widths[$i] : null;
				//if ($fieldVal == '&#182;') $fieldVal = null;
				$fields .= self::__createTextField($element, 'textfield'.($i+1), $fieldVal, $fieldLabel, $fieldWidth);
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
		
		private static function __createTextField($element, $type, $textvalue, $label = null, $width = null) {
			// Generate field
			if ($width != null) {
				$width = 'style="width:'. $width .'% !important;"';
			}
			$label = ($label != null) ? '<label style="display:none;" for="fields[' . $element . '][' . $type . '][]">'.$label.'</label>' : '';
			return '<span class="fieldHolder '. $type .'-holder" '. $width .'>'. $label .'<input type="text" name="fields['. $element .']['. $type .'][]" value="'. $textvalue .'" class="'. $type .' '. $class .'" /></span>';
		}
	}
	
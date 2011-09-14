<?php

	class Textgroup {
		
		public static function createNewTextGroup($element, $fieldCount=2, $values=NULL, $class=NULL, $schema=NULL) {
			// Additional classes
			$classes = array();
			if($class) {
				$classes[] = $class;
			}
			
			// Field creator
			$fields = '';
			for ($i=0; $i<$fieldCount; $i++) {
				$fieldVal = ($values != NULL && $values[$i] != ' ') ? $values[$i] : NULL;
				if ($schema[$i]->options->type == 'select') {
					$fields .= self::__createSelectField($element, $schema[$i]->handle, $fieldVal, $schema[$i]->label, $schema[$i]->width, $schema[$i]->options);
				} else {
					$fields .= self::__createTextField($element, $schema[$i]->handle, $fieldVal, $schema[$i]->label, $schema[$i]->width, $schema[$i]->options->required);
				}
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
		
		private static function __createTextField($element, $handle, $textvalue, $label=NULL, $width=NULL, $required=NULL) {
			// Generate text field
			$width = 'style="width:'. $width .'% !important;"';
			$reqLabelAppendage = $required ? ' <span class="req">*</span>' : '';
			$class .= $required ? ' req' : '';
			$label = '<label style="display:none;" for="fields[' . $element . '][' . $handle . '][]">' . $label . $reqLabelAppendage . '</label>';
			return '<span class="fieldHolder '. $handle .'-holder'.$class.'" '. $width .'>'. $label .'<input type="text" name="fields['. $element .']['. $handle .'][]" value="'. $textvalue .'" class="field-'. $handle .'" /></span>';
		}
		
		private static function __createSelectField($element, $handle, $val, $label=NULL, $width=NULL, $options=NULL) {
			// Generate select list
			$class .= $options->required ? ' req' : '';
			if ($val == NULL)  $class .= ' empty';
			$fSelectItems = explode(',', $options->selectItems);
			$width = 'style="width:'. $width .'% !important;"';
			$select = '<span class="fieldHolder '. $handle .'-holder'. $class .'" '. $width .'>';
			$select .= '<select name="fields['. $element .']['. $handle .'][]" class="styled field-'. $handle .'">';
			$select .= '<option value="">'. $label .'</option>';
			foreach ($fSelectItems as &$item) {
				$item = trim($item);
				$selected = $val == $item ? 'selected="selected"' : '';
				$select .= '<option '. $selected .'>'. $item .'</option>';
			}
			$select .= '</select></span>';
			return $select;
		}
	}
	
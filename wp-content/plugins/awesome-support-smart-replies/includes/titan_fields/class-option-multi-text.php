<?php

/**
 * New titan settings field class for multiple facebook chat bot keywords
 */
class TitanFrameworkOptionMultiText extends TitanFrameworkOption {
        
    
    private static $row_Index = 0;
    
    /**
     * Display field
     */
	public function display() {

		$this->echoOptionHeader();

		$value = $this->getValue();

		$values = $value && is_array( $value )? $value : array();

		$field_index = 0;
		foreach ( $values as $val ) {
			$this->print_field( $val, $field_index );
			$field_index++;
		}
		
		?>
		
		<div class="wpas_multi_text_field_add_new" data-new_field_index="<?php echo $field_index; ?>">
			<a href="#" class="field_add_new_btn">Add</a>
			<?php $this->print_field( '', '{{{index}}}', true ); ?>
		</div>	
		
		<?php
		$this->echoOptionFooter( true );
	}
	
	/**
	 * Print single input field
	 * 
	 * @param string $value
	 * @param string $index
	 * @param boolean $is_new_field
	 */
	function print_field( $value = '', $index = '' ,$is_new_field = false ) {
		
		$name_attr = "name";
		$id_attr = "id";
		
		$name = "{$this->getID()}[{$index}]";
		$id = "{$this->getID()}_{$index}";
		
		if( $is_new_field ) {
			$name_attr = "data-{$name_attr}";
			$id_attr = "data-{$id_attr}";
		}
		
		printf('<div class="wpas_sc_mtext_field_group">
			<input type="text" class="regular-text" %s="%s" %s="%s" placeholder="%s" value="%s" />
			<span class="wpas_sc_remove_mtext_field">&nbsp;</span>
		</div>', 
			$name_attr,
			$name, 
			$id_attr,
			$id,
			isset( $this->settings['placeholder'] ) ? $this->settings['placeholder'] : '', 
			$value );
		
	}
	
    
    
}
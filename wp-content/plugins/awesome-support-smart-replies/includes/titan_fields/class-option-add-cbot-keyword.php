<?php

/**
 * New titan settings field class for facebook chat bot to add new keyword
 */
class TitanFrameworkOptionAddCbotKeyword extends TitanFrameworkOption {
        
    
    private static $row_Index = 0;
    
    
    /**
     * Print table row header for field name
     * 
     * @param boolean $showDesc
     * @param string $title
     * @param boolean $merge_header
     * 
     * @return void
     */
    public function echoOptionHeader( $showDesc = false, $title = "", $merge_header = false ) {
		// Allow overriding for custom styling
		$useCustom = false;
		$useCustom = apply_filters( 'tf_use_custom_option_header', $useCustom );
		$useCustom = apply_filters( 'tf_use_custom_option_header_' . $this->getOptionNamespace(), $useCustom );
		if ( $useCustom ) {
			do_action( 'tf_custom_option_header', $this );
			do_action( 'tf_custom_option_header_' . $this->getOptionNamespace(), $this );
			return;
		}

		$id = $this->getID();
		$name = $title ? $title : '';
		$evenOdd = self::$row_Index++ % 2 == 0 ? 'odd' : 'even';

		$style = $this->getHidden() == true ? 'style="display: none"' : '';

		?>
		<tr valign="top" class="row-<?php echo self::$row_Index ?> <?php echo $evenOdd ?>" <?php echo $style ?>>
			<?php if( !$merge_header ) { ?>
		<th scope="row" class="first">
			<label for="<?php echo ! empty( $id ) ? $id : '' ?>"><?php echo ! empty( $name ) ? $name : '' ?></label>
		</th>
		<?php } ?>
		<td colspan="<?php echo ( $merge_header ? 2 : 1 ); ?>" class="second tf-<?php echo $this->settings['type'] ?>">
		<?php

		$desc = $this->getDesc();
		if ( ! empty( $desc ) && $showDesc ) :
			?>
			<p class='description'><?php echo $desc ?></p>
			<?php
		endif;
	}
    
    /**
     * Display field
     */
    public function display() {
	    
		$this->echoOptionHeader( true , '', true );
		
		?>
			
		<a href="#" class="wpas_cbot_keyword_add_btn">Add a Keyword</a>
		
		<table class="form-table cbot_option_add_keyword">
			<?php
			
			
			$this->echoOptionHeader( true, __( 'Keyword match type', 'wpas_chatbot' ) );
			
			$match_types = wpas_cbot_keyword_match_types();
				
				echo '<p class="description"></p>
				<fieldset class="cbot_add_kw_type_field">';
				
				foreach ( $match_types as $match_type => $match_type_label ) {
					
					printf( '<label><input data-id="%s" data-name="%s" type="radio" value="%s" %s>%s</label><br>', 
						"keyword_match_type_{$match_type}",
						"[keyword_match_type]",
						$match_type,
						checked( 'contain', $match_type, false ),
						$match_type_label
						);
				}
				
				echo '</fieldset>';
			
			$this->echoOptionFooter( false );
			
			
			$this->echoOptionHeader( true, __( 'Include in smart replies search', 'wpas_chatbot' ) );
			echo '<input class="cbot_add_kw_sr_enabled_field" type="checkbox" value="yes" />';
			$this->echoOptionFooter( false );
			
			$this->echoOptionHeader( true, __( 'Keyword', 'wpas_chatbot' ) );
			printf('<input class="regular-text cbot_add_kw_keyword_field" placeholder="%s" type="text" value="" />', __( 'Keyword', 'wpas_chatbot' ) );
			printf( '<a href="#" data-action="delete" class="wpas_cbot_keyword_del_btn">%s</a></li>', __( 'Delete', 'wpas_chatbot' ) );
			$this->echoOptionFooter( false );
			
			$this->echoOptionHeader( true, __( 'Response', 'wpas_chatbot' ) );
			echo '<textarea class="cbot_add_kw_content_field" rows="4" autocomplete="off" cols="40"></textarea>';
			$this->echoOptionFooter( false );
			
			?>
		</table>
		
		
		<?php
			
		self::$row_Index++;
		$this->echoOptionFooter( false );
		
    }
    
	/**
	 * Clean value before saving
	 * 
	 * @param array $value
	 * 
	 * @return array
	 */
	public function cleanValueForSaving( $value ) {
		
		if( $value && is_array( $value ) ) {
			$value = array_map( 'stripslashes_deep', $value );
		}
		return $value;
	}
    
}
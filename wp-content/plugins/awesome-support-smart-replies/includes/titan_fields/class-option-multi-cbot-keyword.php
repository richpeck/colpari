<?php

/**
 * New titan settings field class for multiple facebook chat bot keywords
 */
class TitanFrameworkOptionMultiCbotKeyword extends TitanFrameworkOptionAddCbotKeyword {
        
    
    private static $row_Index = 0;
    
    /**
     * Display field
     */
    public function display() {
	    
		$value = $this->getValue();
		
		$keywords = $value && is_array( $value )? $value : array();
			
		foreach ( $keywords as $item_id => $item ) {

			$keyword = 	$item['keyword'];
			$keyword_content = 	$item['content'];
			$keyword_match_type = isset( $item['keyword_match_type'] ) ? $item['keyword_match_type'] : 'contain';

			$smart_replies_enabled = isset( $item['smart_replies_enabled'] ) ? $item['smart_replies_enabled'] : false;
			
			$name_prefix = $this->getID() . '['.self::$row_Index.']';
			$id_prefix = $this->getID() . '_' . self::$row_Index . '_';

			$this->echoOptionHeader( true , '', true );
			
			?>
			
			<table class="form-table cbot_option_multi_keyword" data-row_index="<?php echo self::$row_Index; ?>">
				<?php
				
				
				$this->echoOptionHeader( true, __( 'Keyword match type', 'wpas_chatbot' ) );
				
				
				$match_types = wpas_cbot_keyword_match_types();
				
				echo '<p class="description"></p>
				<fieldset>';
				
				foreach ( $match_types as $match_type => $match_type_label ) {
					
					printf( '<label for="%s"><input id="%s" type="radio" name="%s" value="%s" %s>%s</label><br>', 
						"{$id_prefix}keyword_match_type_{$match_type}",
						"{$id_prefix}keyword_match_type_{$match_type}",
						"{$name_prefix}[keyword_match_type]",
						$match_type,
						checked( $keyword_match_type, $match_type, false ),
						$match_type_label
						);
				}
				
				
				echo '</fieldset>';
				
				$this->echoOptionFooter( false );
				
				
				$this->echoOptionHeader( true, __( 'Include in smart replies search', 'wpas_chatbot' ) );
				
				printf('<input name="%s" id="%s" type="checkbox" value="yes" %s />',
				"{$name_prefix}[smart_replies_enabled]",
				"{$id_prefix}_smart_replies_enabled",
				checked( 'yes', $smart_replies_enabled, false )
				);
				
				$this->echoOptionFooter( false );
				
				$this->echoOptionHeader( true, __( 'Keyword', 'wpas_chatbot' ) );
				
				printf('<input class="regular-text" name="%s" placeholder="%s" id="%s" type="text" value="%s" />',
				"{$name_prefix}[keyword]",
				'Keyword',
				"{$id_prefix}_keyword",
				esc_attr( $keyword )
				);
				
				printf( '<a href="#" data-action="delete" class="wpas_cbot_keyword_del_btn">%s</a></li>', __( 'Delete', 'wpas_chatbot' ) );
				
				$this->echoOptionFooter( false );
				
				$this->echoOptionHeader( true, __( 'Response', 'wpas_chatbot' ) );
				printf( '<textarea rows="4" autocomplete="off" cols="40" name="%s" id="%s">%s</textarea>', "{$name_prefix}[content]", "{$id_prefix}_content", esc_attr( $keyword_content ) );
				$this->echoOptionFooter( false );
				
				?>
			</table>
			
			<?php
			
			self::$row_Index++;
			$this->echoOptionFooter( false );
		}
	

    }
    
    
}
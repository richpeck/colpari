<?php

/**
 * New titan settings field class for search post type settings in smart responses tab
 */
class TitanFrameworkOptionSearchPostType extends TitanFrameworkOptionMulticheck {
        
    
    
    /**
     * Display field
     */
    public function display() {
	    
		
		$this->echoOptionHeader( true );
		
		$savedValue = wpas_cbot_post_types();
		
		
		foreach ( $this->settings['options'] as $value => $label ) {
			
			$exclude_post_ids = '';
			$exclude_post_dates = '';
			$exclude_categories = array();
			$active = in_array( $value, $savedValue ) ? true : false;
			
				
			if( isset( $savedValue[ $value ] ) ) {
				
				if( is_array( $savedValue[ $value ] ) ) {
					
					$active = isset( $savedValue[ $value ]['active'] ) ? true : false;
					
					$exclude_post_ids = isset( $savedValue[ $value ]['exclude_post_ids'] ) ? $savedValue[ $value ]['exclude_post_ids'] : '';
					$exclude_post_dates = isset( $savedValue[ $value ]['exclude_post_dates'] ) ? $savedValue[ $value ]['exclude_post_dates'] : '';
					$exclude_categories = isset( $savedValue[ $value ]['exclude_categories'] ) ? $savedValue[ $value ]['exclude_categories'] : '';
				} else {
					$active = true;
				}
				
			} 
			
			$active_class = $active ? 'active' : '';
			
			$name_prefix = $this->getID() . "[{$value}]";
			$id_prefix = $this->getID() . "__{$value}__";
			
			?>


			<div class="wpas_cbot_search_post_type <?php echo $active_class; ?>">
			<?php
					
			printf('<label for="%s"><input class="post_type_checkbox" id="%s" type="checkbox" name="%s" value="%s" %s/> %s</label><br>',
				
				"{$id_prefix}_active",
				"{$id_prefix}_active",
				"{$name_prefix}[active]",
				'1',
				checked( $active, true, false ),
				$label
			);
			
			$object_taxonomies = get_object_taxonomies( $value );
			
			?>
			
			<div class="wpas_cbot_exclude_post_fields">
				<table>
					<tr>
						<th><label class="field_label"><?php _e( 'Exclude by Post Ids', 'wpas_chatbot' ); ?></label></th>
						<td><?php printf('<input class="regular-text" type="text" name="%s" id="%s" value="%s" />', 
									"{$name_prefix}[exclude_post_ids]", 
									"{$id_prefix}_exclude_post_ids",
									$exclude_post_ids
									); ?>
								
								<p class="description"><?php _e( 'Enter post ids separate by comma', 'wpas_chatbot' )?></p>
						</td>
					</tr>
					<tr>
						<th><label class="field_label"><?php _e( 'Exclude by Post Dates', 'wpas_chatbot' ); ?></label></th>
						<td>
							<?php printf('<input class="regular-text wpas-multidate" type="text" name="%s" id="%s" value="%s" />', 
									"{$name_prefix}[exclude_post_dates]", 
									"{$id_prefix}_exclude_post_dates",
									$exclude_post_dates
							); ?>
								
							<p class="description"><?php _e( 'Enter post dates separate by comma, format : yyyy-mm-dd', 'wpas_chatbot' ); ?></p>
						</td>
					</tr>
							
					<?php
					if( in_array( 'category', $object_taxonomies ) ) {
					?>
							
					<tr>
						<th><label class="field_label"><?php _e( 'Exclude by Categories', 'wpas_chatbot' ); ?></label> </th>
						<td>
							<?php 
							
							echo wpas_cbot_categories_dropdown( array(
								'name'          => "{$name_prefix}[exclude_categories][]",
								'id'            => "{$id_prefix}_exclude_categories",
								'class'         => '',
								'selected'		=> $exclude_categories,
								'multiple'		=> true
							) );
							
							?>
						</td>
					</tr>
							
					<?php
					}
					?>
							
				</table>	
			</div>
		</div>
			
			
		<?php
		}
		
		
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
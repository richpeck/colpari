<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class TitanFrameworkOptionWarningMessage extends TitanFrameworkOption {
        
    
    private static $row_Index = 0;
    
    
    /**
     * 
     * @param type $showDesc
     * @return type
     */
    public function echoOptionHeader($showDesc = false) {
        
        // Allow overriding for custom styling
	$useCustom = false;
	$useCustom = apply_filters( 'tf_use_custom_option_header', $useCustom );
	$useCustom = apply_filters( 'tf_use_custom_option_header_' . $this->getOptionNamespace(), $useCustom );
	if ( $useCustom ) {
		do_action( 'tf_custom_option_header', $this );
		do_action( 'tf_custom_option_header_' . $this->getOptionNamespace(), $this );
		return;
	}

        
	$evenOdd = self::$row_Index % 2 == 0 ? 'odd' : 'even';

	$style = $this->getHidden() == true ? 'style="display: none"' : '';

	?>
	<tr valign="top" class="row-<?php echo self::$row_Index ?> <?php echo $evenOdd ?>" <?php echo $style ?>>
	
	<td class="second tf-<?php echo $this->settings['type'] ?>" colspan="2">
	<?php

	$desc = $this->getDesc();
	if ( ! empty( $desc ) && $showDesc ) :
		?>
		<p class='description'><?php echo $desc ?></p>
		<?php
	endif;
    }
    
    /**
     * 
     * @return boolean
     */
    public function is_new() {
        return isset($this->settings['is_new']) && $this->settings['is_new'];
    }
    
    
    /**
     * Display field
     */
    public function display() {
        self::$row_Index++;
        ?>
                
        <tr valign="top" class="even first tf-heading autoclose_wm_field_header" data-header_num_row="<?=self::$row_Index?>">
            <?php if(!$this->is_new()) : ?>
            <th scope="row" class="first last" colspan="2">
                <h3>Warning Message</h3>
                <span class="field_delete delete_warningmessage">Delete</span>
            </th>
            <?php endif; ?>
	</tr>
                
                
        <?php
        
        $this->echoOptionHeader( true );
        
        
        ?>

        <div>
            <table class="warning-msg-table" id="warning-msg-table-<?=self::$row_Index?>">
                <?php 
                if(!$this->is_new()) : 
                    $this->printHiddenField(); 
                endif;
                ?>
                <?php $this->displayStatusSelectField(); ?>
                <?php $this->displayAgeField(); ?>
                <?php $this->displaySubjectField(); ?>
                <?php $this->displayMesageField(); ?>
                <?php $this->displayCloseField(); ?>
            </table>
        </div>

        <?php
        
        $this->echoOptionFooter(true);
    }
    
    /**
     * 
     * @param string $field
     * @param string $default
     * @return string
     */
    public function getFieldValue($field, $default = '') {
        $value = (!$this->is_new() && isset($this->settings['wm_data']) && $this->settings['wm_data']) ? $this->settings['wm_data']->{$field} : $default;
        
        return $value;
    }
    
    
    /**
     * print hidden field with row id
     */
    public function printHiddenField() {
        
        $name = $this->getOptionNamespace() . '_autoclose_wm['.self::$row_Index.'][id]';
        
        ?>
                
        <input type="hidden" name="<?=$name?>" value="<?=$this->settings['wm_data']->id?>" />
        
        <?php
    }
    
    /**
     * print ticket status field
     */
    public function displayStatusSelectField() {
        
        $id = ($this->is_new()) ? 'status' : 'autoclose_wm['.self::$row_Index.'][status]';
        
        
        $settings = array(
            'name'    => __( 'Status', WPASS_AUTOCLOSE_TEXT_DOMAIN ),
            'id'      => $id,
            'type'    => 'select',
            'default' => $this->getFieldValue('status'),
            'options' => wpas_get_post_status()
        );
        
        $obj = TitanFrameworkOption::factory( $settings, $this->owner );
        $obj->display();
    }
    
    /**
     * print age field
     */
    public function displayAgeField() {
        
        $id = ($this->is_new()) ? 'age' : 'autoclose_wm['.self::$row_Index.'][age]';
        
        $settings = array(
            'name'    => __( 'Age', WPASS_AUTOCLOSE_TEXT_DOMAIN ),
            'id'      => $id,
            'type'    => 'text',
            'default' => $this->getFieldValue('age'),
            'desc'    => __('Age is in Minutes: 1440 = 1 day, 2480 = 2 days, 7200 = 5 days, 10080 = 7 days, 14400 = 10 days', WPASS_AUTOCLOSE_TEXT_DOMAIN)
        );
        
        $obj = TitanFrameworkOption::factory( $settings, $this->owner );
        $obj->display();
    }
    

    /**
     * print subject field
     */
    public function displaySubjectField() {
        $id = ($this->is_new()) ? 'subject' : 'autoclose_wm['.self::$row_Index.'][subject]';
         
        $settings = array(
            'name'    => __( 'Subject', WPASS_AUTOCLOSE_TEXT_DOMAIN ),
            'id'      => $id,
            'type'    => 'text',
            'default' => $this->getFieldValue('subject')
        );
        
        $obj = TitanFrameworkOption::factory( $settings, $this->owner );
        $obj->display();
     }
    
     /**
      * print message field
      */
    public function displayMesageField() {
        
        $id = ($this->is_new()) ? 'msg' : 'autoclose_wm_'.self::$row_Index.'_msg';
        $textarea_name = $this->getOptionNamespace() . '_' . (($this->is_new()) ? 'msg' : 'autoclose_wm['.self::$row_Index.'][msg]');
        
        $settings = array(
            'name'    => __( 'Warning Message', WPASS_AUTOCLOSE_TEXT_DOMAIN ),
            'id'      => $id,
            'type'    => 'editor',
            'editor_settings' => array('textarea_name' => $textarea_name),
            'default' => $this->getFieldValue('message')
        );
        
        
        $obj = TitanFrameworkOption::factory( $settings, $this->owner );
        $obj->display();
    }
    
    /**
     * print close field
     */
    public function displayCloseField() {
        
        $id = ($this->is_new()) ? 'close' : 'autoclose_wm['.self::$row_Index.'][close]';
        $settings = array(
            'name'    => __( 'Close', WPASS_AUTOCLOSE_TEXT_DOMAIN ),
            'id'      => $id,
            'type'    => 'checkbox',
            'default' => $this->getFieldValue('close')
        );
        
        $obj = TitanFrameworkOption::factory( $settings, $this->owner );
        $obj->display();
    }
    
    
    
}
<div class="row-actions">
    <a href="#TB_inline?width=600&height=550&inlineId=nf-save-progress-modal" class="js--nf-saves-thickbox thickbox"
       data-save-id="<?php echo $save_id; ?>"
       data-updated="<?php echo $updated; ?>"
       data-convert-url="<?php echo $convert_url; ?>"
    >
        <?php _e( 'View Progress', 'ninja-forms' ); ?>
    </a> |
    <a href="<?php echo $convert_url; ?>">
        <?php _e( 'Convert to Submission', 'ninja-forms' ); ?>
    </a> |
    <span class="trash">
        <a href="<?php echo $delete_url; ?>"><?php _e( 'Delete', 'ninja-forms' ); ?></a>
    </span>
</div>
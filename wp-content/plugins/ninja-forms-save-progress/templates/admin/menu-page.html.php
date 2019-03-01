<div class="wrap">

    <h1>Saves</h1>

    <!-- SAVES TABLE -->
    <form method="POST">
        <input type="hidden" name="form_id" value="<?php echo $form_id; ?>">
        <?php $table->display(); ?>
    </form>

    <!-- PROGRESS MODAL -->
    <?php add_thickbox(); ?>
    <div id="nf-save-progress-modal" style="display:none;">
        <h2><?php _e( 'Updated', 'ninja-forms-save-progress' ); ?>: <span class="js--updated"></span></h2>
        <dl></dl>

        <p><a id="nf-save-progress-modal-convert" href="#" class="button button-primary"><?php _e( 'Convert to Submission', 'ninja-forms-save-progress' ); ?></a></p>
    </div>

</div>
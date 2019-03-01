<div class="as-fa-mobile-ticket-reply-content">

    <a href="#" class="as-fa-mobile-close-options">
        <i class="dashicons dashicons-no"></i>
    </a>

    <h3><?php _e( 'Write a reply', 'awesome-support-frontend-agents' ); ?> - <?php _e( 'Ticket', 'awesome-support-frontend-agents' ); ?> #<?php echo $id; ?></h3>

    <form class="wpas-fa-reply-form-mobile" enctype="multipart/form-data" method="post">

        <textarea class="as-fa-reply-editor-mobile as-fa-reply-editor-mobile-<?php echo $id; ?>" name="reply_content"></textarea>

        <br /><br />

        <label for="wpas-fa-attachment-<?php echo $id; ?>">
            <?php _e( 'Attachments', 'awesome-support' ); ?>
        </label>

        <br />

        <input type="file" name="wpas_files[]" multiple>

        <br />

        <?php

            $filetypes = WPAS_File_Upload::get_instance()->get_allowed_filetypes();
            $filetypes = explode( ',', $filetypes );

            foreach ( $filetypes as $key => $type ) {
                $filetypes[ $key ] = "<code>.$type</code>";
            }

            $filetypes = implode( ', ', $filetypes );

            printf( __( ' You can upload up to %d files (maximum %d MB each) of the following types: %s', 'awesome-support' ), (int) wpas_get_option( 'attachments_max' ), (int) wpas_get_option( 'filesize_max' ), apply_filters( 'wpas_attachments_filetypes_display', $filetypes ) )

        ?>

        <br /><br />

        <input type="button" class="as-fa-mobile-button as-fa-mobile-reply-action" data-id="<?php echo $id; ?>" data-action="ticket_reply" value="<?php _e( 'Reply', 'awesome-support' ); ?>" />
        <input type="button" class="as-fa-mobile-button as-fa-mobile-reply-action" data-id="<?php echo $id; ?>" data-action="ticket_reply_close" value="<?php _e( 'Reply & Close', 'awesome-support' ); ?>" />
        <input type="button" class="as-fa-mobile-button cancel as-fa-mobile-close-options" value="<?php _e( 'Cancel', 'awesome-support' ); ?>" />

        
        <br /><br />
        

    </form>

</div>

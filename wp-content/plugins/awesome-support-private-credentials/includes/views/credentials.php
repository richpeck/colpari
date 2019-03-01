<?php 

if ( ! defined( 'WPINC' ) ) {
	die;
}

?>

<h3><?php  _e( 'Private Credentials', 'wpas-pc' ); ?></h3>

<?php if ( count ( $credentials ) > 0 ): ?>

    <?php foreach ( $credentials as $i => $data ): ?>

        <table class="as-pc-table">
            <tr>
                <td colspan="4" class="as-pc-system">
                    <?php echo $data['system'];  ?>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>
                        <?php _e( 'Username', 'wpas-pc' ); ?>
                    </strong> <br />
                    <?php echo $data['username']; ?>
                </td>
                <td>
                    <strong>
                        <?php _e( 'Password', 'wpas-pc' ); ?>
                    </strong> <br />
                    <?php echo $data['password']; ?>
                </td>
                <td>
                    <strong>
                        <?php _e( 'URL', 'wpas-pc' ); ?>
                    </strong> <br />
                    <?php echo $data['url']; ?>
                </td>
                <td>
                    <strong>
                        <?php _e( 'Note', 'wpas-pc' ); ?>
                    </strong> <br />
                    <?php echo $data['note']; ?>
                </td>
            </tr>

            <tr>
                <td colspan="4">
                    <a href="#" class="wpas-pc-btn wpas-pc-load" data-view="edit_credentials" data-post-id="<?php echo $id; ?>" data-key="<?php echo $i; ?>">
                        <?php _e( 'Edit', 'wpas-pc' ); ?>
                    </a>
                    <a href="#" class="wpas-pc-btn wpas-pc-action" data-action="delete_credentials" data-post-id="<?php echo $id; ?>" data-key="<?php echo $i; ?>">
                        <?php _e( 'Delete', 'wpas-pc' ); ?>
                    </a>
                </td>
            </tr>
        </table>

    <?php endforeach; ?>

<?php else: ?>

    <?php _e( 'You dont have any saved credentials', 'wpas-pc'); ?>

<?php endif; ?>

<div class="as-pc-buttons">

    <div class="as-pc-modal-status"></div>

    <a href="#" class="wpas-pc-btn large wpas-pc-load" data-view="add_credentials" data-post-id="<?php echo $id; ?>"><?php _e( 'Add credentials', 'wpas-pc' ); ?></a>
    <a href="#" class="wpas-pc-btn large as-pc-modal-close"><?php _e( 'Close', 'wpas-pc' ); ?></a>

</div>




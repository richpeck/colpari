<?php 

if ( ! defined( 'WPINC' ) ) {
	die;
}


$credentials = $this->get_credentials( $id, true );

$data = array();

foreach( $credentials as $i => $values ) {

    if ( $key == $i ) {
        $data = $values;
        break;
    }
}

?>

<h3><?php  _e( 'Edit Private Credentials', 'wpas-pc' ); ?></h3>

<form action="#" id="as-pc-save-data-form">

    <input type="hidden" name="post_id" value="<?php echo $id; ?>">
    <input type="hidden" name="key" value="<?php echo $key; ?>">
    <input type="hidden" name="crypt_key" value="">

    <table class="as-pc-table">
        <tr>
            <td><?php _e( 'System', 'wpas-pc' ); ?></td>
            <td><input type="text" name="system" placeholder="<?php _e( 'cPanel, WordPress, FTP, ...', 'wpas-pc' ); ?>" value="<?php echo $data['system']; ?>" required></td>
        </tr>
        <tr>
            <td><?php _e( 'Username', 'wpas-pc' ); ?></td>
            <td><input type="text" name="username" value="<?php echo $data['username']; ?>" required></td>
        </tr>
        <tr>
            <td><?php _e( 'Password', 'wpas-pc' ); ?></td>
            <td><input type="text" name="password" value="<?php echo $data['password']; ?>" required></td>
        </tr>
        <tr>
            <td><?php _e( 'URL', 'wpas-pc' ); ?></td>
            <td><input type="text" name="url" value="<?php echo $data['url']; ?>"></td>
        </tr>
        <tr>
            <td colspan="2">
                <?php _e( 'Note', 'wpas-pc' ); ?> <br /><br />
                <textarea name="note"><?php echo $data['note']; ?></textarea>       
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <label>
                    <input type="checkbox" name="reset_key" id="as-pc-crypt-key">
                    <?php _e( 'Reset encryption key on Save', 'wpas-pc' ); ?>
                </label>
                
            </td>
        </tr>
    </table>

    <div class="as-pc-buttons">

        <div class="as-pc-modal-status"></div>

        <input type="submit" class="wpas-pc-btn large" value="<?php _e( 'Save', 'wpas-pc' ); ?>"> 
        <a href="#" class="wpas-pc-btn large as-pc-modal-close"><?php _e( 'Cancel', 'wpas-pc' ); ?></a>
    </div>



</form>



<select name="form_id" class="js--nf-saves-filter">
    <?php foreach( $forms as $form ): ?>
        <option value="<?php echo $form->get_id(); ?>" <?php if( $form_id == $form->get_id() ) echo ' selected'; ?>>
            <?php echo $form->get_setting( 'title' ); ?>
        </option>
    <?php endforeach; ?>
</select>

<select name="user_id" class="js--nf-saves-filter">
    <option value="">-- User</option>
    <?php foreach( $users as $user ): ?>
        <option value="<?php echo $user->ID; ?>" <?php if( $user_id == $user->ID ) echo ' selected'; ?>>
            <?php echo $user->display_name; ?>
        </option>
    <?php endforeach; ?>
</select>
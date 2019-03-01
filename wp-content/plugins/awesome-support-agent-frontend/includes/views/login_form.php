<div id="as-fa-login-form">
    <form id="wpas-login-form" action="#" method="post">

        <h1><?php _e( 'Login', 'awesome-support-frontend-agents' ); ?></h1>

        <label for="wpas-login-username"><?php _e( 'Username', 'awesome-support-frontend-agents' ); ?></label>
        <input id="wpas-login-username" type="text" name="username" required>
        <label for="wpas-login-username"><?php _e( 'Password', 'awesome-support-frontend-agents' ); ?></label>
        <input id="wpas-login-username" type="password" name="password" required>
        <label for="wpas-login-remember"><?php _e( 'Remember Me' ); ?></label>
        <input type="checkbox" name="remember" id="wpas-login-remember">
        <input id="wpas-login-submit" class="wpas-fa-btn" type="submit" value="<?php _e( 'Login', 'awesome-support-frontend-agents' ); ?>" name="submit">

        <p id="wpas-login-status"></p>

    </form>
</div>
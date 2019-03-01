<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class NF_UserManagement_MergeTags_PasswordReset
 */
final class NF_UserManagement_MergeTags_PasswordReset extends NF_Abstracts_MergeTags
{
    protected $id = 'user-management';

    public function __construct()
    {
        parent::__construct();

        $this->title = __( 'Password Reset', 'ninja-forms-user-management' );

        //Merge tag settings array.
        $this->merge_tags = array(
            'password_reset' => array(
                'id' => 'password_reset',
                'tag' => '{user_management:password_reset}',
                'label' => __( 'Password Reset', 'ninja-forms-user-management' ),
                'callback' => 'get_password_reset'
            ),
        );
    }

    /**
     * Get Password Reset
     *
     * Callback for the password reset merge tag.
     * Displays password reset link.
     *
     * @return string
     */
    public function get_password_reset()
    {
        //Gets the site URL.
        $site_url = get_site_url();

        //Builds the password reset link.
        $message = 'Reset Password';
        $password_link =  '<a href="' . $site_url . '/wp-login.php?action=lostpassword">'. $message .'</a>';
        apply_filters( 'nf_password_reset', $message );

        return $password_link;
    }

} // END CLASS NF_UserManagement_MergeTags_PasswordReset

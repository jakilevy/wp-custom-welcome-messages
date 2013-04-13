<?php
/*
 * Plugin Name: WP Custom Welcome Messages
 * Plugin URI: 
 * Description: Customize the default Wordpress welcome email when a user registers on your site.
 * Version: 1.0
 * Author: Ron Z
 * Author URI: 
 * License: License.txt
 */

/*  Copyright 2012  

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
 * Add submenu page under Settings menu
 */

if(!function_exists('nme_register_custom_user_email_page')){
    function nme_register_custom_user_email_page(){
        add_submenu_page('options-general.php', 'Welcome Email Settings', 'Welcome Email', 'manage_options', 'custom_user_email', 'nme_welcome_email_customization');
    }
}

/*
 * Register funuction for admin menu
 */

if(is_admin()){
    add_action('admin_menu', 'nme_register_custom_user_email_page');
}

/*
 * Function that shows form in admin to save options to customize user email
 */

function nme_welcome_email_customization(){
    $message_body = '';
    if(isset($_POST['save_settings'])){
        $from_email = $_POST['from_email'];
        $from_name = $_POST['from_name'];
        $subject = $_POST['subject_line'];
        $message_body = stripslashes($_POST['text_messege']);
        update_option('_custom_user_notifiaction', array('from_email'=> $from_email,'from_name' =>$from_name,
            'subject_line'=> $subject, 'text_messege'=> $message_body));
        echo '<div class="updated"><p>Settings Saved</p></div>';
    }
    $option = get_option('_custom_user_notifiaction');
    $settings = array('textarea_name' => 'text_messege',
                        'textarea_rows ' => 5,
                        '');
    ?>
    <div class="wrap">
        <div id="icon-options-general" class="icon32"></div>
        <h2>Customize Welcome Email</h2>
        <form action="" method="POST" >
            <table width="70%">
                <tr><td><label for="from-email">From Email</label></td><td><input type="text" name="from_email" value="<?php echo $option['from_email'];?>" id="from-email" class="regular-text" /></td></tr>
                <tr><td><label for="from-name">From Name</label></td><td><input type="text" name="from_name" value="<?php echo $option['from_name'];?>" id="from-name" class="regular-text" /></td></tr>
                <tr><td><label for="subject-line">Subject Line</label></td><td><input type="text" name="subject_line" value="<?php echo $option['subject_line'];?>" id="subject-line" class="regular-text" /></td></tr>
                <tr><td><label for="text-message">Message</label></td><td><?php wp_editor($option['text_messege'], 'text_messege', $settings); ?></td></tr>
                <tr><td></td><td><span class="description">You can use these tags to notify user: %user_login% , %user_email%, %password% %login_url%, %site_name% </span></td></tr>
                <tr><td colspan="2"><center><input type="submit" name="save_settings" value="Save Settings" id="" class="button-primary" /></center></td></tr>
            </table>
        </form>
    </div>
<?php
}

/**
 * Notify the site admin of a new user, normally via email and send email to user
 * Some tags are used to replace - %user_login%, %user_email%, %password%, %login_url%
 * @param int $user_id User ID
 * @param string $plaintext_pass Optional. The user's plaintext password
 */

if(!function_exists('wp_new_user_notification') ){
    function wp_new_user_notification($user_id, $plaintext_pass){
        $user = new WP_User($user_id);
            $option = get_option('_custom_user_notifiaction');
            $message = $option['text_messege'];
            $user_login = stripslashes($user->user_login);
            $user_email = stripslashes($user->user_email);
            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

            if(!empty($option['from_email'])){
                $admin_email = $option['from_email'];
            }
            else{
                $admin_email = get_option('admin_email');
            }
            if(!empty($option['subject_line'])){
                $subject = $option['subject_line'];
            }
            else{
                $subject = 'Welcome to '.$blogname;
            }

            //set header for email
            $headers = '';
            $headers .= 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html' . "\r\n";
            $headers .= 'From: ' . $option['from_name'] . ' <' . $admin_email . '>' . "\r\n";
            $message = str_replace("\n", "<br />", $message);
            $message1 = str_replace(array('%user_login%', '%user_email%', '%password%', '%login_url%', '%site_name%'), array($user_login, $user_email, '','', $blogname), $message);
            //send email to admin
            @wp_mail($admin_email, $option['subject_line'], $message1, $headers);

            if ( empty($plaintext_pass) )
                    return;
            $message2 = str_replace(array('%user_login%','%user_email%', '%password%', '%login_url%', '%site_name%'), array($user_login, $user_email, $plaintext_pass, wp_login_url(), $blogname), $message);
            //send email to user
            wp_mail($user_email, $option['subject_line'], $message2, $headers);
    }
}
?>
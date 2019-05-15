<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 7/31/18
 * Time: 12:25 PM
 */


/**
 * The template for displaying map pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */


//https://codex.wordpress.org/Customizing_the_Login_Form#Make_a_Custom_Login_Page
if ( ! is_user_logged_in() ) { // Display WordPress login form:







    ///////////
    echo "<div id='go_password_change_container' style='display: none;'>";

    ?><form method='post' action='changepassword'>
        <div class='mypageMyDetailsBox'>
            <span class='titleSub'>Password</span>
            <table width='90%' align="center">
                <tr>
                    <td width='40%'>Current Password</td>
                    <td width='60%'><input type='text' name='currentpassword' size='70'></td>
                </tr>
                <tr>
                    <td>New Password</td>
                    <td><input type='text' name='newpassword' size='70'></td>
                </tr>
                <tr>
                    <td>Confirm New Password</td>
                    <td><input type='text' name='confirmpassword' size='70'></td>
                </tr>
            </table>
        </div>

        </div>
        <div align='center'>
            <input type='submit' name='submit_update' value='Update' class='subUpt'>
        </div>
    </form>
    //add button to send to function to change password.
    //need to add action, nonce, verify to the function
    //need js action to send ajax and process the result.

<?php



    echo "</div>";



} else { // If logged in:
    wp_loginout( home_url() ); // Display "Log Out" link.
    echo " | ";
    wp_register('', ''); // Display "Site Admin" link.
}



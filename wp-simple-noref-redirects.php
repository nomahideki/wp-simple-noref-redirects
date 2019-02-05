<?php
/*
  Plugin Name: Simple noref Redirects
  Description: Create a list of URLs that you would like to redirect without referrer to another page or site. Now with wildcard support.Derived from http://www.scottnelle.com/simple-noref-redirects-plugin-for-wordpress/
  Version: 1.06
  Author: Hideki Noma
  Author URI: http://www.logitoy.jp/
 */

/*  Copyright 2016-2019  Hideki Noma  (email : r-wp@logitoy.jp)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1noref  USA
 */

if (!class_exists("SimpleNorefRirects")) {

    class SimpleNorefRedirects {
        private $dev_info = null;

        /**
         * create_menu function
         * generate the link to the options page under settings
         * @access public
         * @return void
         */
        function create_menu() {
            add_options_page('Noref Redirects', 'Noref Redirects', 'manage_options', 'noref_options', array($this, 'options_page'));
        }

        /**
         * options_page function
         * generate the options page in the wordpress admin
         * @access public
         * @return void
         */
        function options_page() {
            ?>
            <div class="wrap simple_noref_redirects">
                <script>
                    //todo: This should be enqued
                    jQuery(document).ready(function () {
                        jQuery('span.wpsnoref-delete').html('Delete').css({'color': 'red', 'cursor': 'pointer'}).click(function () {
                            var confirm_delete = confirm('Delete This Redirect?');
                            if (confirm_delete) {

                                // remove element and submit
                                jQuery(this).parent().parent().remove();
                                jQuery('#simple_noref_redirects_form').submit();

                            }
                        });

                        jQuery('.simple_noref_redirects .documentation').hide().before('<p><a class="reveal-documentation" href="#">Documentation</a></p>')
                        jQuery('.reveal-documentation').click(function () {
                            jQuery(this).parent().siblings('.documentation').slideToggle();
                            return false;
                        });
                    });
                </script>

                <?php
                if (isset($_POST['noref_redirects'])) {
                    echo '<div id="message" class="updated"><p>Settings saved</p></div>';
                }
                ?>

                <h2>Simple noref Redirects</h2>

                <form method="post" id="simple_noref_redirects_form" action="options-general.php?page=noref_options&savedata=true">

                    <?php wp_nonce_field('save_redirects', '_snorefr_nonce'); ?>

                    <table class="widefat">
                        <thead>
                            <tr>
                                <th colspan="2">Request</th>
                                <th colspan="1">Destination<br />default</th>
                                <th colspan="1">Destination<br />Android</th>
                                <th colspan="1">Destination<br />iPhone</th>
                                <th colspan="1">Destination<br />Tablet</th>
                                <th colspan="1">Destination<br />iPad</th>
                                <th colspan="1">Delay<br />Type</th>
                                <th colspan="2">Original Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="2"><small>example: /about.htm</small></td>
                                <td colspan="2"><small>example: <?php echo get_option('home'); ?>/about/</small></td>
                            </tr>
                            <?php echo $this->expand_redirects(); ?>
                            <tr>
                                <td style="width:10%;"><input type="text" name="noref_redirects[request][]" value="" style="width:99%;" /></td>
                                <td style="width:2%;">&raquo;</td>
                                <td style="width:15%;"><input type="text" name="noref_redirects[destination][]" value="" style="width:99%;" /></td>
                                <td style="width:12%;"><input type="text" name="noref_redirects[destination_sp][]" value="" style="width:99%;" /></td>
                                <td style="width:12%;"><input type="text" name="noref_redirects[destination_ios][]" value="" style="width:99%;" /></td>
                                <td style="width:12%;"><input type="text" name="noref_redirects[destination_tablet][]" value="" style="width:99%;" /></td>
                                <td style="width:12%;"><input type="text" name="noref_redirects[destination_ipad][]" value="" style="width:99%;" /></td>
                                <td style="width:5%;">
                                    <input type="text" name="noref_redirects[delay][]" value="" style="width:99%;" />
                                    <select name="noref_redirects[type][]">
                                        <option value="noref">noref</option>
                                        <option value="origin">origin</option>
                                        <option value="none">none</option>
                                    </select>
                                </td>
                                <td style="width:20%;"><textarea type="text" name="noref_redirects[message][]" style="width:99%;" /></textarea></td>
                                <td><span class="wpsnoref-delete">Delete</span></td>
                            </tr>
                        </tbody>
                    </table>

                    <?php $wildcard_checked = (get_option('noref_redirects_wildcard') === 'true' ? ' checked="checked"' : ''); ?>
                    <p><input type="checkbox" name="noref_redirects[wildcard]" id="wpsnoref-wildcard"<?php echo $wildcard_checked; ?> /><label for="wpsnoref-wildcard"> Use Wildcards?</label></p>

                    <p class="submit"><input type="submit" name="submit_noref" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
                </form>
                <div class="documentation">
                    <h2>Documentation</h2>
                    <h3>Simple Redirects</h3>
                    <p>Simple redirects work similar to the format that Apache uses: the request should be relative to your WordPress root. The destination can be either a full URL to any page on the web, or relative to your WordPress root.</p>
                    <h4>Example</h4>
                    <ul>
                        <li><strong>Request:</strong> /old-page/</li>
                        <li><strong>Destination:</strong> /new-page/</li>
                    </ul>

                    <h3>Wildcards</h3>
                    <p>To use wildcards, put an asterisk (*) after the folder name that you want to redirect.</p>
                    <h4>Example</h4>
                    <ul>
                        <li><strong>Request:</strong> /old-folder/*</li>
                        <li><strong>Destination:</strong> /redirect-everything-here/</li>
                    </ul>

                    <p>You can also use the asterisk in the destination to replace whatever it matched in the request if you like. Something like this:</p>
                    <h4>Example</h4>
                    <ul>
                        <li><strong>Request:</strong> /old-folder/*</li>
                        <li><strong>Destination:</strong> /some/other/folder/*</li>
                    </ul>
                    <p>Or:</p>
                    <ul>
                        <li><strong>Request:</strong> /old-folder/*/content/</li>
                        <li><strong>Destination:</strong> /some/other/folder/*</li>
                    </ul>
                </div>
            </div>
            <?php
        }

// end of function options_page

        /**
         * expand_redirects function
         * utility function to return the current list of redirects as form fields
         * @access public
         * @return string <html>
         */
        function expand_redirects() {
            $redirects = get_option('noref_redirects');
            $output = '';
            if (!empty($redirects)) {
                foreach ($redirects as $request => $data) {
                    $setting = unserialize($data);
                    $destination = $setting['destination'];
                    $destination_sp = $setting['destination_sp'];
                    $destination_ios = $setting['destination_ios'];
                    $destination_tablet = $setting['destination_tablet'];
                    $destination_ipad = $setting['destination_ipad'];
                    $delay = $setting['delay'];
                    $type = $setting['type'];
                    $message = htmlentities($setting['message']);
                    $output .= '

					<tr>
						<td><input type="text" name="noref_redirects[request][]" value="' . $request . '" style="width:99%" /></td>
						<td>&raquo;</td>
						<td><input type="text" name="noref_redirects[destination][]" value="' . $destination . '" style="width:99%;" /></td>
						<td><input type="text" name="noref_redirects[destination_sp][]" value="' . $destination_sp . '" style="width:99%;" /></td>
						<td><input type="text" name="noref_redirects[destination_ios][]" value="' . $destination_ios . '" style="width:99%;" /></td>
						<td><input type="text" name="noref_redirects[destination_tablet][]" value="' . $destination_tablet . '" style="width:99%;" /></td>
						<td><input type="text" name="noref_redirects[destination_ipad][]" value="' . $destination_ipad . '" style="width:99%;" /></td>
						<td>
                                                    <input type="text" name="noref_redirects[delay][]" value="' . $delay . '" style="width:99%;" />
                                                    <select name="noref_redirects[type][]">
                                                        <option value="noref"' . ($type == 'noref' ? ' selected' : '') .'>noref</option>
                                                        <option value="origin"' . ($type == 'origin' ? ' selected' : '') .'>origin</option>
                                                        <option value="none"' . ($type == 'none' ? ' selected' : '') .'>none</option>
                                                    </select>
                                                </td>
                                                <td><textarea type="text" name="noref_redirects[message][]" style="width:99%;" />' . $message . '</textarea></td>
						<td><span class="wpsnoref-delete"></span></td>
					</tr>

					';
                }
            } // end if
            return $output;
        }

        /**
         * save_redirects function
         * save the redirects from the options page to the database
         * @access public
         * @param mixed $data
         * @return void
         */
        function save_redirects($data) {
            if (!current_user_can('manage_options')) {
                wp_die('You do not have sufficient permissions to access this page.');
            }
            check_admin_referer('save_redirects', '_snorefr_nonce');

            $data = $_POST['noref_redirects'];

            $redirects = array();

            for ($i = 0; $i < sizeof($data['request']); ++$i) {
                $request = trim(sanitize_text_field($data['request'][$i]));
                $destination = trim(sanitize_text_field($data['destination'][$i]));
                $destination_sp = trim(sanitize_text_field($data['destination_sp'][$i]));
                $destination_ios = trim(sanitize_text_field($data['destination_ios'][$i]));
                $destination_tablet = trim(sanitize_text_field($data['destination_tablet'][$i]));
                $destination_ipad = trim(sanitize_text_field($data['destination_ipad'][$i]));
                $delay = trim(sanitize_text_field($data['delay'][$i]));
                $type = trim(sanitize_text_field($data['type'][$i]));
                $message = trim($data['message'][$i]);
                if ($request == '' && $destination == '') {
                    continue;
                } else {
                    $redirects[$request] = serialize(array(
                        'destination' => $destination,
                        'destination_sp' => $destination_sp,
                        'destination_ios' => $destination_ios,
                        'destination_tablet' => $destination_tablet,
                        'destination_ipad' => $destination_ipad,
                        'delay' => $delay,
                        'type' => $type,
                        'message' => $message
                    ));
                }
            }

            update_option('noref_redirects', $redirects);

            if (isset($data['wildcard'])) {
                update_option('noref_redirects_wildcard', 'true');
            } else {
                delete_option('noref_redirects_wildcard');
            }
        }

        /*
            iPhoneが含まれている場合、スマートフォン
            iPodが含まれている場合、スマートフォン
            iPadが含まれている場合、タブレット

            AndroidとMobileが含まれている場合、スマートフォン
            AndroidでMobileが含まれていない場合、タブレット

            Windows PhoneとMobileが含まれている場合、スマートフォン
            Windows PhoneとTabletが含まれている場合、タブレット

            FirefoxとMobileが含まれている場合、スマートフォン
            FirefoxとTabletが含まれている場合、タブレット

            Mobile Safari または Opera Mini が含まれている場合、スマートフォン
         */
        function get_device_type(){
            if ($this->dev_info){
                return $this->dev_info;
            }
            $ua = $_SERVER['HTTP_USER_AGENT'];
            $dev_type = 'pc';
            $os_type = 'windows';
            if (strpos($ua, 'Mobile') !== false){
                $dev_type = 'smartphone';
            }
            elseif (strpos($ua, 'Tablet') !== false){
                $dev_type = 'tablet';
            }
            if (strpos($ua, 'Android') !== false){
                $dev_type = 'smartphone';
                echo strpos($ua, 'Android');
                $os_type = 'Android';
                if (strpos($ua, 'Mobile') === false){
                    $dev_type = 'tablet';
                }
            }
            elseif (strpos($ua, 'iPhone') !== false){
                $dev_type = 'smartphone';
                $os_type = 'iOS';
            }
            elseif (strpos($ua, 'iPod') !== false){
                $dev_type = 'smartphone';
                $os_type = 'iOS';
            }
            elseif (strpos($ua, 'iPad') !== false){
                $dev_type = 'tablet';
                $os_type = 'iOS';
            }
            elseif (strpos($ua, 'Windows Phone') !== false){
                $os_type = 'Windows Phone';
            }
            elseif (strpos($ua, 'Firefox') !== false){
                $os_type = 'Firefox';
            }
            elseif (strpos($ua, 'Mobile Safari') !== false){
                $dev_type = 'smartphone';
                $os_type = 'Safari';
            }
            elseif (strpos($ua, 'Opera mini') !== false){
                $dev_type = 'smartphone';
                $os_type = 'Opera';
            }
            $this->dev_info = array($dev_type, $os_type);
            return $this->dev_info;
        }

        function is_sp(){
            $dev_info = $this->get_device_type();
            if ($dev_info[0] === 'smartphone'){
                return true;
            }
            else {
                return false;
            }
        }

        function is_ios(){
            $dev_info = $this->get_device_type();
            if ($dev_info[1] === 'iOS'){
                return true;
            }
            else {
                return false;
            }
        }

        function is_tablet(){
            $dev_info = $this->get_device_type();
            if ($dev_info[0] === 'tablet'){
                return true;
            }
            else {
                return false;
            }
        }

        /**
         * redirect function
         * Read the list of redirects and if the current page
         * is found in the list, send the visitor on her way
         * @access public
         * @return void
         */
        function redirect() {
            // this is what the user asked for (strip out home portion, case insensitive)
            $userrequest = rtrim(str_ireplace(get_option('home'), '', $this->get_address()), '/');
            $redirects = get_option('noref_redirects');
            if (!empty($redirects)) {
                $wildcard = get_option('noref_redirects_wildcard');
                $do_redirect = '';

                // compare user request to each noref stored in the db
                foreach ($redirects as $storedrequest => $data) {
                    $setting = unserialize($data);
                    $destination = $setting['destination'];
                    if ($this->is_sp()){
                        if ($setting['destination_sp']){
                            $destination = $setting['destination_sp'];
                        }
                        if ($this->is_ios()){
                            if ($setting['destination_ios']){
                                $destination = $setting['destination_ios'];
                            }
                        }
                    }
                    if ($this->is_tablet()){
                        if ($setting['destination_tablet']){
                            $destination = $setting['destination_tablet'];
                        }
                        if ($this->is_ios()){
                            if ($setting['destination_ipad']){
                                $destination = $setting['destination_ipad'];
                            }
                        }
                    }
                    $delay = $setting['delay'];
                    $type = $setting['type'];
                    $message = $setting['message'];
                    if (isset($_GET['ar'])){
                        $force_refresh = true;
                        if ($delay < 0){
                            $delay = 0;
                        }
                    }
                    else {
                        $force_refresh = false;
                    }
                    // check if we should use regex search
                    $canonical_url = $userrequest;
                    if ($wildcard === 'true' && strpos($storedrequest, '*') !== false) {
                        // wildcard redirect
                        // don't allow people to accidentally lock themselves out of admin
                        if (strpos($userrequest, '/wp-login') !== 0 && strpos($userrequest, '/wp-admin') !== 0) {
                            // Make sure it gets all the proper decoding and rtrim action
                            $storedrequest = str_replace('\\', '\\\\', $storedrequest);
                            $storedrequest = str_replace('.', '\\.', $storedrequest);
                            $storedrequest = str_replace('?', '\\?', $storedrequest);
                            $storedrequest = str_replace('*', '(.*)', $storedrequest);
                            $pattern = '/^' . str_replace('/', '\/', rtrim($storedrequest, '/')) . '/';
                            $destination = str_replace('*', '$1', $destination);
                            $output = preg_replace($pattern, $destination, $userrequest);
                            if ($output !== $userrequest) {
                                // pattern matched, perform redirect
                                $do_redirect = $output;
                            }
                        }
                    } elseif (urldecode($userrequest) == rtrim($storedrequest, '/')) {
                        // simple comparison redirect
                        $do_redirect = $destination;
                    }

                    // redirect. the second condition here prevents redirect loops as a result of wildcards.
                    if ($do_redirect !== '' && trim($do_redirect, '/') !== trim($userrequest, '/')) {
                        // check if destination needs the domain prepended

                        if ($type == 'noref'){
                            header('Referrer-Policy: no-referrer');
                        }
                        elseif ($type == 'origin'){
                            header('Referrer-Policy: origin');
                        }
                        if (strpos($do_redirect, '/') === 0) {
                            $do_redirect = home_url() . $do_redirect;
                        }
                        $do_header_refresh = false;
                        $meta_noref = 'no-referrer';
                        if (strpos('MSIE', $_SERVER['HTTP_USER_AGENT']) !== false) {
                            $do_header_refresh = true;
                            $meta_noref = 'never'; // IE11までとEdge
                        }
                        if ($do_header_refresh == true) {
                            header('Refresh:' . $delay . ';URL=' . $do_redirect);
                        }

                        ?>
                        <!doctype html>
                        <html lang="ja">
                            <head>
                                <meta charset="utf-8" />
                                <?php if ($type=='noref'){ ?><meta name="referrer" content="<?php echo $meta_noref; ?>" /><?php } ?>
                                <?php if ($type=='origin'){ ?><meta name="referrer" content="origin" /><?php } ?>
                                <script>

                                    function check_ie() {
                                        var ua = window.navigator.userAgent.toLowerCase();
                                        var ver = window.navigator.appVersion.toLowerCase();
                                        var name = 'unknown';
                                        if (ua.indexOf("msie") != -1) {
                                            if (ver.indexOf("msie 6.") != -1) {
                                                return true;
                                            } else if (ver.indexOf("msie 7.") != -1) {
                                                name = 'ie7';
                                                return true;
                                            } else if (ver.indexOf("msie 8.") != -1) {
                                                name = 'ie8';
                                                return true;
                                            } else if (ver.indexOf("msie 9.") != -1) {
                                                name = 'ie9';
                                                return true;
                                            } else if (ver.indexOf("msie 10.") != -1) {
                                                name = 'ie10';
                                                return true;
                                            } else if (ver.indexOf("msie 11.") != -1) {
                                                name = 'ie11';
                                                return true;
                                            } else {
                                                name = 'ie';
                                                return false;
                                            }
                                        }
                                        return false;
                                    }


                                    if (check_ie()) {
                                        <?php if ($delay >= 0): ?>
                                        setTimeout(function(){ location.href = "<?php echo $do_redirect; ?>"; }, <?php echo $delay * 1000 + 1; ?>);
                                        <?php endif; ?>
                                    }
                                </script>
                                <link rel="canonical" href="<?php echo $canonical_url; ?>" />
                                <?php if ($delay >= 0): ?>
                                <meta http-equiv="refresh" content="<?php echo $delay; ?>; URL=<?php echo $do_redirect; ?>" />
                                <?php endif; ?>
                            </head>
                            <body>
                                <?php if ($message != ''): ?>
                                <?php echo $message; ?>
                                <?php else: ?>
                                <h1>画面切り替え中・・・</h1>
                                <p>画面が切り替わらない場合は <a href="<?php echo $do_redirect; ?>"<?php if ($type=='noref'){ ?> rel="noreferrer"<?php } ?><?php if ($type=='origin'){ ?> rel="origin"<?php } ?>>こちらのリンク</a>をクリックしてください。</p>
                                <?php endif; ?>
                            </body>
                        </html>
                        <?php
                        exit();
                    } else {
                        unset($redirects);
                    }
                }
            }
        }

// end funcion redirect

        /**
         * getAddress function
         * utility function to get the full address of the current request
         * credit: http://www.phpro.org/examples/Get-Full-URL.html
         * @access public
         * @return void
         */
        function get_address() {
            // return the full address
            return $this->get_protocol() . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }

// end function get_address

        function get_protocol() {
            // Set the base protocol to http
            $protocol = 'http';
            // check for https
            if (isset($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == "on") {
                $protocol .= "s";
            }

            return $protocol;
        }

// end function get_protocol
    }

    // end class SimplenorefRedirects
} // end check for existance of class
// instantiate
$redirect_plugin = new SimpleNorefRedirects();

if (isset($redirect_plugin)) {
    // add the redirect action, high priority
    add_action('init', array($redirect_plugin, 'redirect'), 1);

    // create the menu
    add_action('admin_menu', array($redirect_plugin, 'create_menu'));

    // if submitted, process the data
    if (isset($_POST['noref_redirects'])) {
        add_action('admin_init', array($redirect_plugin, 'save_redirects'));
    }
}

// this is here for php4 compatibility
if (!function_exists('str_ireplace')) {

    function str_ireplace($search, $replace, $subject) {
        $token = chr(1);
        $haystack = strtolower($subject);
        $needle = strtolower($search);
        while (($pos = strpos($haystack, $needle)) !== FALSE) {
            $subject = substr_replace($subject, $token, $pos, strlen($search));
            $haystack = substr_replace($haystack, $token, $pos, strlen($search));
        }
        $subject = str_replace($token, $replace, $subject);
        return $subject;
    }

}
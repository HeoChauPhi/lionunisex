<?php
/**
 * template for options page
 * @uses HTCC_Admin::settings_page
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

?>

<!-- style="display: flex; flex-wrap: wrap;" -->

<div class="wrap">
    <div class="row mobile_wrap">
        <div class="col s12 m9 x9 options">
            <div class="mobilemonkey-logo"></div>
            <h6 class="options-subtitle">WP-Chatbot is <a href="https://mobilemonkey.com/" target="_blank">powered by
                    MobileMonkey</a>: an Official Facebook Messenger Solutions Provider Partner</h6>
            <h2><?php settings_errors(); ?> </h2>

            <form action="options.php" method="post">
                <div class="toc-tab-box">
					<?php
					settings_fields('htcc_setting_group');
					do_settings_sections('wp-chatbot');
					?>
                </div>
        </div>
		<?php
        if ($this->stepdis == "open"){
		    submit_button('Save Changes');
		}
        ?>

    </div>
    </form>
</div>

<div class="col s12 m3 x3 ht-cc-admin-sidebar">
	<?php include_once 'commons/ht-cc-admin-sidebar.php'; ?>
</div>
</div>


</div>
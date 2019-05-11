<?php
/**
 * check condtions to display messenger or not
 * get app id
 * get page id
 * and add it to script, div
 */

if (!defined('ABSPATH')) exit;

if (!class_exists('HTCC_Test_Chatbot')) :

	class HTCC_Test_Chatbot
	{

		public $sdk_added = 'no';
		public $sdk_load_time = '';

		public $api;

		public function __construct()
		{
			$this->api = new MobileMonkeyApi();
		}

		public function chatbot()
		{

			$htcc_options = ht_cc()->variables->get_option;
			$fb_page_id = esc_attr($htcc_options['fb_page_id']);
			$fb_app_id = esc_attr($htcc_options['fb_app_id']);
			$fb_sdk_lang = esc_attr($htcc_options['fb_sdk_lang']);
			$fb_greeting_dialog_display = esc_attr($htcc_options['greeting_dialog_display']);
			$fb_greeting_dialog_delay = esc_attr($htcc_options['greeting_dialog_delay']);
			$fb_ref = esc_attr($htcc_options['ref']);
			$fb_color = esc_attr($htcc_options['fb_color']);
			$fb_greeting_login = esc_attr($htcc_options['fb_greeting_login']);
			$fb_greeting_logout = esc_attr($htcc_options['fb_greeting_logout']);
			$is_mobile = ht_cc()->device_type->is_mobile;
			$fb_sdk_src = "//connect.facebook.net/$fb_sdk_lang/sdk/xfbml.customerchat.js";
			$xfbml = true;
			if ('true' == HTCC_PRO) {
				$xfbml = false;
			}

			$is_sdk_after_page_load = 'no';
			if (isset($htcc_options['is_sdk_after_page_load'])) {
				$is_sdk_after_page_load = 'yes';
			}

			$sdk_load_time = '';
			$is_sdk_4_seconds = 'no';
			if (isset($htcc_options['is_sdk_4_seconds'])) {
				$is_sdk_4_seconds = 'yes';
				$sdk_load_time = '4';
			}


			if (isset($htcc_options['sdk_load_time'])) {
				$db_sdk_load_time = esc_attr($htcc_options['sdk_load_time']);
				if ('0.1' < $db_sdk_load_time) {
					$sdk_load_time = $db_sdk_load_time;
				}
			}

			?>
            <!-- Add Messenger - wp-chatbot - HoliThemes - https://www.holithemes.com/wp-chatbot -->
            <script>
                window.onload = function () {
                    jQuery(document).ready(function ($) {

                        window.fbAsyncInit = function () {
                            FB.init({
                                appId: '<?php echo $fb_app_id ?>',
                                autoLogAppEvents: false,
                                xfbml: true,
                                version: 'v3.2'
                            });
                            FB.Event.subscribe('send_to_messenger', function(e) {
                                if (e.event === 'opt_in'){
                                    $('.test-bot-button').hide();
                                    $('.testchat').show();
                                    $("#htcc-messenger").remove();
                                    $("#htcc-customerchat").remove();
                                    $(".fb_dialog").remove();
                                    $("body").append("<div id='htcc-messenger' class='htcc-messenger'><div id='htcc-customerchat' class='fb-customerchat' greeting_dialog_delay='1' greeting_dialog_display='show' page_id='<?php echo $fb_page_id ?>' ref='<?php echo $fb_ref ?>'> </div></div>");
                                    FB.XFBML.parse($("#htcc-messenger").ref);
                                    $('.testchat').on('click',function () {
                                        FB.XFBML.parse($("#htcc-messenger").ref);
                                        $('.test-bot-button').show();
                                        $('.testchat').hide();
                                    })
                                }
                            });
                        };
                        (function (d, s, id) {
                            var js, fjs = d.getElementsByTagName(s)[0];
                            if (d.getElementById(id)) {
                                return;
                            }
                            js = d.createElement(s);
                            js.id = id;
                            js.src = '<?php echo $fb_sdk_src ?>';
                            fjs.parentNode.insertBefore(js, fjs);
                        }(document, 'script', 'facebook-jssdk'));


                    });
                }

            </script>
			<?php
			$this->sdk_added = 'yes';
			$this->sdk_load_time = $sdk_load_time;

		}



	}


	$chatbot = new HTCC_Test_Chatbot();

	add_action( 'admin_head', array( $chatbot, 'chatbot' ));


endif; // END class_exists check
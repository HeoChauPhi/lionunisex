<?php
/**
 * Creates top level menu
 * and options page
 *
 * @package htcc
 * @subpackage admin
 * @since 1.0.0
 *
 */

if (!defined('ABSPATH')) exit;

if (!class_exists('HTCC_Admin')) :

	class HTCC_Admin
	{

		private $api;
		private $fb_page_id;
		private $options;
		private $botid;
		private $token;
		private $test;
		private $internal;
		private $stepdis;

		public function __construct()
		{
			$this->api = new MobileMonkeyApi();
			$this->token = $this->api->connectMobileMonkey();
			$this->options = get_option('htcc_options');
			$this->fb_page_id = $this->options['fb_page_id'];
			$this->botid = $this->api->getActivePage()['bot_id'];
			$this->internal = get_option('mobilemonkey_active_page_id');
			$this->stepdis = "close";
		}

		private function getApi()
		{
			return $this->api;
		}

		/**
		 * Adds top level menu -> WP CSS Shapes
		 *
		 * @uses action hook - admin_menu
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function htcc_options_page()
	    {
            $notification = '!';
			add_menu_page(
				'WP-Chatbot Setting page',
				 !$this->fb_page_id||!$this->token&&!$this->internal ? sprintf('WP-Chatbot <span class="awaiting-mod">%s</span>', $notification) : 'WP-Chatbot',
				'manage_options',
				'wp-chatbot',
				array($this, 'settings_page'),
				'dashicons-format-chat'
			);
			if ($this->getApi()->connectMobileMonkey()) {
				add_submenu_page('wp-chatbot', 'Contacts', 'Contacts', 8, 'wp-chatbot-contact', [
					$this, 'page_contact'
				]);
			}


		}
		/**
		 * Incomplete Setup Notification
		 *
		 * @uses action hook - admin_init
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function htcc_incomplete_setup(){

            if (!$this->fb_page_id || !$this->token && !$this->internal){
                add_action( 'admin_bar_menu', function( \WP_Admin_Bar $bar )
                {
                    $bar->add_menu( array(
                        'id'     => 'wp-chatbot',
                        'title'  => '<span class="ab-icon chat-bot"></span>',
                        'parent' => 'top-secondary',
                        'href'   => admin_url( 'admin.php?page=wp-chatbot' ),
                        'meta'   => array(
                            'target'   => '_self',
                            'title'    => __( 'Wp-Chatbot', 'htcc_plugin' ),
                            'html'     => '',
                        ),
                    ) );
                }, 210);
            }
		}
        public function example_admin_notice() {
		    if (!$this->fb_page_id || !$this->token && !$this->internal){
                HT_CC::view('th-cc-admin-notice-not-connected');
		    }
        }
		/**
		 * Options page Content -
		 *   get settings form from a template settings_page.php
		 *
		 * Call back from - $this->htcc_options_page, add_menu_page
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function settings_page()
		{

			if (!current_user_can('manage_options')) {
				return;
			}

			// get options page form
			require_once('settings_page.php');
		}

		function page_contact()
		{
			include('contact_page.php');
			$table = new MobileMonkey_Contacts_List_Table();
			$table->prepare_items();
			$table->display();
		}

		/**
		 * Options page - Regsiter, add section and add setting fields
		 *
		 * @uses action hook - admin_init
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function htcc_custom_settings()
		{
			add_settings_section('htcc_settings_connect', '', array($this, 'htcc_section_connect_render'), 'wp-chatbot');
			add_settings_section('htcc_settings_as', '', array($this, 'htcc_section_as_render'), 'wp-chatbot');

			add_settings_field('htcc_fb_welcome_message', '', array($this, 'htcc_fb_welcome_message_cb'), 'wp-chatbot', 'htcc_settings_as');
			add_settings_field('htcc_fb_as_state', '', array($this, 'htcc_fb_as_state_cb'), 'wp-chatbot', 'htcc_settings_as');
			add_settings_field('htcc_fb_answer1','', array($this, 'htcc_fb_answer1_cb'), 'wp-chatbot', 'htcc_settings_as');
			add_settings_field('htcc_fb_answer2','', array($this, 'htcc_fb_answer2_cb'), 'wp-chatbot', 'htcc_settings_as');
			add_settings_field('htcc_fb_answer3','', array($this, 'htcc_fb_answer3_cb'), 'wp-chatbot', 'htcc_settings_as');
			add_settings_field('htcc_fb_thank_answer','', array($this, 'htcc_fb_thank_answer_cb'), 'wp-chatbot', 'htcc_settings_as');
			add_settings_field('htcc_fb_email_trans','', array($this, 'htcc_fb_email_trans_cb'), 'wp-chatbot', 'htcc_settings_as');
			register_setting('htcc_setting_group', 'htcc_as_options', array($this, 'htcc_as_options_sanitize'));



			add_settings_section('htcc_settings', '', array($this, 'print_additional_settings_section_info'), 'wp-chatbot');
			add_settings_field('htcc_fb_color', __('Color', 'wp-chatbot'), array($this, 'htcc_fb_color_cb'), 'wp-chatbot', 'htcc_settings');
			add_settings_field('htcc_fb_greeting_login', __('Logged in Greeting', 'wp-chatbot'), array($this, 'htcc_fb_greeting_login_cb'), 'wp-chatbot', 'htcc_settings');
			add_settings_field('htcc_fb_greeting_logout', __('Logged out Greeting', 'wp-chatbot'), array($this, 'htcc_fb_greeting_logout_cb'), 'wp-chatbot', 'htcc_settings');
			add_settings_field('htcc_fb_greeting_dialog_display', __('Greeting Dialog Display', 'wp-chatbot'), array($this, 'htcc_fb_greeting_dialog_display_cb'), 'wp-chatbot', 'htcc_settings');
			add_settings_field('htcc_fb_greeting_dialog_delay', __('Greeting Dialog Delay', 'wp-chatbot'), array($this, 'htcc_fb_greeting_dialog_delay_cb'), 'wp-chatbot', 'htcc_settings');
			add_settings_field('htcc_fb_sdk_lang', __('Messenger language', 'wp-chatbot'), array($this, 'htcc_fb_sdk_lang_cb'), 'wp-chatbot', 'htcc_settings');
			add_settings_field('htcc_show_hide', __('Hide Based on post type', 'wp-chatbot'), array($this, 'htcc_show_hide_post_types_cb'), 'wp-chatbot', 'htcc_settings');
			add_settings_field('htcc_list_id_tohide', __('Post, Page Id\'s to Hide', 'wp-chatbot'), array($this, 'htcc_list_id_tohide_cb'), 'wp-chatbot', 'htcc_settings');
			add_settings_field('htcc_list_cat_tohide', __('Categorys to Hide', 'wp-chatbot'), array($this, 'htcc_list_cat_tohide_cb'), 'wp-chatbot', 'htcc_settings');
			add_settings_field('htcc_devices_show_hide', __('Hide Based on Devices', 'wp-chatbot'), array($this, 'htcc_show_hide_devices_cb'), 'wp-chatbot', 'htcc_settings');
			add_settings_field('htcc_shortcode', __('Shortcode name', 'wp-chatbot'), array($this, 'htcc_custom_shortcode_cb'), 'wp-chatbot', 'htcc_settings');
			register_setting('htcc_setting_group', 'htcc_options', array($this, 'htcc_options_sanitize'));
		}


		function htcc_section_connect_render() {
		    ?>
            <h3 class="acc-title <?php if ($this->botid && $this->token){ echo "colapse"; } else {echo "open";}?>"><div class="circle">1</div>Connect to your Facebook account<i class="fa fa-angle-down step_fa"></i></h3>

			 <div class="acc-content" style="display:block">
                    <div class="accordionItemHeading">
                        <?php $this->htcc_fb_connection_button_cb(); ?>
                    </div>
                </div>
            <?php
		}
		function htcc_section_as_render() {
			?>
			<?php
            if ($this->fb_page_id && $this->token && $this->botid){
               $this->stepdis = "open";
                if ($this->api->mmOnlyCheck($this->fb_page_id)){
                        $style = "disabled";
                        $this->test = "none";
                    }else{
                         $style = "";
                         $this->test = "block";
                }
            }
			?>
             <h3 class="acc-title <?php echo $this->stepdis?>"><div class="circle">2</div>Set up your ChatBot<i class="fa fa-angle-down step_fa"></i></h3>
                    <div class="acc-content answering-service <?php echo $style; ?>" style="display:block">
                        <div class="accordionItemHeading">

                            <div class="mm_only" style="display: none">
                                <h6><?php _e('Looks like you\'ve already worked in MobileMonkey. Please use the MobileMonkey app to make additional edits to the \'Welcome message\' and \'Answering Service\'.', 'wp-chatbot') ?></h6>
                                <a target="_blank" rel="noopener noreferrer" href='https://app.mobilemonkey.com/chatbot-editor/<?php echo"$this->internal"?>/home' class="button">Go to MobileMonkey</a>
                            </div>
            <?php
		}
		function print_additional_settings_section_info() {
		    ?>
            </div>
            </div>
            <h3 class="acc-title <?php echo $this->stepdis?>"><div class="circle">3</div>Customize<i class="fa fa-angle-down step_fa"></i></h3>
            <div class="acc-content" style="display:block">
            <div class="accordionItemHeading">
        <?php

		}
		public function htcc_fb_connection_button_cb()
		{
			$options = get_option('htcc_options');
			$options_as = get_option('htcc_as_options');
			$api = $this->getApi();
			$api->logoutMobilemonkey();
			$token = $api->connectMobileMonkey();

			if ($token) {
				$reset = FALSE;
				if ($api->connectPage() || $api->disconnectPage()) {
					$reset = TRUE;
				}

				$pages = $api->getPages();

				$activePage = $api->getActivePage($reset);
				if ($activePage) {
				    if ($activePage['bot_id']){
				         if ( isset($_REQUEST['settings-updated']) && $_REQUEST['settings-updated']){
                            if (!$this->api->mmOnlyCheck($this->fb_page_id)){
                                $test = $api->getWidgets($activePage['remote_id']);
                                if ((float)$test->enabled!== (float)$options_as['fb_as_state']){

                                    if ($options_as['fb_as_state']== null || $options_as['fb_as_state']==0){
                                        $valuse = false;
                                    } else {
                                         $valuse = true;
                                    }
                                    $api->AsStateSave($valuse,$activePage['remote_id']);
                                }
                                if ($test) {
                                    foreach ($test->widgets as $key=>$value){
                                        if ($value->type == "quick_question"){
                                            $key+=1;
                                            if ($options_as['fb_answer'.$key.'']!== $value->config->body){
                                                $dump_value = $value;
                                                $dump_value->config->body = $options_as['fb_answer'.$key.''];
                                                $api->updateWidgets($dump_value);
                                            }
                                        }
                                        if ($value->type == 'text'){
                                            if ($options_as['thank_message']!== $value->config->body) {
                                                $dump_value = $value;
                                                $dump_value->config->body = $options_as['thank_message'];
                                                $api->updateWidgets($dump_value);

                                            }
                                        }
                                        if ($value->type == 'email'){
                                            if ($options_as['email']!== $value->config->recipient) {
                                                $dump_value = $value;
                                                $dump_value->config->recipient = $options_as['email'];
                                                $api->updateWidgets($dump_value);

                                            }
                                        }
                                    }
                                }
                                $current_welcome_message = $api->getWelcomeMessage($activePage['remote_id']);
                                if ($options['fb_welcome_message'] !== $current_welcome_message) {
                                    $api->updateWelcomeMessage($options['fb_welcome_message'], $activePage['remote_id']);
                                }
                            }
                        }
                        $custom_settings = $this->api->getCustomChatSettings($activePage['remote_id']);
                        if (isset($_REQUEST['settings-updated']) && $_REQUEST['settings-updated']){
                                 if ($custom_settings) {
                                     foreach ($custom_settings as $key=>$value){
                                         if ($key != "js_src"){
                                             if ($key == 'hide_mobile' || $key == 'hide_desktop'){
                                                if (!$options['fb_'.$key]){
                                                    $options['fb_'.$key] = false;
                                                }else{
                                                    $options['fb_'.$key] = true;
                                                }
                                             }
                                             if ($options['fb_'.$key]!=$value){
                                                $new_value[$key] = $options['fb_'.$key];

                                             }
                                        }
                                     }
                                     if (!empty($new_value)){
                                       $api->updateCustomChatSettings($new_value,$activePage['remote_id']);
                                     }
                                 }
                            $current_language = $api->getLanguage($activePage['remote_id']);
                            if (!empty($options['fb_sdk_lang']) && $options['fb_sdk_lang'] !== $current_language) {
                                $api->updateLanguage($options['fb_sdk_lang'], $activePage['remote_id']);
                            }
                        }

				    }else {
				        echo "<style>.settings-error{display: none}</style>";
				       $this->api->renderNotice('Your chatbot has been disabled in MobileMonkey. Please reactivate it before making additional edits. Go <a target="_blank" rel="noopener noreferrer" href="https://app.mobilemonkey.com/chatbot-editor/">here</a> to reactivate your chatbot');
				    }


                    $fb_connected_area_active_page_settings = [
                        'connected_page' => $activePage,
                        'current_facebook_page_block' => '',
                        'logout_path' => add_query_arg([
                            'page' => HTCC_PLUGIN_MAIN_MENU,
                            'logout' => true,
                        ], admin_url('admin.php')),
                    ];
                    HT_CC::view('ht-cc-admin-fb-button-connected', $fb_connected_area_active_page_settings);

				} else {
				    if ($this->internal){
				        echo "<style>.settings-error{display: none}</style>";
				        $this->api->renderNotice('Your Facebook page has been disconnected in MobileMonkey. Please connect to a page to reactivate your chatbot.');
				    }
					$fb_connected_area_pages_settings = [
						'pages' => $pages,
						'logout_path' => add_query_arg([
							'page' => HTCC_PLUGIN_MAIN_MENU,
							'logout' => true,
						], admin_url('admin.php')),
					];
					HT_CC::view('ht-cc-admin-fb-button-select-page', $fb_connected_area_pages_settings);
				}

			} else {

				HT_CC::view('ht-cc-admin-fb-button-not-connected', [
					'options' => $options,
					'path' => $this->getApi()->connectLink(),
				]);
			}

		}


		// color - next new version added ..
		public function htcc_fb_color_cb_old()
		{
			$htcc_fb_color = get_option('htcc_options');
			?>

            <div class="row">
                <div class="input-field col s12">

                    <!-- <input name="htcc_options[fb_color]" data-default-color="#26a69a" value="<?php echo esc_attr($htcc_fb_color['fb_color']) ?>" type="text" class="htcc-color-wp" style="height: 1.375rem;" > -->

                    <input id="htcc-color-wp" class="htcc-color-wp" name="htcc_options[fb_color]"
                           value="<?php echo esc_attr($htcc_fb_color['fb_color']) ?>" type="text"
                           style="height: 1.375rem;">
                    <p class="description"><?php _e('messenger theme color, leave empty for default color - ', 'wp-chatbot') ?>
                        <a target="_blank"
                           href="https://mobilemonkey.com/wp-chatbot/messenger-theme-color/"><?php _e('more info', 'wp-chatbot') ?></a>
                    </p>
                </div>
            </div>
			<?php
		}

		// color
		public function htcc_fb_color_cb()
		{

			$htcc_fb_color = get_option('htcc_options');
			?>
            <div class="row">
                <div class="input-field col s12">
                    <!-- <input name="htcc_options[fb_color]" value="<?php echo esc_attr($htcc_fb_color['fb_color']) ?>" type="color" class="htcc-color-wp" style="width: 5rem; height: 1.5rem;" > -->
                    <input name="htcc_options[fb_color]" value="<?php echo esc_attr($htcc_fb_color['fb_color']) ?>"
                           type="text" class="htcc-color-wp" style="height: 1.375rem;">
                    <p class="description"><?php _e('messenger theme color, leave empty for default color - ', 'wp-chatbot') ?>
                        <a target="_blank"
                           href="https://mobilemonkey.com/wp-chatbot/messenger-theme-color/"><?php _e('more info', 'wp-chatbot') ?></a>
                    </p>
                    <!-- <p class="description"><?php _e('please open settings page in the browser that supports "type color", we are planning to make a better way to choose the color ', 'wp-chatbot') ?></p> -->
                </div>
            </div>
			<?php
		}
		public function htcc_fb_as_state_cb()
		{
			$htcc_fb_as_state = get_option('htcc_as_options');
			?>
            <div class="row">
                <div class="input-field as_state col s12">
                    <label class="switch">
                    <input id="htcc_fb_as_state" name="htcc_as_options[fb_as_state]" type="checkbox" value="1" <?php isset($htcc_fb_as_state['fb_as_state']) ? checked($htcc_fb_as_state['fb_as_state'], 1) : checked(0); ?>/>
                    <span class="slider round"></span>
                    </label>
                    <p><?php _e('Answering Service is on', 'wp-chatbot') ?></p>
                </div>
            </div>
			<?php
		}

		// Welcome message
		public function htcc_fb_welcome_message_cb()
		{
			$htcc_fb_welcome_message = get_option('htcc_options');
			$ref = get_option('htcc_fb_ref');
			$htcc_fb_app_id = get_option('mobilemonkey_environment');
			?>
            <div class="row">
            <div class="test-bot-button"  style="display: <?php echo $this->test; ?>">
                <div class="test-bot-button__button-wrapper">
                    <div class="test-bot-button__messenger">
                        <div class="fb-send-to-messenger"
                          messenger_app_id="<?php echo $htcc_fb_app_id->fb_app_id; ?>"
                          page_id="<?php echo $this->fb_page_id; ?>"
                          data-ref="<?php echo $ref;?>"
                          color="blue"
                          size="large">
                        </div>
                    </div>
                </div>
            </div>

                <a target="_blank" rel="noopener noreferrer" style="display: none" href="https://www.m.me/<?php echo $this->fb_page_id?>" id="messanger" class="button testchat">Open Messenger</a>
                <h6><?php _e('Welcome message', 'wp-chatbot') ?></h6>
                <div class="input-field col s12">
                    <label for="fb_greeting_login"><?php _e('WP-Chatbot will greet your chat users with this message.', 'wp-chatbot') ?></label>
                    <textarea rows="5" style="width:78%" name="htcc_options[fb_welcome_message]" id="fb_welcome_message"> <?php echo esc_attr($htcc_fb_welcome_message['fb_welcome_message']) ?></textarea>
                </div>
            </div>
			<?php
		}
		public function htcc_fb_answer1_cb()
		{
			$htcc_fb_answer1 = get_option('htcc_as_options');
			?>
            <div class="row as">
                <div class="input-field col l9 s12">
                    <h6><?php _e('Quick Questions', 'wp-chatbot') ?></h6>
                    <label for="fb_answer1"><?php _e('WP-Chatbot will ask your chat users a few questions.', 'wp-chatbot') ?></label>
                    <input type="text" name="htcc_as_options[fb_answer1]" id="fb_answer1"
                           value="<?php echo esc_attr($htcc_fb_answer1['fb_answer1']) ?>">
                </div>
            </div>
			<?php
		}
		public function htcc_fb_answer2_cb()
		{
			$htcc_fb_answer2 = get_option('htcc_as_options');
			?>
            <div class="row as">
                <div class="input-field col l9 s12">
                    <input type="text" name="htcc_as_options[fb_answer2]" id="fb_answer2"
                           value="<?php echo esc_attr($htcc_fb_answer2['fb_answer2']) ?>">
                </div>
            </div>
			<?php
		}
		public function htcc_fb_answer3_cb()
		{
			$htcc_fb_answer3 = get_option('htcc_as_options');
			?>
            <div class="row as">
                <div class="input-field col l9 s12">
                    <input type="text" name="htcc_as_options[fb_answer3]" id="fb_answer3"
                           value="<?php echo esc_attr($htcc_fb_answer3['fb_answer3']) ?>">
                </div>
            </div>
			<?php
		}
		public function htcc_fb_thank_answer_cb()
		{
			$htcc_fb_thank_answer = get_option('htcc_as_options');
			?>
            <div class="row as">
                <div class="input-field col l9 s12">
                    <h6><?php _e('Thank you message', 'wp-chatbot') ?></h6>
                    <label for="fb_answer1"><?php _e('Thank your users for answering your questions, and let them know you\'ll get back to them.', 'wp-chatbot') ?></label>
                    <input type="text" name="htcc_as_options[thank_message]" id="thank_message"
                           value="<?php echo esc_attr($htcc_fb_thank_answer['thank_message']) ?>">
                </div>
            </div>
			<?php
		}
		public function htcc_fb_email_trans_cb()
		{
			$htcc_fb_email_trans = get_option('htcc_as_options');
			?>
            <div class="row as">
                <div class="input-field col l9 s12">
                    <h6><?php _e('Email to send transcripts to:', 'wp-chatbot') ?></h6>
                    <label for="htcc_fb_email_trans"><?php _e('When people answer all of the questions below, we can send the answers to an email address of your choice!', 'wp-chatbot') ?></label>
                    <input type="text" name="htcc_as_options[email]" id="email"
                           value="<?php echo esc_attr($htcc_fb_email_trans['email']) ?>">
                </div>
            </div>
            <div class="row mobilego">
                <h6><?php _e('Add More Questions and Customization for free in MobileMonkey!', 'wp-chatbot') ?></h6>
                <a target="_blank" rel="noopener noreferrer" href='https://app.mobilemonkey.com/chatbot-editor/<?php echo $this->botid?>/bot-builder' class="button">Enter MobileMonkey Free Web Edition</a>
            </div>
			<?php
		}

		// Greeting for logged in user
		public function htcc_fb_greeting_login_cb()
		{

			$htcc_fb_greeting_login = get_option('htcc_options');
			?>
            <div class="row">
                <div class="input-field col s12">
                    <input type="text" name="htcc_options[fb_logged_in_greeting]" id="fb_greeting_login"
                           value="<?php echo esc_attr($htcc_fb_greeting_login['fb_logged_in_greeting']) ?>">
                    <label for="fb_greeting_login"><?php _e('Logged in Greetings', 'ht-click') ?></label>
                    <p class="description"><?php _e('Greetings text - If Facebook logged in the current browser, leave empty for default message - ', 'wp-chatbot') ?>
                        <a target="_blank"
                           href="https://mobilemonkey.com/wp-chatbot/change-facebook-messenger-greetings-text/"><?php _e('more info', 'wp-chatbot') ?></a>
                    </p>
                    <!-- <p class="description"><?php _e('Grettings can add in any language, this can be different to the messenger language', 'wp-chatbot') ?></p> -->
                    <!-- <p class="description"><?php _e('If this Greetings fields are blank, default Greetings will load based on Messenger Language', 'wp-chatbot') ?></p> -->
                </div>
            </div>
			<?php
		}

		// Greeting for logged out user
		public function htcc_fb_greeting_logout_cb()
		{

			$htcc_fb_greeting_logout = get_option('htcc_options');
			?>
            <div class="row">
                <div class="input-field col s12">
                    <input type="text" name="htcc_options[fb_logged_out_greeting]" id="fb_greeting_logout"
                           value="<?php echo esc_attr($htcc_fb_greeting_logout['fb_logged_out_greeting']) ?>">
                    <label for="fb_greeting_logout"><?php _e('Logged out Greetings', 'ht-click') ?></label>
                    <p class="description"><?php _e('Greetings text - If Facebook logged out in the current browser, leave empty for default message - ', 'wp-chatbot') ?>
                        <a target="_blank"
                           href="https://mobilemonkey.com/wp-chatbot/change-facebook-messenger-greetings-text/"><?php _e('more info', 'wp-chatbot') ?></a>
                    </p>
                </div>
            </div>
			<?php
		}

		// sdk lang. / messenger lang
		public function htcc_fb_sdk_lang_cb()
		{
            if ($this->fb_page_id && $this->token && $this->botid){
			$lang = $this->api->getLanguage($this->fb_page_id);
			}
			?>
            <div class="row">
                <div class="input-field col s12">
                    <select name="htcc_options[fb_sdk_lang]">
						<?php
						$fb_lang = HTCC_Lang::$fb_lang;

						foreach ($fb_lang as $key => $value) {
							?>
                            <option value="<?php echo $value ?>"<?php if ($lang == $value) echo 'SELECTED'; ?> ><?php echo $value ?></option>
							<?php
						}
						?>
                    </select>
                    <label for=""><?php _e('Language', 'ht-click') ?></label>
                    <p class="description"><?php _e('Language displays in chat window, not user input - ', 'wp-chatbot') ?>
                        <a target="_blank"
                           href="https://mobilemonkey.com/wp-chatbot/messenger-language/"><?php _e('more info', 'wp-chatbot') ?></a>
                    </p>
                    <p class="description"><?php _e('Facebook SDK is not supporting all languages.., please don\'t consider it, as an error ', 'wp-chatbot') ?> </p>
                    <!-- <p class="description"><?php _e('If desired Language is not added - ', 'wp-chatbot') ?><a target="_blank" href="https://www.messenger.com/t/1541811499235090/"><?php _e('please message us', 'wp-chatbot') ?></a> </p> -->
                </div>
            </div>
			<?php
		}

		// greeting_dialog_display - since v2.2
		public function htcc_fb_greeting_dialog_display_cb()
		{
			$greeting_dialog_display = get_option('htcc_options');
			$min_value = esc_attr($greeting_dialog_display['fb_greeting_dialog_display']);
			?>
            <div class="row">
                <div class="input-field col s12">
                    <select name="htcc_options[fb_greeting_dialog_display]" class="select-1">
                        <option value="" <?php echo $min_value == "" ? 'SELECTED' : ''; ?> >Default</option>
                        <option value="show" <?php echo $min_value == "show" ? 'SELECTED' : ''; ?> >Show</option>
                        <option value="fade" <?php echo $min_value == "fade" ? 'SELECTED' : ''; ?> >Fade</option>
                        <option value="hide" <?php echo $min_value == "hide" ? 'SELECTED' : ''; ?> >Hide</option>
                    </select>
                    <label for=""><?php _e('Greeting Dialog Display', 'ht-click') ?></label>
                    <p class="description"><?php _e('Greetings Dialog Display  - ', 'wp-chatbot') ?><a target="_blank"
                                                                                                       href="https://mobilemonkey.com/wp-chatbot/greeting-dialog-display/"><?php _e('more info', 'wp-chatbot') ?></a>
                    </p>
                    <p class="description"><?php _e('Show - The greeting dialog will always be shown when the plugin loads.', 'wp-chatbot') ?></p>
                    <p class="description"><?php _e('Fade - The greeting dialog of the plugin will be shown, then fade away and stay hidden afterwards.', 'wp-chatbot') ?></p>
                    <p class="description"><?php _e('Hide - The greeting dialog of the plugin will always be hidden until a user clicks on the plugin.', 'wp-chatbot') ?></p>
                </div>
            </div>
			<?php
		}
 		// greeting_dialog_delay - since v2.2
		public function htcc_fb_greeting_dialog_delay_cb()
		{
			$greeting_dialog_delay = get_option('htcc_options');
			$delay_time = esc_attr($greeting_dialog_delay['fb_greeting_dialog_delay']);
			?>
            <div class="row">
                <div class="input-field col s12">
                    <input type="number" min="0" name="htcc_options[fb_greeting_dialog_delay]" id="fb_greeting_dialog_delay"
                           value="<?php echo $delay_time ?>">
                    <label for="fb_greeting_dialog_delay"><?php _e('Greeting Dialog Delay', 'ht-click') ?></label>
                    <p class="description"><?php _e('Sets the number of seconds of delay before the greeting dialog is shown after the plugin is loaded. Leave blank to disable delay - ', 'wp-chatbot') ?>
                        <a target="_blank"
                           href="https://mobilemonkey.com/wp-chatbot/greeting-dialog-delay/"><?php _e('more info', 'wp-chatbot') ?></a>
                    </p>
                </div>
            </div>
			<?php
		}


		// minimized  - deprecated - since v2.2
		// removed since 3.2
		public function htcc_fb_is_minimized_cb()
		{
			$minimized = get_option('htcc_options');
			$min_value = esc_attr($minimized['minimized']);
			?>
            <div class="row">
                <div class="input-field col s12">
                    <div>
                        <select name="htcc_options[minimized]" class="select-1">
                            <option value="" <?php echo $min_value == "" ? 'SELECTED' : ''; ?> >Default</option>
                            <option value="false" <?php echo $min_value == "false" ? 'SELECTED' : ''; ?> >False</option>
                            <option value="true" <?php echo $min_value == "true" ? 'SELECTED' : ''; ?> >True</option>
                        </select> This attribute is now deprecated - <a target="_blank"
                                                                        href="https://mobilemonkey.com/wp-chatbot/minimize-messenger/"><?php _e('more info', 'wp-chatbot') ?></a>
                    </div>
                    <p class="description"><?php _e('Instead, use greeting_dialog_display, greeting_dialog_delay for customization', 'wp-chatbot') ?> </p>
                </div>
            </div>
			<?php
		}


		// checkboxes - Hide based on Type of posts ..
		public function htcc_show_hide_post_types_cb()
		{
			$htcc_checkbox = get_option('htcc_options');

			// Single Posts
			if (isset($htcc_checkbox['hideon_posts'])) {
				?>
                <p>
                    <label>
                        <input name="htcc_options[hideon_posts]" type="checkbox"
                               value="1" <?php checked($htcc_checkbox['hideon_posts'], 1); ?> id="filled-in-box1"/>
                        <span><?php _e('Hide on - Posts', 'wp-chatbot') ?></span>
                    </label>
                </p>
				<?php
			} else {
				?>
                <p>
                    <label>
                        <input name="htcc_options[hideon_posts]" type="checkbox" value="1" id="filled-in-box1"/>
                        <span><?php _e('Hide on - Posts', 'wp-chatbot') ?></span>
                    </label>
                </p>
				<?php
			}


			// Page
			if (isset($htcc_checkbox['hideon_page'])) {
				?>
                <p>
                    <label>
                        <input name="htcc_options[hideon_page]" type="checkbox"
                               value="1" <?php checked($htcc_checkbox['hideon_page'], 1); ?> id="filled-in-box2"/>
                        <span><?php _e('Hide on - Pages', 'wp-chatbot') ?></span>
                    </label>
                </p>
				<?php
			} else {
				?>
                <p>
                    <label>
                        <input name="htcc_options[hideon_page]" type="checkbox" value="1" id="filled-in-box2"/>
                        <span><?php _e('Hide on - Pages', 'wp-chatbot') ?></span>
                    </label>
                </p>
				<?php
			}


			// Home Page
			if (isset($htcc_checkbox['hideon_homepage'])) {
				?>
                <p>
                    <label>
                        <input name="htcc_options[hideon_homepage]" type="checkbox"
                               value="1" <?php checked($htcc_checkbox['hideon_homepage'], 1); ?> id="filled-in-box3"/>
                        <span><?php _e('Hide on - Home Page', 'wp-chatbot') ?></span>
                    </label>
                </p>
				<?php
			} else {
				?>
                <p>
                    <label>
                        <input name="htcc_options[hideon_homepage]" type="checkbox" value="1" id="filled-in-box3"/>
                        <span><?php _e('Hide on - Home Page', 'wp-chatbot') ?></span>
                    </label>
                </p>
				<?php
			}


			/* Front Page
			 A front page is also a home page, but home page is not a front page
			 if front page unchecked - it works on both homepage and fornt page
			 but if home page is unchecked - it works only on home page, not on front page */
			if (isset($htcc_checkbox['hideon_frontpage'])) {
				?>
                <p>
                    <label>
                        <input name="htcc_options[hideon_frontpage]" type="checkbox"
                               value="1" <?php checked($htcc_checkbox['hideon_frontpage'], 1); ?> id="filled-in-box4"/>
                        <span><?php _e('Hide on - Front Page', 'wp-chatbot') ?></span>
                    </label>
                </p>
				<?php
			} else {
				?>
                <p>
                    <label>
                        <input name="htcc_options[hideon_frontpage]" type="checkbox" value="1" id="filled-in-box4"/>
                        <span><?php _e('Hide on - Front Page', 'wp-chatbot') ?></span>
                    </label>
                </p>
				<?php
			}


			// Category
			if (isset($htcc_checkbox['hideon_category'])) {
				?>
                <p>
                    <label>
                        <input name="htcc_options[hideon_category]" type="checkbox"
                               value="1" <?php checked($htcc_checkbox['hideon_category'], 1); ?> id="filled-in-box5"/>
                        <span><?php _e('Hide on - Category', 'wp-chatbot') ?></span>
                    </label>
                </p>
				<?php
			} else {
				?>
                <p>
                    <label>
                        <input name="htcc_options[hideon_category]" type="checkbox" value="1" id="filled-in-box5"/>
                        <span><?php _e('Hide on - Category', 'wp-chatbot') ?></span>
                    </label>
                </p>
				<?php
			}


			// Archive
			if (isset($htcc_checkbox['hideon_archive'])) {
				?>
                <p>
                    <label>
                        <input name="htcc_options[hideon_archive]" type="checkbox"
                               value="1" <?php checked($htcc_checkbox['hideon_archive'], 1); ?> id="filled-in-box6"/>
                        <span><?php _e('Hide on - Archive', 'wp-chatbot') ?></span>
                    </label>
                </p>
				<?php
			} else {
				?>
                <p>
                    <label>
                        <input name="htcc_options[hideon_archive]" type="checkbox" value="1" id="filled-in-box6"/>
                        <span><?php _e('Hide on - Archive', 'wp-chatbot') ?></span>
                    </label>
                </p>
				<?php
			}


			// 404 Page
			if (isset($htcc_checkbox['hideon_404'])) {
				?>
                <p>
                    <label>
                        <input name="htcc_options[hideon_404]" type="checkbox"
                               value="1" <?php checked($htcc_checkbox['hideon_404'], 1); ?> id="filled-in-box7"/>
                        <span><?php _e('Hide on - 404 Page', 'wp-chatbot') ?></span>
                    </label>
                </p>
				<?php
			} else {
				?>
                <p>
                    <label>
                        <input name="htcc_options[hideon_404]" type="checkbox" value="1" id="filled-in-box7"/>
                        <span><?php _e('Hide on - 404 Page', 'wp-chatbot') ?></span>
                    </label>
                </p>
				<?php
			}
			?>
            <p class="description"> <?php _e('check for not to load Messenger - based on type of the page - ', 'wp-chatbot') ?>
                <a target="_blank"
                   href="https://mobilemonkey.com/wp-chatbot/show-hide-messenger-based-on-type-of-the-page/"><?php _e('more info', 'wp-chatbot') ?></a>
            </p>


			<?php
		}


		// ID 's list to hide styles
		function htcc_list_id_tohide_cb()
		{
			$htcc_list_id_tohide = get_option('htcc_options');
			?>
            <div class="row">
                <div class="input-field col s12">
                    <input name="htcc_options[list_hideon_pages]"
                           value="<?php echo esc_attr($htcc_list_id_tohide['list_hideon_pages']) ?>"
                           id="list_hideon_pages htcc_list_id_tohide" type="text">
                    <label for="list_hideon_pages"><?php _e('Post, Page Ids to Hide', 'ht-click') ?></label>
                    <p class="description"> <?php _e('Add Post, Page, Media - ID\'s to hide,', 'wp-chatbot') ?>
                        <br> <?php _e('can add multiple IDs separate with comma ( , )', 'wp-chatbot') ?> - <a
                                target="_blank"
                                href="https://mobilemonkey.com/wp-chatbot/hide-messenger-based-on-post-id/"><?php _e('more info', 'wp-chatbot') ?></a>
                    </p>
                </div>
            </div>
			<?php
		}

		//  Categorys list - to hide
		function htcc_list_cat_tohide_cb()
		{
			$htcc_list_cat_tohide = get_option('htcc_options');
			?>
            <div class="row">
                <div class="input-field col s12">
                    <input name="htcc_options[list_hideon_cat]"
                           value="<?php echo esc_attr($htcc_list_cat_tohide['list_hideon_cat']) ?>"
                           id="list_hideon_cat htcc_list_cat_tohide" type="text">
                    <label for="list_hideon_cat"><?php _e('Categorys to Hide', 'ht-click') ?></label>
                    <p class="description"> <?php _e('Category name\'s to hide,', 'wp-chatbot') ?>
                        <br> <?php _e('can add multiple Categories separate with comma ( , )', 'wp-chatbot') ?> - <a
                                target="_blank"
                                href="https://mobilemonkey.com/wp-chatbot/hide-messenger-based-on-category/"><?php _e('more info', 'wp-chatbot') ?></a>
                    </p>
                </div>
            </div>
			<?php
		}


		// checkboxes - based on Type of device ..
		public function htcc_show_hide_devices_cb()
		{
			$htcc_devices = get_option('htcc_options');

			// Hide on Mobile Devices
			if (isset($htcc_devices['fb_hide_mobile'])) {
				?>
                <p>
                    <label>
                        <input name="htcc_options[fb_hide_mobile]" type="checkbox"
                               value="1" <?php checked($htcc_devices['fb_hide_mobile'], 1); ?> id="fb_hide_mobile"/>
                        <span><?php _e('Hide on - Mobile Devices', 'wp-chatbot') ?></span>
                    </label>
                </p>
				<?php
			} else {
				?>
                <p>
                    <label>
                        <input name="htcc_options[fb_hide_mobile]" type="checkbox" value="1" id="fb_hide_mobile"/>
                        <span><?php _e('Hide on - Mobile Devices', 'wp-chatbot') ?></span>
                    </label>
                </p>
				<?php
			}


			// Hide on Desktop Devices
			if (isset($htcc_devices['fb_hide_desktop'])) {
				?>
                <p>
                    <label>
                        <input name="htcc_options[fb_hide_desktop]" type="checkbox"
                               value="1" <?php checked($htcc_devices['fb_hide_desktop'], 1); ?> id="fb_hide_desktop"/>
                        <span><?php _e('Hide on - Desktops', 'wp-chatbot') ?></span>
                    </label>
                </p>
				<?php
			} else {
				?>
                <p>
                    <label>
                        <input name="htcc_options[fb_hide_desktop]" type="checkbox" value="1" id="fb_hide_desktop"/>
                        <span><?php _e('Hide on - Desktops', 'wp-chatbot') ?></span>
                    </label>
                </p>
				<?php
			}
		}


		//  Custom shortcode
		function htcc_custom_shortcode_cb()
		{
			$htcc_shortcode = get_option('htcc_options');
			?>
            <div class="row">
                <div class="input-field col s12">
                    <input name="htcc_options[shortcode]" value="<?php echo esc_attr($htcc_shortcode['shortcode']) ?>"
                           id="shortcode" type="text" class="validate input-margin">
                    <label for="shortcode"><?php _e('Shortcode name', 'ht-click') ?></label>
					<?php
					// $shorcode_list = '';
					// foreach ($GLOBALS['shortcode_tags'] AS $key => $value) {
					//    $shorcode_list .= $key . ', ';
					//  }
					?>
                    <p class="description"> <?php printf(__('Default value is \'%1$s\', can customize shortcode name', 'wp-chatbot'), 'chatbot') ?>
                        - <a target="_blank"
                             href="https://mobilemonkey.com/wp-chatbot/change-shortcode-name/"><?php _e('more info', 'wp-chatbot') ?></a>
                    </p>
                    <p class="description"> <?php _e('please don\'t add an already existing shortcode name', 'wp-chatbot') ?>
                        <!-- <p class="description"> <?php _e('please dont add this already existing shorcode names', 'wp-chatbot') ?> - </p> -->
                </div>
            </div>
			<?php
		}



		public function htcc_options_sanitize($input)
		{
            $option = get_option('htcc_options');
            $error=false;
            $error_delay_lenght =false;
            $error_delay_value =false;
			if (!current_user_can('manage_options')) {
				wp_die('not allowed to modify - please contact admin ');
			}

			$new_input = array();

			foreach ($input as $key => $value) {
			    if ($key == 'fb_welcome_message' && isset($_REQUEST['action']) && $_REQUEST['action']== 'update' && !$this->api->mmOnlyCheck($this->fb_page_id)){
			        if ($value == '' || ctype_space($value)){
                        $new_input[$key] = $option[$key];
                        $error = true;
                    }else {
			            $new_input[$key] = sanitize_text_field($input[$key]);
                    }
                    }elseif($key == 'fb_greeting_dialog_delay' && $_REQUEST['action']== 'update'){
                         if (strlen($value) > 9){
                            $new_input[$key] = $option[$key];
                            $error_delay_lenght = true;
                        }else {
                             if ($value == '0'){
                                 $error_delay_value = true;
                                 $new_input[$key] = $option[$key];
                             }else {
                                $new_input[$key] = sanitize_text_field($input[$key]);
                            }
                    }
			        }elseif(isset($input[$key])) {
					    $new_input[$key] = sanitize_text_field($input[$key]);
				}
			}
			if ($error){
                $this->api->settingSaveError("welcome_message");
            }
			if ($error_delay_lenght){
                $this->api->settingSaveError("delay_length");
            }
			if ($error_delay_value){
                $this->api->settingSaveError("delay_0");
            }
			return $new_input;
		}
		public function htcc_as_options_sanitize($input)
		{
            $error=false;
            $error_email=false;
            $option = get_option('htcc_as_options');

			if (!current_user_can('manage_options')) {
				wp_die('not allowed to modify - please contact admin ');
			}
            if ($input){
                $new_input = array();

                foreach ($input as $key => $value) {
                        if ($value == '' || ctype_space($value)){
                            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'update'){
                                    $new_input[$key] = $option[$key];
                                    $error = true;
                            }
                        }elseif (isset($input[$key])) {
                            if ($key == 'email' && !is_email($value)){
                                $new_input[$key] = $option[$key];
                                $error_email = true;
                            }else {
                                $new_input[$key] = sanitize_text_field($input[$key]);
                            }
                        }
                }
            }
            if ($error){
                $this->api->settingSaveError("AS");
            }
            if ($error_email){
                $this->api->settingSaveError("email");
            }
			return $new_input;
		}

	}

endif; // END class_exists check

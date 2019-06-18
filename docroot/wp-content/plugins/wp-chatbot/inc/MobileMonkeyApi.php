<?php

class MobileMonkeyApi
{

	private $option_prefix = 'mobilemonkey_';
	private $api_domain = 'https://api.mobilemonkey.com/';
	private $src = 'wordpress';
	private $pages = [];
	private $active_page = false;
	private $promoters = [];
	private $landing_page;
	private $env = true;
	private $pagination;
	private $contacts;
	private $plugin_name = 'wp-chatbot';

	private function getApiDomain()
	{
		return $this->api_domain;
	}

	private function getSrc()
	{
		return $this->src;
	}

	public function getOptionPrefix()
	{
		return $this->option_prefix;
	}

	private function setToken()
	{
		$token = filter_input(INPUT_GET, "auth_token", FILTER_SANITIZE_STRING);
		if ($token) {
			update_option($this->option_prefix . 'token', $token);
			return true;
		}
		return false;
	}

	private function getToken()
	{
		return get_option($this->option_prefix . 'token');
	}

	private function setCompanyId()
	{
		$company_id = filter_input(INPUT_GET, "company_id", FILTER_SANITIZE_STRING);
		if ($company_id) {
			update_option($this->option_prefix . 'company_id', $company_id);
			return true;
		}
		return false;
	}

	public function getCompanyId($connection_page_id)
	{

		if ($connection_page_id) {

			$pages = $this->getPages();
			foreach ($pages as $page) {
				if ($page['id'] && $connection_page_id == $page['remote_id']) {
					return $page['company_id'];
				}
			}
			return get_option($this->option_prefix . 'company_id');
		}
	}

	private function getInternalPageId($connection_page_id)
	{

		if ($connection_page_id) {

			$pages = $this->getPages();
			foreach ($pages as $page) {
				if ($page['id'] && $connection_page_id == $page['remote_id']) {
					return $page['id'];
				}
			}
			return get_option($this->option_prefix . 'fb_internal_page_id');
		}
	}
	private function default_custom_setting(){
		$data = array();
		$data['fb_logged_in_greeting'] = 'Hi! How can we help you?';
		$data['fb_logged_out_greeting'] = 'Hi! We\'re here to answer any questions you may have';
		$data['fb_color'] = '#0084FF';
		return($data);
	}
	private function setActiveBotId($bot_id)
	{
		update_option($this->option_prefix . 'active_bot', $bot_id);
	}

	private function getActiveBotId()
	{
		return get_option($this->option_prefix . 'active_bot');
	}

	private function setActivePageId($page_id)
	{
		update_option($this->option_prefix . 'active_page_id', $page_id);
	}

	private function getActivePageId()
	{
		return get_option($this->option_prefix . 'active_page_id');
	}

	private function setEnvironment($environment)
	{
		update_option($this->option_prefix . 'environment', $environment);
	}

	public function getEnvironment()
	{
		return get_option($this->option_prefix . 'environment');
	}

	public function refreshSettingsPage()
	{
		echo "<script type='text/javascript'>
				var path = location.protocol + '//' + location.host + location.pathname + '?page=wp-chatbot';
		        window.location = path;
		       
		        </script>";
	}

	public function connectLink()
	{
		$current_user = wp_get_current_user();

		if (!empty($current_user->user_email)) {
			$user_email = $current_user->user_email;
		} else {
			$user_email = get_option('admin_email', '');
		}
		return $this->getApiDomain() . 'wordpress/auth?callback="' . add_query_arg(['page' => $this->plugin_name], admin_url('admin.php')) . '"&email=' . $user_email . '&v=' . HTCC_VERSION.'.2';
	}

	public function connectMobileMonkey()
	{
		if ($this->setToken() && $this->setCompanyId()) {

			$this->getEnv();

			$this->sendUserEmail();

			$this->refreshSettingsPage();
		}
		return $this->getToken();
	}

	public function logoutMobilemonkey($reset = false)
	{
		$logout = filter_input(INPUT_GET, "logout", FILTER_SANITIZE_STRING);
		if ($logout || $reset) {
			$this->disconnectPage(get_option($this->option_prefix . 'active_page_id'));
			delete_option($this->option_prefix . 'token');
			delete_option($this->option_prefix . 'company_id');
			delete_option($this->option_prefix . 'active_page_id');
			delete_option($this->option_prefix . 'active_page_remote_id');
			delete_option($this->option_prefix . 'active_bot');
			$this->refreshSettingsPage();
		}
	}

	public function mmOnlyCheck($page_id)
	{
		$args = [
			'timeout' => 10,
			'headers' => [
				'Authorization' => $this->getToken(),
				'Content-Type' => 'application/json; charset=utf-8',
			],
			'method' => 'GET',
		];
		$response = wp_remote_post($this->getApiDomain() . 'api/facebook_pages/' . $this->getInternalPageId($page_id).'?v=' . HTCC_VERSION.'.2', $args);
		$error = $this->ErrorHandler($response,'MM Only State','get');
		if ($error) {
			$connect_response = json_decode($error);
			return $connect_response->wordpress_settings->mm_only_mode;
		}

	}


	public function connectPage()
	{
		$pageId = filter_input(INPUT_GET, "connect", FILTER_SANITIZE_STRING);
		$pageName = filter_input(INPUT_GET, "page_name", FILTER_SANITIZE_STRING);
		if ($pageId && $pageName) {
			$args = [
				'timeout' => 10,
				'body' => json_encode([
					'remote_id' => $pageId,
					'company_id' => $this->getCompanyId($pageId),
					'name' => $pageName,
					'base_url' => get_site_url(),
					'src' => $this->getSrc(),
				]),
				'headers' => [
					'Authorization' => $this->getToken(),
					'Content-Type' => 'application/json; charset=utf-8',
				],
				'method' => 'POST',
			];
			$response = wp_remote_post($this->getApiDomain() . 'api/facebook_pages?v=' . HTCC_VERSION.'.2', $args);
			$content = wp_remote_retrieve_body($response);
			$connect_response = json_decode($content);
			if (json_last_error() !== JSON_ERROR_NONE) {
				$this->renderNotice('API communication error. Unable to connect facebook page.');
			} elseif (!empty($connect_response->success)) {
				if ($connect_response->facebook_page->remote_id) {
					$options = get_option('htcc_options', array());
					$options['fb_page_id'] = $connect_response->facebook_page->remote_id;
					$options['fb_internal_page_id'] = $connect_response->facebook_page->id;
					update_option('htcc_options', $options);
					if ($connect_response->facebook_page->active_bot_id) {
						$custom_settings = $this->getCustomChatSettings($options['fb_page_id']);
						$default_setting = $this->default_custom_setting();
						foreach ($custom_settings as $key=>$value){
							if ($value == '' || !isset($value)){
								$options['fb_'.$key] = $default_setting['fb_'.$key];
							}else {
								$options['fb_'.$key]=$value;
							}
						}
						$ref_cont = $this->getBotRef($connect_response->facebook_page->active_bot_id);
						$ref = stristr($ref_cont->test_link, '=');
						$ref_value = str_replace("=", "", $ref);
						update_option('htcc_fb_ref', $ref_value);
						update_option('htcc_fb_js_src', $custom_settings->js_src);
						if (!$this->mmOnlyCheck($options['fb_page_id'])){
							$test = $this->getWidgets($connect_response->facebook_page->remote_id);
							if ($test) {
								foreach ($test->widgets as $key => $value) {
									if ($value->type == "quick_question") {
										$key += 1;
										$value_new['fb_answer' . $key . ''] = $value->config->body;
									}
									if ($value->type == 'text') {
										$value_new['thank_message'] = $value->config->body;
									}
									if ($value->type == 'email') {
										$value_new['email'] = $value->config->recipient;
									}
								}
							}
							$value_new['fb_as_state'] = 0;
							update_option('htcc_as_options', $value_new);
						}
						if ($connect_response->welcome_message) {
							$options['fb_welcome_message'] = $connect_response->welcome_message->body;
						} else {
							$options['fb_welcome_message'] = '';
						}
						$options['fb_sdk_lang'] = $this->getLanguage($connect_response->facebook_page->remote_id);
						update_option('htcc_options', $options);
						$this->refreshSettingsPage();
						return true;
					}

				}

			} elseif ($connect_response->error_code) {
				$this->renderNotice('Error code: ' . $connect_response->error_code);
				if (!empty($connect_response->errors)) {
					foreach ($connect_response->errors as $error) {
						$this->renderNotice('Error: ' . $error);
					}
				}
			} elseif (!empty($connect_response->errors)) {
				foreach ($connect_response->errors as $error) {
					$this->renderNotice('Error: ' . $error);
				}
			} else {
				$this->renderNotice('API communication error. Unable to connect facebook page.');
			}
		}
		return false;
	}


	public function AsStateSave($state, $fb_page_id)
	{
		$args = [
			'timeout' => 10,
			'headers' => [
				'Authorization' => $this->getToken(),
				'Content-Type' => 'application/json',
			],
			'body' => json_encode([
				'enabled' => $state,
				'fb_page_remote_id' => $fb_page_id,
				'src' => $this->getSrc()
			]),
			'method' => 'PUT',
		];
		$response = wp_remote_request($this->getApiDomain() . 'api/wordpress_settings/answering_service?v=' . HTCC_VERSION.'.2', $args);
		$this->ErrorHandler($response,'AS State Save','');
	}

	public function disconnectPage($pageId='')
	{
		if (!$pageId){
			$pageId = filter_input(INPUT_GET, "disconnect", FILTER_SANITIZE_STRING);
		}
		if ($pageId) {
			$args = [
				'timeout' => 10,
				'body' => json_encode([
					'src' => $this->getSrc(),
				]),
				'headers' => [
					'Authorization' => $this->getToken(),
					'Content-Type' => 'application/json; charset=utf-8',
				],
				'method' => 'DELETE',
			];
			$response = wp_remote_request($this->getApiDomain() . '/api/facebook_pages/' . $pageId.'?v=' . HTCC_VERSION.'.2', $args);
			$content = wp_remote_retrieve_body($response);
			$connect_response = json_decode($content);
			if (empty($content)) {

				$this->active_page = false;

				$options = get_option('htcc_options', array());
				$options['fb_page_id'] = '';
				$options['fb_welcome_message'] = '';
				update_option('htcc_options', $options);

				delete_option($this->option_prefix . 'active_page_id');
				delete_option($this->option_prefix . 'active_page_remote_id');
				delete_option($this->option_prefix . 'active_bot');

				$this->refreshSettingsPage();

				return true;

			} elseif (isset($response["response"]["code"]) && $response["response"]["code"] == 422) {
				$this->renderNotice('The page is not connected!');
			} elseif ($connect_response->error_code) {
				$this->renderNotice('Error code: ' . $connect_response->error_code);
				if (!empty($connect_response->errors)) {
					foreach ($connect_response->errors as $error) {
						$this->renderNotice('Error: ' . $error);
					}
				}
			} elseif (!empty($connect_response->errors)) {
				foreach ($connect_response->errors as $error) {
					$this->renderNotice('Error: ' . $error);
				}
			} else {
				$this->renderNotice('API communication error. Unable to disconnect facebook page.');
			}
			return false;
		}
	}

	public function getPages()
	{
		$args = [
			'timeout' => 10,
			'headers' => [
				'Authorization' => $this->getToken()
			],
			'body' =>[
				'src' => $this->getSrc()
			]
		];
		$pagesObj = NULL;
		$pages = [];
		$response = wp_remote_get($this->getApiDomain() . '/api/facebook_pages/available_options?src=' . $this->getSrc().'&v=' . HTCC_VERSION.'.2', $args);
		$content = wp_remote_retrieve_body($response);
		if (!empty($content)) {
			$pagesObj = json_decode($content);

			if (empty($pagesObj->errors)) {
				if(is_array($pagesObj->data)) {
					foreach ($pagesObj->data as $page) {
						$p = [
							'name' => $page->name,
							'remote_id' => $page->remote_id,
							'id' => $page->facebook_page_id,
							'bot_id' => $page->bot_id,
							'bot_kind' => $page->bot_kind,
							'company_id' => $page->company_id,
							'path' => add_query_arg([
								'page' => $this->plugin_name,
								'connect' => $page->remote_id,
								'page_name' => $page->name
							], admin_url('admin.php')),
						];

						$pages[] = $p;
					}
				}
			}
		}
		$this->pages = $pages;

		return $pages;
	}

	public function getActivePage($reset = false)
	{

		if (!$reset && !empty($this->active_page)) {
			return $this->active_page;
		}

		$activePage = [];
		$pages = $this->getPages();

		$options = get_option('htcc_options', array());
		$active_remote_page_id = $options['fb_page_id'];

		foreach ($pages as $page) {
			if ($active_remote_page_id == $page['remote_id']) {
				$activePage['remote_id'] = $page['remote_id'];
				$activePage['bot_id'] = $page['bot_id'];
				$activePage['name'] = $page['name'];
				$activePage['id'] = $page['id'];
				if ($page['id']){
				$activePage['path'] = add_query_arg([
					'page' => $this->plugin_name,
					'disconnect' => $page['id'],
				], admin_url('admin.php'));
				}else {
					return false;
					break;
				}
				update_option($this->option_prefix . 'active_page_remote_id', $page['remote_id']);
				$this->setActivePageId($page['id']);
				$this->setActiveBotId($page['bot_id']);
				break;
			}
		}

		$this->active_page = $activePage;
		return $activePage;
	}

	public function sendUserEmail()
	{
		if (function_exists('wp_get_current_user')) {
			$current_user = wp_get_current_user();
		}
		if (!empty($current_user->user_email)) {
			$user_email = $current_user->user_email;
		} else {
			$user_email = get_option('admin_email', '');
		}

		$args = [
			'timeout' => 10,
			'headers' => [
				'Authorization' => $this->getToken(),
				'Content-Type' => 'application/json',
			],
			'body' => json_encode([
				'user' => [
					"wp_email" => $user_email,
				],
				'src' => $this->getSrc(),
			]),
			'method' => 'PUT',
		];

		$response = wp_remote_request($this->getApiDomain() . '/api/user/', $args);
		$this->ErrorHandler($response,'User Email','put');
	}

	public function getWelcomeMessage($remote_id)
	{
		$args = [
			'timeout' => 10,
			'headers' => [
				'Authorization' => $this->getToken(),
				'Content-Type' => 'application/json',
			],
			'body' => [
				'src' => $this->getSrc()
			]
		];

		$response = wp_remote_get($this->getApiDomain() . 'api/wordpress_settings/welcome_message?fb_page_remote_id=' . $remote_id . '&v=' . HTCC_VERSION.'.2', $args);
		$error = ($this->ErrorHandler($response,'Welcome Message','get'));
		if ($error) {
			return str_replace('"', '', $error);
		}
	}

	public function updateWelcomeMessage($new_welcome_message, $fb_page_id)
	{
		$args = [
			'timeout' => 10,
			'headers' => [
				'Authorization' => $this->getToken(),
				'Content-Type' => 'application/json',
			],
			'body' => json_encode([
				'body' => $new_welcome_message,
				'fb_page_remote_id' => $fb_page_id,
				'src' => $this->getSrc()
			]),
			'method' => 'PUT',
		];
		$response = wp_remote_request($this->getApiDomain() . 'api/wordpress_settings/welcome_message?v=' . HTCC_VERSION.'.2', $args);
		$this->ErrorHandler($response,'Welcome Message','put');


	}

	public function updateLanguage($new_language, $fb_page_id)
	{
		$args = [
			'timeout' => 10,
			'headers' => [
				'Authorization' => $this->getToken(),
				'Content-Type' => 'application/json',
			],
			'body' => json_encode([
				'language' => $new_language,
				'fb_page_remote_id' => $fb_page_id,
				'src' => $this->getSrc()
			]),
			'method' => 'PUT',
		];
		$fb_lang = HTCC_Lang::$fb_lang;
		if (in_array($new_language, $fb_lang)){
			$response = wp_remote_request($this->getApiDomain() . 'api/wordpress_settings/language?v=' . HTCC_VERSION.'.2', $args);
		}else {
			$this->renderNotice('Incorrect Language Value');
		}
		$this->ErrorHandler($response,'Language','put');
	}

	public function getLanguage($remote_id)
	{
		$args = [
			'timeout' => 10,
			'headers' => [
				'Authorization' => $this->getToken(),
				'Content-Type' => 'application/json',
			],
			'body' => [
				'src' => $this->getSrc()
			]
		];

		$response = wp_remote_get($this->getApiDomain() . 'api/wordpress_settings/language?fb_page_remote_id=' . $remote_id . '&v=' . HTCC_VERSION.'.2', $args);
		$error = ($this->ErrorHandler($response,'Language','get'));
		if ($error) {
			return str_replace('"', '', $error);
		}
	}

	public function getWidgets($remote_id)
	{
		$args = [
			'timeout' => 10,
			'headers' => [
				'Authorization' => $this->getToken(),
				'Content-Type' => 'application/json',
			],
			'body' => [
				'src' => $this->getSrc()
			]
		];

		$response = wp_remote_get($this->getApiDomain() . 'api/wordpress_settings/answering_service?fb_page_remote_id=' . $remote_id . '&v=' . HTCC_VERSION.'.2', $args);
		$error = $this->ErrorHandler($response,'Widget','get');
		if ($error) {
			$connect_response = json_decode($error);
			return $connect_response;
		} else {
			return false;
		}
	}

	public function updateWidgets($object)
	{
		$argsas = json_decode(json_encode($object->config), true);
		$args = [
			'timeout' => 10,
			'headers' => [
				'Authorization' => $this->getToken(),
				'Content-Type' => 'application/json',
			],
			'body' => json_encode(['config' => $argsas, 'src' => $this->getSrc()]),
			'method' => 'PATCH',
		];
		$response = wp_remote_request($this->getApiDomain() . 'api/widgets/' . $object->id . '', $args);
		$error = ($this->ErrorHandler($response,'Widget','put'));
		if ($error) {
			return $response;
		}
	}

	public function getBotRef($bot_id)
	{
		$args = [
			'timeout' => 10,
			'headers' => [
				'Authorization' => $this->getToken(),
				'Content-Type' => 'application/json; charset=utf-8',
			],
			'body' => [
				'src' => $this->getSrc()
			],
			'method' => 'GET',
		];
		$response = wp_remote_post($this->getApiDomain() . 'api/bots/' . $bot_id, $args);
		$error = ($this->ErrorHandler($response,'Bot Ref','get'));
		if ($error) {
			$connect_response = json_decode($error);
			return $connect_response;
		}
	}

	public function getEnv()
	{

		$args = [
			'timeout' => 10,
			'headers' => [
				'Authorization' => $this->getToken(),
			],
		];
		$response = wp_remote_get($this->getApiDomain() . '/api/env/', $args);
		$content = wp_remote_retrieve_body($response);
		if (!empty($content)) {

			$env = json_decode($content);

			if (json_last_error() !== JSON_ERROR_NONE) {
				$this->renderNotice('API communication error. Please try again later.');
			} elseif (!empty($env->errors)) {
				$this->renderNotice('API communication error. Please try again later.');
			} else {
				$this->env = $env;

				$options = get_option('htcc_options', array());
				$options['fb_app_id'] = $this->env->fb_app_id;
				update_option('htcc_options', $options);

				$this->setEnvironment($env);

				return $env;
			}
		}

		return false;
	}

	public function renderNotice($text)
	{
		$setting_page_args = [
			'text' => $text,
		];
		HT_CC::view('ht-cc-admin-fb-button-notice', $setting_page_args);
	}
	public function renderError($text)
	{
		$setting_page_args = [
			'text' => $text,
		];
		HT_CC::view('ht-cc-admin-fb-button-error', $setting_page_args);
	}

	public function settingSaveError($type)
	{
		$text = "";
		switch ($type){
			case "AS":
				$text = "Changes to the Answering Service fields will not be saved if you leave the fields blank. Please include non-empty field if you wish to make a change.";
				break;
			case "email":
				$text = "Validation failed: Invalid recipient email address(es)";
				break;
			case "welcome_message":
				$text = "Changes to the Welcome Message field will not be saved if you leave the fields blank. Please include non-empty field if you wish to make a change";
				break;
			case "delay_length":
				$text = "Greeting Dialog Delay shouldn't exceed 9 digits.";
				break;
			case "delay_0":
				$text = "Greeting Dialog Delay cannot be lower than 1 second";
				break;
		}
		add_settings_error(
		'htcc_setting_group', // setting name
		'', //
		__("$text", 'wp-chatbot'),
		'error' // type of notify
		);
	}
	public function debug()
	{
		$options = [];
		$options['token'] = get_option($this->option_prefix . 'token');
		$options['company_id'] = get_option($this->option_prefix . 'company_id');
		$options['active_page_id'] = get_option($this->option_prefix . 'active_page_id');
		$options['active_page_remote_id'] = get_option($this->option_prefix . 'active_page_remote_id');
		$options['active_bot'] = get_option($this->option_prefix . 'active_bot');
		$options['environment'] = get_option($this->option_prefix . 'environment');
		$options['htcc_options'] = get_option('htcc_options');
		return var_dump($options);
	}

	private function setContacts($data)
	{
		$this->contacts = $data;
	}

	public function getContacts()
	{
		if (empty($this->contacts)) {
			$this->getData();
		}
		return $this->contacts;
	}

	private function setPagination($data)
	{
		$this->pagination = $data;
	}

	public function getPagination()
	{
		if (empty($this->pagination)) {
			$this->getData();
		}
		return $this->pagination;
	}

	private function getArgs()
	{
		$get = [
			'page' => 1,
			'pre_page' => 25,
			'sort_column' => 'created_at',
			'sort_direction' => 'desc',
		];

		$paged = filter_input(INPUT_GET, "paged", FILTER_SANITIZE_STRING);
		if (!empty($paged)) {
			$get['page'] = $paged;
		}

		$orderby = filter_input(INPUT_GET, "orderby", FILTER_SANITIZE_STRING);
		if (!empty($orderby)) {
			$get['sort_column'] = $orderby;
		}

		$order = filter_input(INPUT_GET, "order", FILTER_SANITIZE_STRING);
		if (!empty($order)) {
			$get['sort_direction'] = $order;
		}

		return $get;

	}


	public function updateCustomChatSettings($new_value, $fb_page_id)
	{
		$args = [
			'timeout' => 10,
			'headers' => [
				'Authorization' => $this->getToken(),
				'Content-Type' => 'application/json',
			],
			'body' => json_encode(
				$new_value
			),
			'method' => 'PUT',
		];
		$response = wp_remote_request($this->getApiDomain() . '/api/wordpress_settings/customer_chat?fb_page_remote_id=' . $fb_page_id . '&v=' . HTCC_VERSION.'.2', $args);
		$error = ($this->ErrorHandler($response,"custom settings",'put'));

	}

	public function getCustomChatSettings($remote_id)
	{
		$args = [
			'timeout' => 10,
			'headers' => [
				'Authorization' => $this->getToken(),
				'Content-Type' => 'application/json',
			],'method' => 'GET',
		];

		$response = wp_remote_get($this->getApiDomain() . 'api/wordpress_settings/customer_chat?fb_page_remote_id=' . $remote_id . '&v=' . HTCC_VERSION.'.2', $args);
		$error = ($this->ErrorHandler($response,"custom settings",'get'));
		if ($error) {
			return json_decode($error);
		}
	}


	private function ErrorHandler($response,$point,$type){
		$content = wp_remote_retrieve_body($response);
		$connect_response = json_decode($content);
		$code = wp_remote_retrieve_response_code( $response );
		$type =  "<style>#setting-error-settings_updated{display: none;}</style>";
		if (isset($code) && $code == 200) {
			$type ='';
			return $content;
		} elseif ($connect_response->error_code) {
			$this->renderError('Error code: ' . $connect_response->error_code);
			if (!empty($connect_response->errors)) {
				foreach ($connect_response->errors as $error) {
					$this->renderError('Error: ' . $error);
				}
			}
		} elseif (!empty($connect_response->errors)) {
			foreach ($connect_response->errors as $error) {
				$this->renderError('Error: ' . $error);
			}
		} else {
			if ($point=="Welcome Message" && $code==422){
				return true;
			}else {
				$this->renderError('API communication error. Unable to '.$type .' ' .$point.'');
			}
		}
		echo $type;
	}

	private function getData()
	{
		$get = $this->getArgs();

		$args = [
			'timeout' => 10,
			'headers' => [
				'Authorization' => $this->getToken(),
			],
		];

		$activePageId = $this->getActivePageId();

		$response = wp_remote_get($this->getApiDomain() . 'api/facebook_pages/' . $activePageId . '/contacts?page=' . $get['page'] . '&per_page=' . $get['pre_page'] . '&sort_column=' . $get['sort_column'] . '&sort_direction=' . $get['sort_direction'] . '&src=' . $this->getSrc(), $args);
		$content = wp_remote_retrieve_body($response);
		if (!empty($content)) {
			$contacts = json_decode($content);
			$this->setContacts($contacts->contacts);
			$this->setPagination($contacts->pagination);
		}
	}
}
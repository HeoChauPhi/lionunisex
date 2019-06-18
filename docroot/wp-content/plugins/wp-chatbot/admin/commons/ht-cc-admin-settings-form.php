<?php
/**
 * sidebar in admin area - plugin settings page.
 *
 * @uses at settings_page.php
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>
<h2><?php settings_errors(); ?> </h2>
<div class="step-wrapper">
	<div class="tab_header">
		<ul class="tabs_wrapper">
			<li class="tab-link current" data-tab="tab-1">
				<span class="tab_number">1</span>
				<span class="tab_header">Setup</span>
			</li>
			<li class="tab-link" data-tab="tab-2">
				<span class="tab_number">2</span>
				<span class="tab_header">Customize</span>
			</li>
			<li class="tab-link" data-tab="tab-3">
				<span class="tab_number">3</span>
				<span class="tab_header">Contacts</span>
				<span class="tab_contacts__count">2323</span>
			</li>
			<li class="tab-link" data-tab="tab-4">
				<span class="tab_number">4</span>
				<span class="tab_header">Advanced</span>
			</li>
		</ul>
		<div class="list_tabs__button">
			<ul class="list_tabs"></ul>
		</div>
		<div class="setup_statement">
			Setup Incomplete
		</div>
	</div>

	<div id="tab-1" class="tab-content current">
		<div class="tab-content__wrapper">
            <h2>Settings</h2>

            <form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				// settings_fields( $option_group )
				settings_fields( 'htcc_as_setting_group' );
				// do_settings_sections( $page )
				do_settings_sections( 'htcc-as-setting-section' );
				?>
				<?php submit_button('Save Changes'); ?>
            </form>
		</div>
	</div>
	<div id="tab-2" class="tab-content customize_section">
		<div class="tab-content__wrapper">
            <form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				// settings_fields( $option_group )
				settings_fields( 'htcc_setting_group' );
				// do_settings_sections( $page )
				do_settings_sections( 'wp-chatbot' );
				?>
				<?php submit_button('Save Changes'); ?>
            </form>
		</div>
	</div>
	<div id="tab-3" class="tab-content">

	</div>
	<div id="tab-4" class="tab-content">

	</div>

</div>

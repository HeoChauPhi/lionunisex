<?php
switch ($data['msg']) {
    case "translations-imported":
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php
                print sprintf('%s. %s: %d.', __('Translations updated', 'polylang-tt'), __('Items', 'polylang-tt'), $data['items']);
                ?>
            </p>
        </div>
        <?php
        break;
    case "translations-import-error":
        ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <?php
                print __('Translations import error.', 'polylang-tt');
                ?>
            </p>
        </div>
        <?php
        break;
}
?>

<h3>
    <?php _e('How it is work?', 'polylang-tt'); ?>
</h3>
<div class="wrap">
    <p>
        <?php _e('"Theme and plugin translation for Polylang (TTfP)" automatically searches all files of WordPress themes and plugins.', 'polylang-tt'); ?>
        <?php _e('It chooses from this file only those files with extensions:', 'polylang-tt'); ?>
    </p>
    <ul>
        <li>php</li>
        <li>inc</li>
        <li>twig</li>
    </ul>
    <p>
        <?php _e('Plugin in searched skins or plugins chooses texts from Polylang functions, such as:', 'polylang-tt'); ?>
    </p>
    <ul>
        <li>pll_e();</li>
        <li>pll__();</li>
    </ul>
    <p>
        <?php _e('This functions are defined by Polylang plugin for printing', 'polylang-tt'); ?><br/>
        <?php _e('Thanks "Theme and plugin translation for Polylang" you can find these strings to translate and add to Polylang register on very simple way.', 'polylang-tt'); ?>
        <br/>
        <?php _e('Then you can translate these texts from the admin dashboard.', 'polylang-tt'); ?><br/>
        <?php _e('The scan result can be seen on the tab with translations:', 'polylang-tt'); ?><br/>
        <?php _e('`Settings -> Languages -> String translation`', 'polylang-tt'); ?><br/>
        <?php _e('or', 'polylang-tt'); ?><br/>
        <?php _e('`Languages -> String translation`', 'polylang-tt'); ?><br/>
    </p>
</div>

<div class="form-wrap">
    <p>
        <?php _e('Export all texts for translate as CSV file', 'polylang-tt'); ?>:
    </p>
    <form id="import_export_strings" method="post"
          action="<?php echo esc_url(add_query_arg('pll_action', 'export_strings')); ?>">
        <?php wp_nonce_field('export_strings', '_wpnonce_export_strings'); ?>
        <input name="export_strings" type="hidden" value="1"/>
        <?php
        submit_button(__('Export data', 'polylang-tt')); // Since WP 3.1
        ?>
    </form>
</div>


<div class="form-wrap">
    <p>
        <?php _e('Import all translated texts', 'polylang-tt'); ?>:
    </p>
    <form id="import_export_strings" method="post" enctype="multipart/form-data"
          action="<?php echo esc_url(add_query_arg('pll_action', 'import_strings')); ?>">
        <?php wp_nonce_field('import_strings', '_wpnonce_import_strings'); ?>
        <input type="hidden" name="action_import_strings" value="1">
        <label for="import_strings">
            <?php _e('Select CSV file:', 'polylang-tt'); ?>:
        </label>
        <input type="file" name="import_strings" id="import_strings" accept=".csv" required>
        <?php
        submit_button(__('Run importer', 'polylang-tt')); // Since WP 3.1
        ?>
    </form>
</div>
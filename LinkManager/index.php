<?php
/*
 * Plugin Name: Link Manager
 * Description: Менеджер ссылок
 * Version:     1.0.0
 */

function my_plugin_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=linkmanager">Настройки</a>';
    array_unshift($links, $settings_link);
    return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'my_plugin_settings_link');

function LM_register_options_page() {
    add_options_page('Link Manager', 'Link Manager', 'manage_options', 'linkmanager', 'LM_options_page');
}

add_action('admin_menu', 'LM_register_options_page');

function on_activation() {}

function on_deactivation() {}

function on_uninstall() {}

register_activation_hook(__FILE__, 'on_activation');
register_deactivation_hook(__FILE__, 'on_deactivation');
register_uninstall_hook(__FILE__, 'on_uninstall');

function LM_options_page()
{
    ?>
    <h3>Link Manager</h3>
    <?php
}

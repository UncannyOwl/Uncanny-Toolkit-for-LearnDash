add_filter("plugin_action_links_".plugin_basename(__FILE__), "duplicate_post_plugin_actionsx", 10, 4);

function duplicate_post_plugin_actionsx( $actions, $plugin_file, $plugin_data, $context ) {
array_unshift($actions, "<a href=\"".menu_page_url('uo-menu-slug', false)."\">".__("Settings")."</a>");
return $actions;
}
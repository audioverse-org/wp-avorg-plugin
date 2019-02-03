<?php
/*
Plugin Name: WP Avorg Plugin
Description: AudioVerse plugin
Author: Nathan Arthur
Version: 1.0
Author URI: http://NathanArthur.com/
Text Domain: wp-avorg-plugin
Domain Path: /languages
*/

namespace Avorg;

if (!\defined('ABSPATH')) exit;

define( "AVORG_BASE_PATH", dirname(__FILE__) );
define( "AVORG_BASE_URL", \plugin_dir_url(__FILE__) );

include_once(AVORG_BASE_PATH . "/vendor/autoload.php");

$factory = new Factory();
$plugin = $factory->get("Plugin");
$adminPanel = $factory->get("AdminPanel");
$contentBits = $factory->get("ContentBits");
$router = $factory->get("Router");

\register_activation_hook(__FILE__, array($plugin, "activate"));

\add_action("admin_menu", array($adminPanel, "register"));
\add_action("init", array($plugin, "init"));
\add_action("add_meta_boxes", array($contentBits, "addIdentifierMetaBox"));
\add_action("save_post", array($contentBits, "saveIdentifierMetaBox"));
\add_action("wp_enqueue_scripts", array($plugin, "enqueueScripts"));

\add_filter("locale", array($router, "setLocale"));
\add_filter("redirect_canonical", array($router, "filterRedirect"));

function avorgLog($message) {
	$line = date('Y-m-d H:i:s') . " : $message" . PHP_EOL;

	file_put_contents(AVORG_BASE_PATH . "/logs/general.log", $line, FILE_APPEND);
}

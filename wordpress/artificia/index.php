<?php
// The theme's only template: pages and posts render through the PHP site's layout and sections (inc/site.php); anything else is its 404.
defined('ABSPATH') || exit;
$post = is_singular(['page', 'post']) ? get_queried_object() : null;
artificia_send($post instanceof WP_Post ? $post : null);

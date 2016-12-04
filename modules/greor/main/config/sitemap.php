<?php defined('SYSPATH') or die('No direct access allowed.');

return array
(
	'directory' => 'upload/sitemap',
	'filename' => 'Sitemap.xml',
	'limit' => 30000,
	'default' => array(
		'changefreq' => 'weekly',
		'priority' => '0.5',
		'separate_file' => FALSE,
	),
	'modules' => array(
// 		'news' => 'Sitemap::news',
// 		'staff' => 'Sitemap::staff',
	),
);
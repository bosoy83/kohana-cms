<?php defined('SYSPATH') or die('No direct script access.');

/**
 * TRUE if image size less or equal $width and $height,
 * else return FALSE
 * 
 * @param uri $src
 * @param int $width
 * @param int $height
 */
function check_img_size($src, $width, $height) {
	$file = realpath(DOCROOT.$src);
	
	if (empty($file)) {
		return NULL;
	}
	
	$info = getimagesize($file);
	
	return ($info[0] <= $width AND $info[1] <= $height);
}

function array_merge_recursive_distinct(array & $array1, array & $array2) {
	$merged = $array1;

	foreach ($array2 as $key => & $value) {
		if (is_array($value) && isset($merged[ $key ]) && is_array($merged[ $key ])) {
			$merged[ $key ] = array_merge_recursive_distinct($merged[ $key ], $value);
		} else {
			$merged[ $key ] = $value;
		}
	}

	return $merged;
}

function view_list_row_class(ORM $orm, array $hided_list) {
	$class = '';
	
	if ($orm->site_id != SITE_ID) {
		$class = 'foreign-element';
		if (in_array($orm->id, $hided_list)) {
			$class .= ' hided-element';
		}
	} else {
		$class = 'self-element';
	}
	
	return $class;
}

function row_exist(ORM $orm, $field_name, $field_value, array $where = array()) {
	$search_orm = clone $orm;
	$search_orm->clear();

	foreach ($where as $_conf) {
		if ($_conf[0] === 'and') {
			$search_orm
				->where($_conf[1], $_conf[2], $_conf[3]);
		} elseif ($_conf[0] === 'or') {
			$search_orm
				->or_where($_conf[1], $_conf[2], $_conf[3]);
		}
	}

	if ($orm->loaded()) {
		$orm = $search_orm
			->where($field_name, '=', $field_value)
			->and_where('id', '!=', $orm->id)
			->find();
	} else {
		$orm = $search_orm
			->where($field_name, '=', $field_value)
			->find();
	}

	return $orm->loaded();
}

function transliterate_unique($str, ORM $orm, $field_name, $where = array())
{
	$_value = Ku_Text::slug($str);
	$_value = substr($_value, 0, 50);
	while(row_exist($orm, $field_name, $_value, $where)) {
		$_value .= '-'.uniqid();
	}
	return $_value;
}

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */

Route::set('modules', 'admin/modules/<controller>(/<action>(/<id>))(?<query>)')
	->defaults(array(
		'directory' => 'admin/modules',
		'action' => 'index',
	));

Route::set('admin', 'admin(/<controller>(/<action>(/<id>)))(?<query>)')
	->defaults(array(
		'directory' => 'admin',
		'controller' => 'home',
		'action' => 'index',
	));

Route::set('admin_error', 'admin/error/<action>(/<message>)', array('action' => '[0-9]++', 'message' => '.+'))
	->defaults(array(
		'directory' => 'admin',
		'controller' => 'error'
	));

Route::set('sitemap', 'sitemap')
	->defaults(array(
		'controller' => 'sitemap',
		'action'     => 'index',
	));
	
Route::set('widgets', 'widgets/<controller>(/<action>(/<id>))(?<query>)')
	->defaults(array(
		'directory' => 'widgets',
		'action' => 'index',
	));
	
Route::set('uploader', 'uploader(?<query>)')
	->defaults(array(
		'controller' => 'uploader',
		'action' => 'index',
	));	
	
Route::set('home', '(?<query>)')
	->defaults(array(
		'directory' => 'modules',
		'controller' => 'home',
		'action' => 'index',
	));
	
Route::set('preview', '_preview/<directory>/<controller>(?<query>)')
	->defaults(array(
		'action' => 'preview',
	));
	
	
	
	


Route::remove_route('sitemap_index');
Route::remove_route('sitemap');

Route::set('sitemap', 'Sitemap.xml(/<action>)(?<query>)')
	->defaults(array(
		'controller' => 'sitemap',
		'action'     => 'index',
	));
	
if (Kohana::$config->load('import.enable')) {
	Route::set('import', 'import/<controller>(/<action>(/<id>))(?<query>)')
		->defaults(array(
			'directory' => 'import',
			'action' => 'index',
		));
}	
	
Route::set('error', 'error/<action>(/<message>)', array('action' => '[0-9]++', 'message' => '.+'))
	->defaults(array(
		'controller' => 'error'
	));

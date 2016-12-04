<?php defined('SYSPATH') or die('No direct script access.');

class Helper_Sitemap {
	
	public static $host;
	public static $directory;
	public static $temp;
	public static $format_output = TRUE;
	public static $sites;
	public static $region_method = 'query';
	
	public static function create()
	{
		$document = new DOMDocument('1.0', Kohana::$charset);
		$root = $document->createElement('urlset');
		$root->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
		$document->appendChild($root);
	
		return array(
			'document' => $document,
			'root' => $root,
		);
	}
	
	
	/**
	 * Add <url> node to <urlset>
	 * @param array $urlset		array('document' => DOMDocument, 'root' => DOMElement)
	 * @param array $item		set of fields: 'loc', 'lastmod', 'changefreq', 'priority'
	 */
	public static function add(array $urlset, array $item)
	{
		if (empty($item['loc'])) {
			return;
		}
	
		$document = $urlset['document'];
		$root = $urlset['root'];
	
		$node_url = $document->createElement('url');
		$root->appendChild($node_url);
	
		$node_loc = $document->createElement('loc', $item['loc']);
		$node_url->appendChild($node_loc);
	
		$node_lastmod = $document->createElement('lastmod', date('Y-m-d', strtotime($item['lastmod'])));
		$node_url->appendChild($node_lastmod);
	
		$node_changefreq = $document->createElement('changefreq', $item['changefreq']);
		$node_url->appendChild($node_changefreq);
	
		$node_priority = $document->createElement('priority', $item['priority']);
		$node_url->appendChild($node_priority);
	}
	
	public static function prepare_directory()
	{
		self::$temp = dirname(self::$directory).DIRECTORY_SEPARATOR.'~'.basename(self::$directory);
		$dir = DOCROOT.self::$temp;
		if (file_exists($dir)) {
			Ku_Dir::make_writable($dir);
			Ku_Dir::remove($dir);
		}
		Ku_Dir::make($dir);
		Ku_Dir::make_writable($dir);
	}
	
	public static function write_to_file(DOMDocument $document, $filename)
	{
		$file = DOCROOT.self::$temp.DIRECTORY_SEPARATOR.$filename;
	
		$document->formatOutput = self::$format_output;
	
		$result = FALSE;
		if (file_put_contents($file, $document->saveXML()) !== FALSE) {
			$result = self::$host.'/'.str_replace(DIRECTORY_SEPARATOR, '/', self::$directory).'/'.$filename;
		}
	
		return $result;
	}

	public static function commit()
	{
		$dir = DOCROOT.self::$directory;
		if (file_exists($dir)) {
			Ku_Dir::make_writable($dir);
			Ku_Dir::remove($dir);
		}
	
		rename(self::$temp, $dir);
	}
	
	public static function set_region($uri, $site_id)
	{
		$result = NULL;
		$code = Arr::get(self::$sites, $site_id);
		if ($code === NULL) {
			return $result;
		}
	
		if ($code === '') {
			$result = self::$host.'/'.$uri;
		} elseif (self::$region_method == 'query') {
			$result = self::$host.'/'.$uri.(strpos($uri, '?') ? '&' : '?').'region='.$code;
		} else {
			$tmp = explode('//', self::$host);
			$result = $tmp[0].'//'.$code.'.'.$tmp[1].'/'.$uri;
		}
	
		return $result;
	}
	
}
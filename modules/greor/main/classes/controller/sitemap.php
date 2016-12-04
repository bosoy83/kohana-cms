<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Sitemap extends Controller {

	protected $site_id_master;
	
	protected $config = 'sitemap';
	protected $default;
	protected $directory;
	protected $modules;
	
	public function before()
	{
		if (Kohana::$environment === Kohana::PRODUCTION AND ! Kohana::$is_cli) {
			throw new HTTP_Exception_404();
		}
		
		set_time_limit(0);
		
		parent::before();
		
		$this->site_id_master = $this->request->site_id_master;
		
		$this->config = Kohana::$config->load($this->config);
		$this->default = $this->config->get('default');
		$this->modules = $this->config->get('modules');
		
		Helper_Sitemap::$region_method = Kohana::$config->load('site.region');
		Helper_Sitemap::$sites = DB::select('id', 'code')
			->from(ORM::factory('site')->table_name())
			->execute()
			->as_array('id', 'code');
		Helper_Sitemap::$host = Kohana::$config->load('site.host');
		Helper_Sitemap::$directory = str_replace('/', DIRECTORY_SEPARATOR, $this->config->get('directory'));
		
		Helper_Sitemap::prepare_directory();
	}
	
	public function action_index()
	{
		$document = new DOMDocument('1.0', Kohana::$charset);
		$root = $document->createElement('sitemapindex');
		$root->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
		$document->appendChild($root);
		
		$urlset_static = Helper_Sitemap::create();
		
		$pages = $this->get_pages();
		foreach ($pages as $_item) {
			
			switch ($_item['type']) {
				case 'static':
					Helper_Sitemap::add($urlset_static, array(
						'loc' => Helper_Sitemap::set_region($_item['uri'], $_item['site_id']), 
						'lastmod' => ($_item['updated'] > $_item['created'] ? $_item['updated'] : $_item['created']), 
						'changefreq' => $_item['sm_changefreq'], 
						'priority' => $_item['sm_priority'],
					));
					break;
				case 'module':
					if ( ! array_key_exists($_item['data'], $this->modules)) {
						continue 2;
					}
					
					$_res = call_user_func(Arr::get($this->modules, $_item['data']), $_item);
					if ( ! empty($_res)) {
						if (is_numeric(key($_res))) {
							foreach ($_res as $_arr) {
								$this->sitemap_add($document, $root, $_arr['link'], $_arr['lastmod']);
							}
						} else {
							$this->sitemap_add($document, $root, $_res['link'], $_res['lastmod']);
						}
					}
					
					break;
				default:
					continue 2;
			}
			
		}
		
		$link = Helper_Sitemap::write_to_file($urlset_static['document'], 'static.xml');
		$this->sitemap_add($document, $root, $link, date('Y-m-d'));
		
		if ( ! Helper_Sitemap::write_to_file($document, $this->config->get('filename'))) {
			echo new Exception('Sitemap.xml saving error');
		}
		
		Helper_Sitemap::commit();
	}

	protected function sitemap_add(DOMDocument $document, DOMElement $root, $loc, $lastmod)
	{
		$node_sitemap = $document->createElement('sitemap');
		$root->appendChild($node_sitemap);
	
		$node_loc = $document->createElement('loc', $loc);
		$node_sitemap->appendChild($node_loc);
	
		$node_lastmod = $document->createElement('lastmod', $lastmod);
		$node_sitemap->appendChild($node_lastmod);
	}
	
	protected function set_default($item)
	{
		if (empty($item['sm_changefreq'])) {
			$item['sm_changefreq'] = $this->default['changefreq'];
		}
		if (empty($item['sm_priority'])) {
			$item['sm_priority'] = $this->default['priority'];
		}
	
		return $item;
	}
	
	private function get_pages()
	{
		$table_name = ORM::factory('page')->table_name();
		$pages_db = DB::select(
				'id', 'parent_id', 'site_id', 'type', 'data', 'uri', 'created', 'updated',
				'sm_changefreq', 'sm_priority'
			)
			->from($table_name)
			->where('delete_bit', '=', 0)
			->where('type', 'NOT IN', array('page', 'url'))
			->order_by('level', 'asc')
			->order_by('parent_id', 'asc')
			->order_by('position', 'asc')
			->execute();
		
		$result = array();
		foreach ($pages_db as $_row) {
			$_item = $_row;
			
			$_parent_id = $_row['parent_id'];
			if (isset($result[$_parent_id])) {
				$_item['uri'] = $result[$_parent_id]['uri'].'/'.$_row['uri'];
			}
			
			$result[$_row['id']] = $this->set_default($_item);
		}
		
		return $result;
	}
	
}
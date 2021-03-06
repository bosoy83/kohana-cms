<?php defined('SYSPATH') or die('No direct script access.');

class View_Admin extends View {

	/**
	 * Returns a new View_Admin object. If you do not define the "file" parameter,
	 * you must call [View::set_filename].
	 *
	 *     $view = View_Admin::factory($file);
	 *
	 * @param   string  view filename
	 * @param   array   array of values
	 * @return  View_Admin
	 */
	public static function factory($file = NULL, array $data = NULL)
	{
		return new View_Admin($file, $data);
	}

	public function set_filename($file)
	{
		$theme = Kohana::$config->load('admin/site.theme');
		if ($theme AND ($path = Kohana::find_file('views', $theme.'/'.$file)) !== FALSE) {
			$this->_file = $path;

			return $this;
		}

		// By default try use common view
		return parent::set_filename($file);
	}

} // End Kohana_View_Admin
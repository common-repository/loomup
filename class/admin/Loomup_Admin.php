<?php

class Loomup_Admin {

  public function __construct()
  {
		add_action('admin_init', array($this, 'loomup_register_settings'));
		add_action('admin_menu', array($this, 'loomup_add_admin_menu'), 8);
	}

	public function loomup_add_admin_menu()
	{
	    add_menu_page(
			'Bienvenue dans Loomup',
			'Loomup',
			'administrator',
			'loomup_videos',
            array($this, 'loomup_videos')
		);
	}

	public function loomup_videos()
	{
		require  plugin_dir_path( __FILE__ ) . 'Loomup_Videos.php';
	}

	public function loomup_register_settings()
	{
		register_setting('loomup_key_config', 'loomup_key');

		wp_register_style( 'style.css', plugin_dir_url( __FILE__ ) . 'assets/css/style.css');
		wp_enqueue_style( 'style.css');
	}

	static public function loomup_isKeyDefined()
	{
		if(get_option('loomup_key')){
			return true;
		}
		return false;
	}
}

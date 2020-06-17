<?php
	/*
	Plugin Name: APPROVE WordPress Development Tools
	Plugin URI: http://kwipped.com
	description:May be used by APPROVE clients to create the necessary link to connect into the Approve cart from wordpress.
	Version: 2.0.0
	Author: Wellington Souza
	Author URI: http://kwipped.com
	License: GPL2
	*/
	require('Approve.php');

	class ApproveWordPressDevelopmentLibraries{
		public static $version = "2.0.0";
		public $approve_id = "";
		public $approve_url = "";
		public $test = false;

		public function __construct(){
			/**
			 * Provides update functionality
			 */
			require 'plugin-update-checker-4.9/plugin-update-checker.php';
			$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
				'https://github.com/KWIPPED/approve-wordpress-development-libraries/',
				__FILE__,
				'approve-wordpress-development-libraries'
			);

			//Get scripts loaded at the right time.
			add_action('wp_enqueue_scripts', [$this,'load_scripts']);

			//Settings such as approve_id and approve_url...
			if(file_exists(__DIR__."/client_settings.php")){
				require(__DIR__."/client_settings.php");
				$this->approve_id = $client_settings['approve_id'];
				$this->approve_url = $client_settings['approve_url'];
				$this->test = isset($client_settings['test']) && $client_settings['test'] ? true : false;
			}

			// //Will use information passed in data dn return approve rates base on that
			add_action("wp_ajax_get_approve_teaser_devtools",[$this,'get_approve_teaser']);
			add_action("wp_ajax_nopriv_get_approve_teaser_devtools",[$this,'get_approve_teaser']);

			//Will retrieve woocart and return approve rates based on that
			add_action("wp_ajax_get_approve_information_devtools", [$this,'get_approve_information']);
			add_action("wp_ajax_nopriv_get_approve_information_devtools",[$this,'get_approve_information']);
		}

		function dd2($item){
			error_log(print_r($item,true));
		}
		
		//***********************************************************************
		//* The following functions will load all needed WORDPRESS settings, etc.
		//***********************************************************************
		public function load_scripts() {
			global $current_version;
			$data =[
				"ajax_url" => admin_url("admin-ajax.php")
			];
			wp_enqueue_script('approve_wordpress_development_libraries', plugin_dir_url(__FILE__) . 
				'approve_wordpress_development_libraries.js', array('jquery'),self::$version);
			wp_localize_script( 'approve_wordpress_development_libraries', 'php_vars', $data );
		}

		public function get_approve_teaser(){
			$value = $_POST['data']['value'];
			$settings = (object)[
				"approve_id"=>$this->approve_id,
				"approve_url"=>$this->approve_url,
				"test"=>$this->test
			];
			$approve = new Approve($settings);
			wp_send_json($approve->get_teaser($value));
			wp_die(); // this is required to terminate immediately and return a proper response
		}

		public function get_approve_information(){
			$items = $_POST['data']['items'];
			$settings = (object)[
				"approve_id"=>$this->approve_id,
				"approve_url"=>$this->approve_url,
				"test"=>$this->test
			];
			$approve = new Approve($settings);
			foreach($items as $item) $approve->add($item['model'],$item['price'],$item['qty'],$item['type']);
			wp_send_json($approve->get_approve_information());
			wp_die(); // this is required to terminate immediately and return a proper response
		}
	}

	//Lift off
	$approve_wordpress_development_libraries = new ApproveWordPressDevelopmentLibraries();
?>

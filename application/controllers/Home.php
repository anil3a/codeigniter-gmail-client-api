<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends Core_Controller {

	public function index()
	{
		$this->load->model("gmail_model");

		$this->setTitle( 'Home Sweet home' );
		$this->setTopBar();
		$this->setLayout();
	}
}

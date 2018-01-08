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

	public function settings()
	{
		$this->load->helper('form');
        $this->load->library('form_validation');

		$options = $this->system->get();

        foreach ( $options as $option )
        {
            $this->form_validation->set_rules( $option['name'], $option['name'], 'trim|required');
        }
        
        if( $this->input->post('submit') )
        {
            if ( $this->form_validation->run() == TRUE)
            {
                $post = $this->input->post();
             
                if ( isset( $post['submit'] ) )
                {
                    unset( $post['submit'] );
                }

                foreach ($post as $name => $value)
                {
                    $this->system->update_option( $name, $value );
                }
                redirect(site_url('settings'));
            }
        }
        		
		$this->setTitle( 'Settings Option' );
		$this->setTopBar( true, array( 'menu' => array( 1 => 'active' ) ) );
		$this->setLayout( 'options', array( 'options' => $options ) );
	}
}

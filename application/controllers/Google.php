<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Google extends Core_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('gmail_model');
    }

	public function index()
	{
		redirect(site_url('google/emails'));
    }
    
    public function emails()
    {
        $this->setTitle( 'My Emails' );
        $this->setTopBar( true, array( 'menu' => array( 1 => 'active' ) ) );
        $this->setLayout( 'google/gemails' );
    }

    public function get_emails()
    {
        if ( $this->gmail_model->isAuthenticated() )
        {
            $client = $this->gmail_model->get_emails();
            return $this->return_result(array('message' => 'Finished.', 'result' => $client), true);
        }
        return $this->return_result(array('message' => 'Gmail Client not authenticated.'), false);
    }

    public function emaillists()
    {
        // DB table to use
        $table = 'emails';
         
        // Table's primary key
        $primaryKey = 'id';
         
        // Array of database columns which should be read and sent back to DataTables.
        // The `db` parameter represents the column name in the database, while the `dt`
        // parameter represents the DataTables column identifier. In this case simple
        // indexes
        $columns = array(
            array( 'db' => 'id', 'dt' => 0 ),
            array( 'db' => 'email',  'dt' => 1 ),
            array( 'db' => 'subject',   'dt' => 2 ),
            array( 'db' => 'messageid',     'dt' => 3 ),
            array( 'db' => 'threadID',     'dt' => 4 ),
            array(
                'db'        => 'created_at',
                'dt'        => 5,
                'formatter' => function( $d, $row ) {
                    return date( 'jS M y', strtotime($d));
                }
            ),
            array(
                'db'        => 'updated_at',
                'dt'        => 6,
                'formatter' => function( $d, $row ) {
                    return ( !empty( $d ) ? date( 'jS M y', strtotime($d)) : '' );
                }
            ),
        );

        $this->load->model("SSP_model");
         
        echo json_encode(
            $this->SSP_model::complex( $_GET, $table, $primaryKey, $columns, null, "reply_of is null" )
        );
    }

    public function settings()
    {
        $data['hasGoogleToken'] = $this->gmail_model->isAuthenticated();
        $data['urlToAuth'] = $this->gmail_model->get_url_auth();

        $this->setTitle( 'Settings for Google mail ' );
        $this->setTopBar( true, array( 'menu' => array( 2 => 'active' ) ) );
        $this->setLayout( 'google/authenticate_gmail', $data );
    }
    
    public function remove_access_token()
    {
        $this->system->delete_option('google_access_token');
        redirect(site_url('google/emails'));

    }

    public function debug_email($messageId)
    {
        if ($this->gmail_model->isAuthenticated()) {
            $client = $this->gmail_model->debugMessage('me', $messageId);
            return $this->return_result(array('message' => 'Finished.', 'result' => $client), true);
        }
        return $this->return_result(array('message' => 'Gmail Client not authenticated.'), false);
    }

    public function oauthcallback()
    {
        $code = $this->input->get('code');

        //log_message( 'error', 'Success Google Code. ' . json_encode( $code ) );

        if (empty($code)) {
            redirect(site_url('google/emails'));
        }

        $token = $this->gmail_model->create_authentication($code);

        //log_message( 'error', 'Success Google Access Token Updated.');
    }
}

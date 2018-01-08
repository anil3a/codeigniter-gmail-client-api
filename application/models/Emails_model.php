<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Emails_model extends Core_Model {

    protected $_table = 'emails';
    protected $_primary = 'id';
    protected $dependencies = array( 'user_id' );

    public function __construct()
    {
        parent::__construct();
    }

    public function tableName()
    {
        return $this->_table;
    }

    public function primaryKey()
    {
        return $this->_primary;
    }

    public function get()
    {
        return $this->findAll()->result_array();
    }

    public function insert_or_update( array $data )
    {
        return $this->save( $data );
    }

}




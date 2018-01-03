<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users_model extends Core_Model {

    protected $_table = 'users';
    protected $_primary = 'id';

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

}




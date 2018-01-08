<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Options_model extends Core_Model {

    protected $_table = 'options';
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

    public function insert_or_update( array $data )
    {
        return $this->save( $data );
    }

    public function get_option( string $name )
    {
        $val = $this->db->select("value")->where( "name", $name )->get( $this->tableName(), 1 )->row();
        
        if ( empty( $val->value ) ) return '';

        return $val->value;
    }

    public function update_option( string $name, string $value )
    {
        $id = $this->primaryKey();

        $db_result = $this->db->select("id,name,value")->where( "name", $name )->get( $this->_table, 1 )->row();

        if ( !empty( $db_result->name ) )
        {
            $this->db->update( $this->tableName(), array( 'value' => $value, 'updated_at' => mdate( "%Y-%m-%d %H:%i:%s", now() ) ), array( $id => $db_result->id ) );
        }
        else 
        {
            $this->db->insert( $this->tableName(), array( 'name' => $name, 'value' => $value ) );
        }

        return $value;
    }

    public function add_option( string $name, string $value )
    {
        $id = $this->primaryKey();

        $db_result = $this->db->select("id,name,value")->where( "name", $name )->get( $this->_table, 1 )->row();

        if ( !empty( $db_result->name ) ) return false;
        
        return $this->db->insert( $this->tableName(), array( $name => $value ) );

    }

    public function delete_option( string $name )
    {
        $id = $this->primaryKey();

        $db_result = $this->db->select("id,name")->where( "name", $name )->get( $this->_table, 1 )->row();

        if ( !empty( $db_result->name ) )
        {
            $this->db->where( $id, $db_result->id )->delete( $this->tableName() );

            $db_result = $this->db->select("id,name")->where( "name", $name )->get( $this->_table, 1 )->row();

            if ( empty( $db_result->name ) ) return true;
            
        }

        return false;
    }

    public function cleanString( string $string ) {
       $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

       return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }

}




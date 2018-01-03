<?php
defined('BASEPATH') OR exit('No direct script access allowed');

abstract class Core_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

	abstract protected function tableName();
    abstract protected function primaryKey();
    
	public function findAll( $order_by = 'id desc', $limit = 50 )
	{
		return $this->db->order_by( $order_by )->get( $this->tableName(), $limit );
	}

	public function getById( $id )
	{
		return $this->db->where( $this->primaryKey(), $id )->get( $this->tableName() );
	}

    public function getByColumn( $keyValue, $limit = 50, $order_by = 'id desc' )
    {
        if ( empty( $keyValue ) ) throw new Exception( "Empty value passed in getByColumn function parameter. Associative Array with Columnn name and value pair required." );

        if ( !is_array( $keyValue ) ) throw new Exception( "Invalid value passed in getByColumn function parameter. Associative Array with Columnn name and value pair required." );

        $fields = $this->db->list_fields( $this->tableName() );

        foreach ( $keyValue as $key => $value )
        {
            if ( !empty( $key ) && !empty( $value ) )
            {
                if( in_array( $key, $fields ) === false ) throw new Exception("Database Error: ".$key ." column doesn't exists in the table ". $this->tableName(), 1);

                $this->db->where( $key, $value );
            }
        }
        return $this->db->order_by( $order_by )->get( $this->tableName(), $limit );

    }

    public function save( $data )
    {
        $id = $this->primaryKey();

        if ( !empty( $data[ $id ] ) )
        {
            $fields = $this->db->list_fields( $this->tableName() );

            if( in_array( "updated_at", $fields ) === true )
            {
                $data['updated_at'] = mdate( "%Y-%m-%d %H:%i:%s", now() );
            }

            return $this->db->update( $this->tableName(), $data, array( $id => $data[ $id ] ) );
        }
        else 
        {
            $this->db->insert( $this->tableName(), $data );
            return $this->db->insert_id();
        }

    }
}


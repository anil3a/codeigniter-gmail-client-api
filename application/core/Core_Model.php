<?php
defined('BASEPATH') OR exit('No direct script access allowed');

abstract class Core_Model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Abstract to define Table Name for the model
     * @return  string table name
     * @author Anil <anilprz3@gmail.com>
     * @version 1.0
     */
	abstract protected function tableName();

    /**
     * Abstract to define Primary key field name of the table
     * @return  string Table primary key field name
     * @author Anil <anilprz3@gmail.com>
     * @version 1.0
     */
    abstract protected function primaryKey();
    
    /**
     * Fetch all data from Table
     * @param   string  $order_by Mysql Order By
     * @param   integer $limit    Mysql Limit
     * @return  object            Mysql Result Object Resource
     * @author Anil <anilprz3@gmail.com>
     * @version 1.0
     */
	public function findAll( $order_by = 'id desc', $limit = 50 )
	{
		return $this->db->order_by( $order_by )->get( $this->tableName(), $limit );
	}

    /**
     * Fetch by primary key
     * @param   int $id  get by primary key id
     * @return  object   Mysql Result Object Resource
     * @author Anil <anilprz3@gmail.com>
     * @version 1.0
     */
	public function getById( int $id )
	{
		return $this->db->where( $this->primaryKey(), $id )->get( $this->tableName() );
	}

    /**
     * Fetch by field name and value with limt and order by from table
     * @param   array   $keyValue Associative Array with key as field name and value as field value
     * @param   integer $limit    Mysql Limit
     * @param   string  $order_by Mysql Order By
     * @return  object            Mysql Result Object Resource
     * @author Anil <anilprz3@gmail.com>
     * @version 1.0
     */
    public function getByColumn( array $keyValue, $limit = 50, $order_by = 'id desc' )
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

    /**
     * Save Datas either with Mysql Insert or Mysql Update depending upon supplier primary key value
     * @param   array $data  Associative Array with key as field name and value as field value
     * @return  int          If Insert, then last inserted id, If update, affected rows numbers.
     * @author Anil <anilprz3@gmail.com>
     * @version 1.0
     */
    public function save( array $data )
    {
        $id = $this->primaryKey();

        if ( !empty( $data[ $id ] ) )
        {
            $fields = $this->db->list_fields( $this->tableName() );

            if( in_array( "updated_at", $fields ) === true )
            {
                $data['updated_at'] = mdate( "%Y-%m-%d %H:%i:%s", now() );
            }

            $result = $this->db->update( $this->tableName(), $data, array( $id => $data[ $id ] ) );
            
            if ( !empty( $result ) )
            {
                return true;
            }
            return false;
        }
        else 
        {
            $this->db->insert( $this->tableName(), $data );
            return $this->db->insert_id();
        }

    }
}


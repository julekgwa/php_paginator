<?php

/**
* 
*/
class Db {
	private $_db;

	public function __construct($dsn,$username,$password,$options) {
		try {
			$this->_db = new PDO($dsn,$username,$password,$options);
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}
	// get row counts
	public function rowCounts($tableName) {
        return $this->_db->query("SELECT * FROM $tableName")->rowCount();
    }

    // get rows left in the database
    public function rowsLeft($tableName, $start, $limit) {
        return $this->_db->query("SELECT * FROM $tableName LIMIT $start, $limit")->rowCount();
    }

    // select data from the database
    public function selecetLimit($tableName, $columns, $start, $limit) {
 
            $select = "SELECT $columns FROM $tableName ORDER BY ID DESC LIMIT ?,?";
            $prepare = $this->_db->prepare($select);
            $prepare->bindParam(1, $start, PDO::PARAM_INT);
            $prepare->bindParam(2, $limit, PDO::PARAM_INT);
            $prepare->execute();
            $resulst = $prepare->fetchAll();
        return $resulst;
    }
}



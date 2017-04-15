<?php

/**
 * User: junius
 * Date: 2017/04/15
 * Time: 3:46 PM
 */
class Paginator
{
    private $_db;
    private $_hide = 'style="display: none !important"';


    /**
     * Paginator constructor.
     * @param $dsn
     * @param $username
     * @param $password
     */
    public function __construct($dsn, $username, $password)
    {
        try {
            $this->_db = new PDO($dsn, $username, $password);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @return mixed
     */
    public function getHide()
    {
        return $this->_hide;
    }

    /**
     * @param mixed $hide
     */
    public function setHide($hide)
    {
        $this->_hide = $hide;
    }

    public function setColumnCaseAttribute($case)
    {
        switch ($case) {
            case 'lower':
                $this->_db->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
                break;
            case 'upper':
                $this->_db->setAttribute(PDO::ATTR_CASE, PDO::CASE_UPPER);
                break;
            case 'natural':
                $this->_db->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
                break;
            default:
                throw new Exception("Unknown column case.");
        }
    }

    public function setErrorModeAttribute($error) {
        switch ($error) {
            case 'silent':
                $this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                break;
            case 'warning':
                $this->_db->setAttribute(PDO::ATTR_CASE, PDO::ERRMODE_WARNING);
                break;
            case 'exception':
                $this->_db->setAttribute(PDO::ATTR_CASE, PDO::ERRMODE_EXCEPTION);
                break;
            default:
                throw new Exception("Unknown error mode.");
        }
    }

}
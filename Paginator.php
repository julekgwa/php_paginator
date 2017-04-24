<?php

/**
 * User: junius
 * Date: 2017/04/15
 * Time: 3:46 PM
 */
class Paginator
{
    private $_db;
    private $_table = null;
    private $_currentItemClass = '';
    private $_itemLimitPerPage;
    private $_rowOffset = 0;

    /**
     * @return int value of itemLimitPerPage
     */
    public function getItemLimitPerPage()
    {
        return $this->_itemLimitPerPage;
    }

    /**
     * @param int $limitItems number of items per page
     */
    public function setItemLimitPerPage($limitItems)
    {
        $this->_itemLimitPerPage = $limitItems;
    }

    /**
     * @return int value of rowOffset
     */
    public function getRowOffset()
    {
        return $this->_rowOffset;
    }

    /**
     * @param int $rowOffset number of row offset
     */
    public function setRowOffset($rowOffset)
    {
        $this->_rowOffset = $rowOffset;
    }


    /**
     * Paginator constructor.
     * @param string $dsn database host and database name
     * @param string $username database username
     * @param string $password user password
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
    public function getTable()
    {
        return $this->_table;
    }

    /**
     * @param mixed $table
     */
    public function setTable($table)
    {
        $this->_table = $table;
    }

    /**
     * @return string
     */
    public function getCurrentItemClass()
    {
        return $this->_currentItemClass;
    }

    /**
     * @param string $currentItemClass
     */
    public function setCurrentItemClass($currentItemClass)
    {
        $this->_currentItemClass = $currentItemClass;
    }

    // get row count
    public function rowCount($table = null)
    {
        if ($this->_table === null && $table === null) {
            throw new Exception("Table was not set");
        } else {
            if ($table !== null) {
                $stmt = $this->_db->prepare("SELECT * FROM $table");
                $stmt->execute();
                return $stmt->rowCount();
            } elseif ($this->_table !== null) {
                $stmt = $this->_db->prepare("SELECT * FROM $this->_table");
                $stmt->execute();
                return $stmt->rowCount();
            }
        }
    }

    public function getRowsLeft($table = null)
    {
        if ($this->getCurrentPage() !== 'index.php') {
            $this->_rowOffset = ($this->_itemLimitPerPage * $this->getPageNumber());
        }
        if ($this->_table === null && $table === null) {
            throw new Exception("Table was not set");
        } else {
            if ($table !== null) {
                $stmt = $this->_db->prepare("SELECT * FROM $table LIMIT $this->getRowOffset(), $this->getLimitItems()");
                $stmt->execute();
                return $stmt->rowCount();
            } elseif ($this->_table !== null) {
                $stmt = $this->_db->prepare("SELECT * FROM $this->_table LIMIT $this->getRowOffset(), $this->getLimitItems()");
                $stmt->execute();
                return $stmt->rowCount();
            }
        }
    }

    public function getPageData($colId, $params = [])
    {
        if ($this->_table === null && !isset($params['table'])) {
            throw new Exception("Table was not set");
        }
        $columns = ($params['columns']) ? $params['columns'] : '*';
        $sort = ($params['sort']) ? $params['sort'] : 'DESC';
        if ($params['table'] !== null) {
            $table = $params['table'];
            $rowsLeft = $this->getRowsLeft($table);
            if ($rowsLeft < $this->_itemLimitPerPage) {
                $this->_itemLimitPerPage = $rowsLeft;
            }
            $select = "SELECT $columns FROM " . $table['table'] . "ORDER BY $colId $sort LIMIT ?,?";
            $prepare = $this->_db->prepare($select);
            $prepare->bindParam(1, $this->getRowOffset(), PDO::PARAM_INT);
            $prepare->bindParam(2, $this->getItemLimitPerPage(), PDO::PARAM_INT);
            $prepare->execute();
            $results = $prepare->fetchAll();
            return $results;
        } elseif ($this->_table !== null) {
            $rowsLeft = $this->getRowsLeft($this->_table);
            if ($rowsLeft < $this->_itemLimitPerPage) {
                $this->_itemLimitPerPage = $rowsLeft;
            }
            $prepare = $this->_db->prepare("SELECT * FROM $this->_table ORDER BY $colId $sort LIMIT $this->getRowOffset(), $this->getLimitItems()");
            $prepare->execute();
            $results = $prepare->fetchAll();
            return $results;
        }
    }

    function prevPages($pageNumber, $numPrevPages)
    {
        $listItems = ''; // to save all list items.
        while ($numPrevPages >= 1) {
            $pageNumber -= 1;
            if ($pageNumber >= 1) {
                $page = $pageNumber . '.php';
                if (file_exists("$page")) {
                    $listItems = '<li><a href="' . $pageNumber . '.php">' . $pageNumber . '</a></li>' . $listItems;
                }
            }
            $numPrevPages -= 1;
        }
        return $listItems;
    }

    function nextPages($pageNumber, $numNextPages)
    {
        $listItems = ''; // to save list items.
        $count = 1;
        while ($count <= $numNextPages) {
            $pageNumber += 1;
            $page = $pageNumber . '.php';
            if (file_exists("$page")) {
                $listItems .= '<li><a href="' . $pageNumber . '.php">' . $pageNumber . '</a></li>';
            }
            $count += 1;
        }
        return $listItems;
    }

    function pagination($pageNumber, $numPrevPages, $numNextPages, $paginationCssClasses = '')
    {

        $prevPagesList = '<ul class="' . $paginationCssClasses . '"">' . $this->prevButton($pageNumber) . $this->prevPages($pageNumber, $numPrevPages);
        $nextPageList = $this->nextPages($pageNumber, $numNextPages) . $this->nextButton($pageNumber) . '</ul>';
        if ($pageNumber == 'index') {
            $listItems = $prevPagesList . $nextPageList;
        }else {
            $listItems = $prevPagesList . '<li class="' . $this->getCurrentItemClass() . '"><a href="">' . $pageNumber . '</a> </li>' . $nextPageList;
        }
        echo $listItems;
    }

    function prevButton($pageNumber)
    {
        $prev = '';
       if ($pageNumber == 1) {
            $prev = '<li><a href="index.php">&laquo; Previous</a></li>';
        } elseif ($pageNumber > 1) {
            $prev = '<li><a href="' . ($pageNumber - 1) . '.php' . '">&laquo; Previous</a></li>';
        }
        return $prev;
    }

    function nextButton($pageNumber)
    {
        if ($pageNumber == 'index') {
            $page = '1.php';
        } else {
            $page = ($pageNumber + 1) . '.php';
        }
        if (file_exists($page)) {
            return '<li><a href="' . ($pageNumber + 1) . '.php">Next &raquo; </a></li>';
        }
        return '';
    }

    function getPageNumber()
    {
        $currentPage = basename($_SERVER['SCRIPT_FILENAME']);
        $pageNumber = rtrim($currentPage, '.php');
        return $pageNumber;
    }

    function getCurrentPage()
    {
        $currentPage = basename($_SERVER['SCRIPT_FILENAME']);
        return $currentPage;
    }

    function createPages()
    {
        $last_page = ($this->rowCount() / $this->getItemLimitPerPage()) - 1;
        if (!is_int($last_page)) {
            $last_page = (int)$last_page + 1;
        }
        for ($counter = 1; $counter <= $last_page; $counter++) {
            $page = $counter . '.php';
            if (!file_exists($page)) {
                copy('index.php', $page);
            }
        }
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

    public function setErrorModeAttribute($error)
    {
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

    public function setOracleNullsAttribute($error)
    {
        switch ($error) {
            case 'null natural':
                $this->_db->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_NATURAL);
                break;
            case 'null empty string':
                $this->_db->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);
                break;
            case 'null to string':
                $this->_db->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_TO_STRING);
                break;
            default:
                throw new Exception("Unknown Oracle null attribute.");
        }
    }

    public function setStringifyFetchesAttribute($bool)
    {
        if (is_bool($bool)) {
            $this->_db->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, $bool);
        } else {
            throw new Exception("Function requires boolean value, unknown type provided.");
        }
    }

    public function setStatementClassAttribute($custom)
    {
        $this->_db->setAttribute(PDO::ATTR_STATEMENT_CLASS, array($custom, array($this->_db)));
    }

    public function setTimeoutAttribute($seconds)
    {
        $this->_db->setAttribute(PDO::ATTR_TIMEOUT, $seconds);
    }

    public function setAutoCommitAttribute($bool)
    {
        $this->_db->setAttribute(PDO::ATTR_AUTOCOMMIT, $bool);
    }

    public function setEmulatePreparesAttribute($bool)
    {
        $this->_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, $bool);
    }
}
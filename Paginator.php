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
    private $_currentPageClass = '';
    private $_itemLimitPerPage;
    private $_rowOffset = 0;
    private $_urlPattern = '/';
    private $_urlParam = false;
    private $_lastPage = null;
    private $_paramName = 'page';

    /**
     * @return string url pattern
     */
    public function getUrlPattern()
    {
        if ($this->_urlParam) {
            return $this->_urlPattern . '?' . $this->_paramName . '=';
        }
        return $this->_urlPattern;
    }

    /**
     * @param string $urlPattern
     */
    public function setUrlPattern($urlPattern)
    {
        $this->_urlPattern = $urlPattern;
    }

    /**
     * @param string $paramName
     */
    public function setParamName($paramName)
    {
        $this->_paramName = $paramName;
    }


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
     * @return bool
     */
    public function isUrlParam()
    {
        return $this->_urlParam;
    }

    /**
     * @param bool $urlParam
     */
    public function setUrlParam($urlParam)
    {
        $this->_urlParam = $urlParam;
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
     * Get the name of the table
     * @return string the name of the table
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     * Set the name of the table
     * @param string $table the name of the table to be used
     */
    public function setTable($table)
    {
        $this->_table = $table;
    }

    /**
     * Get the class to be used on the current item/page
     * @return string the current page class
     */
    public function getCurrentPageClass()
    {
        return $this->_currentPageClass;
    }

    /**
     * Set the class to be used on the current item/page
     * @param string $currentPageClass set the class to be used for the current page
     */
    public function setCurrentPageClass($currentPageClass)
    {
        $this->_currentPageClass = $currentPageClass;
    }

    /**
     * @return mixed
     */
    private function getLastPage()
    {
        if ($this->_lastPage == null) {
            $this->_lastPage = round(($this->getRowCount() / $this->getItemLimitPerPage()) - 1);
        }
        return $this->_lastPage;
    }

    /**
     * Get the number of rows available
     * @param null $table optional table name
     * @return int the number of row count
     * @throws Exception when table is not set or provided
     */
    public function getRowCount($table = null)
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

    /**
     * Get the number of rows left from the database
     * @param null $table optional table name
     * @return int number of rows left
     * @throws Exception when table is not set or provided
     */
    public function getRowsLeft($table = null)
    {
        if ($this->getCurrentPage() !== 'index.php') {
            $this->_rowOffset = ($this->_itemLimitPerPage * $this->getPageNumber());
        }
        if ($this->_table === null && $table === null) {
            throw new Exception("Table was not set");
        } else {
            if ($table !== null) {
                $stmt = $this->_db->prepare("SELECT * FROM $table LIMIT " . $this->getRowOffset() . "," . $this->getItemLimitPerPage());
                $stmt->execute();
                return $stmt->rowCount();
            } elseif ($this->_table !== null) {
                $stmt = $this->_db->prepare("SELECT * FROM $this->_table LIMIT " . $this->getRowOffset() . "," . $this->getItemLimitPerPage());
                $stmt->execute();
                return $stmt->rowCount();
            }
        }
    }

    /**
     * Get data to be used on the current page
     * @param string $colId column id
     * @param array $params optional parameters for ['table' => 'tableName', 'sort' => 'ASC', 'columns' => 'colId, name, etc']
     * @return array columns from database
     * @throws Exception when table is not set or provided
     */
    public function getPageData($colId, $params = [])
    {
        if ($this->_table === null && !isset($params['table'])) {
            throw new Exception("Table was not set");
        }
        $columns = isset($params['columns']) ? $params['columns'] : '*';
        $sort = isset($params['sort']) ? $params['sort'] : 'DESC';
        if (isset($params['table'])) {
            $table = $params['table'];
            $rowsLeft = $this->getRowsLeft($this->_table);
            $limit = $this->_itemLimitPerPage;
            if ($rowsLeft < $this->_itemLimitPerPage) {
                $limit = $rowsLeft;
            }
            $select = "SELECT $columns FROM " . $table . " ORDER BY $colId $sort LIMIT ?,?";
            $prepare = $this->_db->prepare($select);
            $prepare->bindParam(1, $this->_rowOffset, PDO::PARAM_INT);
            $prepare->bindParam(2, $limit, PDO::PARAM_INT);
            $prepare->execute();
            $results = $prepare->fetchAll();
            return $results;
        } elseif ($this->_table !== null) {
            $rowsLeft = $this->getRowsLeft($this->_table);
            $limit = $this->_itemLimitPerPage;
            if ($rowsLeft < $this->_itemLimitPerPage) {
                $limit = $rowsLeft;
            }
            $prepare = $this->_db->prepare("SELECT * FROM $this->_table ORDER BY $colId $sort LIMIT " . $this->getRowOffset() . "," . $limit);
            $prepare->execute();
            $results = $prepare->fetchAll();
            return $results;
        }
    }

    /**
     * Create pages that will appear before the current page
     * @param int $pageNumber the current page number
     * @param int $numPrevPages the number of pages to appear before the current page
     * @param $cssClass class set to the li list
     * @param $attr attribtes for li list
     * @return string list of pagination links
     */
    function prevPages($pageNumber, $numPrevPages, $cssClass, $attr)
    {
        $listItems = ''; // to save all list items.
        while ($numPrevPages >= 1) {
            $pageNumber -= 1;
            if ($pageNumber >= 1) {
                $listItems = $this->createListItem($cssClass, $attr, $pageNumber) . $listItems;
            }
            $numPrevPages -= 1;
        }
        return $listItems;
    }

    function createListItem($cssClass, $attr, $pageNumber)
    {
        $lastPage = $this->getLastPage();
        if ($this->_urlParam && $pageNumber <= $lastPage && $lastPage > 0) {
            return '<li class="' . $cssClass . '" ' . $attr . '><a href="' . $this->getUrlPattern() . $pageNumber . '">' . $pageNumber . '</a></li>';
        } elseif (!$this->_urlParam) {
            $page = $pageNumber . '.php';
            if (file_exists($page)) {
                return '<li class="' . $cssClass . '" ' . $attr . '><a href="' . $this->getUrlPattern() . $pageNumber . '.php">' . $pageNumber . '</a></li>';
            }
        }
    }

    /**
     * Create pages that will appear after the current page
     * @param $pageNumber the current page number
     * @param $numNextPages the number of pages to appear after the current page
     * @param $cssClass class set to the li list
     * @param $attr attribtes for li list
     * @return string list of pagination links
     */
    function nextPages($pageNumber, $numNextPages, $cssClass, $attr)
    {
        $listItems = ''; // to save list items.
        $count = 1;
        while ($count <= $numNextPages) {
            $pageNumber += 1;
            $listItems .= $this->createListItem($cssClass, $attr, $pageNumber);
            $count += 1;
        }
        return $listItems;
    }

    /**
     * Create the pagination links
     * @param $pageNumber the current page number
     * @param $numPrevPages the number of pages to appear before the current page
     * @param $numNextPages  the number of pages to appear after the current page
     * @param array $attributes optional list of list attributes.
     * ['ul-class': => 'space separated list of classes', 'ul-attr': 'id="someId" data-pre="pre"', 'li-class': 'space separated list of classes', 'li-attr': 'id="someid"']
     */
    function pagination($pageNumber, $numPrevPages, $numNextPages, $attributes = [])
    {
        $ulCssClass = isset($attributes['ul-class']) ? $attributes['ul-class'] : '';
        $ulAttr = isset($attributes['ul-attr']) ? $attributes['ul-attr'] : '';
        $liCssClass = isset($attributes['li-class']) ? $attributes['li-class'] : '';
        $liAttr = isset($attributes['li-attr']) ? $attributes['li-attr'] : '';
        $prevPagesList = '<ul class="' . $ulCssClass . '" ' . $ulAttr . '>' . $this->prevButton($pageNumber) . $this->prevPages($pageNumber, $numPrevPages, $liCssClass, $liAttr);
        $nextPageList = $this->nextPages($pageNumber, $numNextPages, $liCssClass, $liAttr) . $this->nextButton($pageNumber) . '</ul>';
        if ($pageNumber == 'index') {
            $listItems = $prevPagesList . $nextPageList;
        } else {
            $listItems = $prevPagesList . '<li class="' . $this->getCurrentPageClass() . '"><a href="">' . $pageNumber . '</a> </li>' . $nextPageList;
        }
        echo $listItems;
    }

    /**
     * Create a link for previous button
     * @param $pageNumber the current page number
     * @return string the previous link item list
     */
    function prevButton($pageNumber)
    {
        $prev = '';
        if ($this->_urlParam && $pageNumber >= 1) {
            return '<li><a href="' . $this->getUrlPattern() . ($pageNumber - 1) . '">&laquo; Previous</a></li>';
        } elseif (!$this->_urlParam) {
            if ($pageNumber == 1) {
                $prev = '<li><a href="index.php">&laquo; Previous</a></li>';
            } elseif ($pageNumber > 1) {
                $prev = '<li><a href="' . $this->getUrlPattern() . ($pageNumber - 1) . '.php' . '">&laquo; Previous</a></li>';
            }
            return $prev;
        }
    }

    /**
     * Create a link for next button
     * @param $pageNumber the current page number
     * @return string the next link item list
     */
    function nextButton($pageNumber)
    {
        if ($this->_urlParam && $pageNumber < $this->getLastPage()) {
            return '<li><a href="' . $this->getUrlPattern() . ($pageNumber + 1) . '">Next &raquo; </a></li>';
        } elseif (!$this->_urlParam) {
            if ($pageNumber == 'index') {
                $page = '1.php';
            } else {
                $page = ($pageNumber + 1) . '.php';
            }
            if (file_exists($page)) {
                return '<li><a href="' . $this->getUrlPattern() . ($pageNumber + 1) . '.php">Next &raquo; </a></li>';
            }
        }
        return '';
    }

    /**
     * Get the current page number
     * @return int the current page number
     */
    function getPageNumber()
    {
        if ($this->_urlParam && isset($_GET[$this->_paramName]) && $_GET[$this->_paramName] != 0) {
            return $_GET[$this->_paramName];
        }
        $currentPage = basename($_SERVER['SCRIPT_FILENAME']);
        $pageNumber = rtrim($currentPage, '.php');
        return $pageNumber;
    }

    /**
     * Get the current page
     * @return string return the current page
     */
    function getCurrentPage()
    {
        if ($this->_urlParam && isset($_GET[$this->_paramName]) && $_GET[$this->_paramName] != 0) {
            return $_GET[$this->_paramName];
        }
        $currentPage = basename($_SERVER['SCRIPT_FILENAME']);
        return $currentPage;
    }

    /**
     * create the required pages
     */
    function createPages()
    {
        if (!$this->_urlParam) {
            $lastPage = $this->getLastPage();
            for ($counter = 1; $counter <= $lastPage; $counter++) {
                $page = $counter . '.php';
                if (!file_exists($page)) {
                    copy('index.php', $page);
                }
            }
        }
    }

    /**
     * Force column names to a specific case
     * @param string $case the case attribute to be set
     * @throws Exception when unknown case was provided
     */
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

    /**
     * Set error reporting
     * @param string $error type of error mode to be set
     * @throws Exception when unknown error mode was provided
     */
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

    /**
     * Conversion of NULL and empty strings
     * @param string $error type of null attribute to be set
     * @throws Exception when unknown type is provided
     */
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

    /**
     * Convert numeric values to strings when fetching
     * @param boolean $bool set boolean value for stringify fetches
     * @throws Exception when non boolean value if provided
     */
    public function setStringifyFetchesAttribute($bool)
    {
        if (is_bool($bool)) {
            $this->_db->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, $bool);
        } else {
            throw new Exception("Function requires boolean value, unknown type provided.");
        }
    }

    /**
     * Set user-supplied statement class derived from PDOStatement
     * @param string $custom the name of the custom class
     */
    public function setStatementClassAttribute($custom)
    {
        $this->_db->setAttribute(PDO::ATTR_STATEMENT_CLASS, array($custom, array($this->_db)));
    }

    /**
     * Specifies the timeout duration in seconds
     * @param int $seconds number of timeout seconds
     */
    public function setTimeoutAttribute($seconds)
    {
        $this->_db->setAttribute(PDO::ATTR_TIMEOUT, $seconds);
    }

    /**
     * Whether to autocommit every single statement
     * @param boolean $bool set db to autcommit
     */
    public function setAutoCommitAttribute($bool)
    {
        $this->_db->setAttribute(PDO::ATTR_AUTOCOMMIT, $bool);
    }

    /**
     * Enables or disables emulation of prepared statements
     * @param boolean $bool
     */
    public function setEmulatePreparesAttribute($bool)
    {
        $this->_db->setAttribute(PDO::ATTR_EMULATE_PREPARES, $bool);
    }
}
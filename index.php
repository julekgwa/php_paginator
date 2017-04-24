<?php

require_once 'Paginator.php';

$Paginator = new Paginator('mysql:host=localhost;dbname=ng_app', 'root', '000000');
$Paginator->setItemLimitPerPage(4);
$Paginator->setTable('comments');
$Paginator->createPages();
$Paginator->setCurrentPageClass('active');
$Paginator->setUrlPattern('/php_paginator/');
$numPrevPage = 4;
$numNextPage = 4;
$paginationCssClass = 'pagination';

?>
<!DOCTYPE html>
<!--[if IE 9]>
<html class="lt-ie10" lang="en"> <![endif]-->
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <!-- If you delete this meta tag World War Z will become a reality -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foundation 5</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <h1>Pagination using Bootstrap</h1>
            <!-- body content here -->
            <!-- display data from database here with $Paginator->getPageData() -->
            <div class="text-center">
                <!-- our pagination  using Bootstrap-->
                <hr>
                <?php
                    $Paginator->pagination($Paginator->getPageNumber(), $numPrevPage, $numNextPage, $paginationCssClass);
                ?>

            </div>
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>
</html>

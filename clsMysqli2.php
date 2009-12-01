<?php

class mysqli2 extends mysqli
{
    function query($sql)
    {
        return parent::query($sql);
    }

    function __construct($host,$user,$pass,$dbname)
    {
        parent::__construct($host,$user,$pass,$dbname);
    }
}

?>

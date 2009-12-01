<?php

class mysqli2 extends mysqli
{
    public function query($sql)
    {
        echo "\n<!-- $sql -->\n";
        return parent::query($sql);
    }

    public function __construct($host,$user,$pass,$dbname)
    {
        die("hello");
        parent::__construct($host,$user,$pass,$dbname);
    }
}

?>

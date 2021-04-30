<?php
    class DBConnection {

        public function connect($host, $user, $password, $dbname) {
            $con = new mysqli($host, $user, $password, $dbname);
            $con->set_charset("utf8");
            
            return $con;
        }
    }

?>
    private function _sp_free_result($query)
    {
        // http://php.net/manual/en/mysqli.quickstart.stored-procedures.php
        do {
            if ($res = $query->conn_id->store_result()) {
                $res->free();
            } else {
                if ($query->conn_id->errno) {
                    echo "Store failed: (" . $query->conn_id->errno . ") " . $query->conn_id->error;
                }
            }
        } while ($query->conn_id->more_results() && $query->conn_id->next_result());
    }

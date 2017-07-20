    private function _row_num_offset($page = 1, $row_num = 20)
    {
        return ($page > 1)? $row_num * ($page - 1): 0;
    }

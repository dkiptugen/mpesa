<?php
class MY_Model extends CI_Model
    {
        public function __construct()
            {
                parent::__construct();
                $query = "set   interactive_timeout = 1";
		        $this->db->query($query);
				$query = "set  wait_timeout=1";
				$this->db->query($query);
            }
    }
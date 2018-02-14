<?php
class MY_Controller extends CI_Controller
	{
	    public $data;
		public function __construct()
			{
				parent::__construct();
				$this->data["msg"]          =   "";
				$this->data["ProjectName"]  =   "Mpesa Platform";
				$this->data["title"]        =   "Mpesa";
			}
		public function usertype($type=2)
			{
				switch($type)
					{
						case 1:
						        $user 	=	"Admin";
						        break;
						case 2: 
								$user 	=	"NormalUser";
								break;
						case 3:
								$user 	=	"SuperUser";
								break;
						default:
								$user 	=	"invalidUser";
					}
				return $user;
			}
        public function is_logged_in()
            {
                if($this->session->userdata("loggedin") === TRUE)
                    {
                        return (bool)TRUE;
                    }
                else
                    {
                        return (bool)FALSE;
                    }
            }
	}
<?php
class Login_model extends MY_Model
	{
		public function __construct()
			{
				parent::__construct();
			}
        public function login()
			{
				
				$dbh	=	$this->db->where("username",$this->input->post("username"))
									 ->or_where("email",$this->input->post("username"))
									 ->get("users");
				if($dbh->num_rows()>0)
					{
						$data	=	$dbh->row();
						if($data->status === 0)
							{
								return (object)array("login" => (bool)FALSE,"data" => NULL,"msg" => "Account is inactive, Contact System Administrator");
							}
						else
							{
								$pass 	=	$this->assist->secu($data->auth_key,$this->input->post("password"));
								$dbh	=	$this->db->where("id",$data->id)
											 		 ->where("password",$pass)
									 		 		 ->get("users");
						        if($dbh->num_rows()>0)
						        	{
										return (object)array("login" => (bool)TRUE,"data" => $data,"msg" => "success");
									}
								else
									{
										return (object)array("login" => (bool)FALSE,"data" => NULL,"msg" => "Invalid Username or Password");
									}
							}
					}
				else
					{
						return (object)array("login" => (bool)FALSE,"data" => NULL,"msg" => "Invalid Username or Password");
					}
			}
		public function changeRequest()
			{
				$dbh	=	$this->db->or_where("email",$this->input->post("username"))
									 ->get("users");
				if($dbh->num_rows()>0)
					{
						$data	=	$this->db->row();
						$id     =	$this->assist->certEncrypt(json_encode((object)array("id" => $data->id,"auth" => $data->auth_key)),"domain.crt");
						$this->db->where("id",$data->id)
						         ->set("pass_status",0)
						         ->update("users");
						return (object)array("data"=>urlencode($id));
					}
				else
					{
						return (object)array("data" => NULL,"msg" => "Email does not exist");
					}
			}
		public function passChange($id,$auth_key,$password)
			{
				$pass 	= 	$this->assist->secu($auth_key,$password);
				$dbh	=	$this->db->where("id",$id)
									 ->where("pass_status",1)
				         			 ->set("password",$pass)
				         			 ->set("pass_status",1)
				          			 ->update("users");
				if($dbh->affected_rows()>0)
					{
						return (bool)TRUE;
					}
				else
					{
						return (bool)FALSE;
					}
			}
	}
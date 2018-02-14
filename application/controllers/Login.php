<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends MY_Controller
	{
	    public $data;
		public function __construct()
			{
				parent::__construct();
                $this->data["msg"]  =   NULL;
                $this->load->model("Login_model","lmode");

			}
		public function login()
			{
                if(parent::is_logged_in())
                    {
                        redirect("home");
                    }
                $this->data["title"] = "Mpesa Login";
			    if($this->input->post())
                    {
                                $details    =   $this->lmode->login();
                                if($details->login)
                                    {

                                        if($details->data->pass_status === 2)
                                            {
                                                $this->changepassword($details->data);
                                            }
                                        else
                                            {
                                                $newdata=(array)$details->data;
                                                $newdata["loggedin"] = TRUE;
                                                $this->session->set_userdata($newdata);
                                                redirect("home");
                                            }
                                    }
                                else
                                    {
                                        $this->data["msg"]  = $details->msg;
                                    }
                    }
                $this->load->view("login/login",$this->data);
			}
        public function forgotPass()
            {
                if(parent::is_logged_in())
                    {
                        redirect("home");
                    }
                $this->data["title"] = "Forgot Password";

                $this->load->view("login/changereq",$this->data);
            }
        public function changePass($data)
            {
                if(parent::is_logged_in())
                    {
                        redirect("home");
                    }
                $this->data["title"] = "Change Password";
                $x=json_decode($this->assist->certDecrypt(urldecode($data)));
                if($this->input->post())
                    {
                        if($this->input->post("pass1") === $this->input->post("pass2"))
                            {
                                $this->lmode->passChange($x->id,$x->auth,$this->input->post("pass1") );

                            }
                        else
                            {
                                $this->data["msg"]  = "Password Missmatch";
                            }
                    }                
                $this->load->view("login/changepass",$this->data);
            }
        public function changepassword($data)
            {
                if(parent::is_logged_in())
                    {
                        redirect("home");
                    }
                $this->data["title"] = "Change Password";
            }
        public function logout()
            {
                try {
                        $t = $this->session->all_userdata();
                        $this->session->unset_userdata($t);
                        $this->session->sess_destroy();
                        redirect('login',"refresh");
                    }
                catch(Exception $e)
                    {
                        echo $e->getMessage();
                    }
            }
	}
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
                if(parent::is_logged_in())
                    {
                        redirect("home");
                    }
			}
		public function login()
			{
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
                $this->data["title"] = "Forgot Password";

                $this->load->view("login/changereq",$this->data);
            }
        public function changePass($data)
            {
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
                $this->data["title"] = "Change Password";
            }
        public function logout()
            {
                var_dump(array_keys($_SESSION));

                $this->session->unset_userdata(array_keys($_SESSION));
                $this->session->sess_destroy();
                redirect('login',"refresh");
            }
	}
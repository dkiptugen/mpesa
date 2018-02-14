<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends MY_Controller
	{
	    public $data;
		public function __construct()
			{
				parent::__construct();

			}
		public function index()
			{

			    $this->data["subtitle"] =   "Dashboard";
			    $this->data["view"]     =   "dashboard";
				$this->load->view("structure",$this->data);
			}
		public function reports()
            {

            }
		public function B2Ctest()
            {                
                $this->mpesa->B2C("SalaryPayment","5000","salary","salaries deployment");
            }
        public function B2Btest()
            {                
               echo $this->mpesa->B2B("BusinessToBusinessTransfer","5000","A0007","salaries");
            }
        public function Register()
        	{
        		echo $this->mpesa->C2B_REGISTER();
        	}
        public function checkout()
            {
                var_dump($this->mpesa->checkout("254713154085",1,"DPM278","payment of dpm"));
            }
		public function encrypt()
            {
                echo $this->mpesa->encryptPassword("This is me");
            }
        public function token()
        	{
        		echo $this->mpesa->generatetoken();
        	}
        public function transactionstatus()
            {
                echo $this->mpesa->transactionstatus("MAI71H3ZPB","AG_20180118_000044aaee99e2e4f9d2",'600771',"shortcode","mpesa payment","px");
            }
	}

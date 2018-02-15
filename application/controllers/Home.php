<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends MY_Controller
	{
	    public $data;
		public function __construct()
			{
				parent::__construct();
                if(!parent::is_logged_in())
                    {
                        redirect("login");
                    }
			}
		public function index()
			{

			    $this->data["subtitle"] =   "Dashboard";
			    $this->data["view"]     =   "dashboard";
				$this->load->view("structure",$this->data);
			}

        public function reports($type)
            {
                $this->data["title"]    =   "Reports";
                if($type === "incoming")
                    {
                        $this->data["subtitle"] =   "Incoming Transactions";
                    }
                elseif($type === "outgoing")
                    {
                        $this->data["subtitle"] =   "Outgoing Transactions";
                    }
                $this->data["view"] =   "reports";
                $this->load->view("structure",$this->data);
            }
        public function b2c()
            {
                $this->data["subtitle"] =   "Business to Customer";
                $this->data["view"]     =   "b2c";
                $this->load->view("structure",$this->data);
            }
        public function b2b()
            {
                $this->data["subtitle"] =   "Business to Business";
                $this->data["view"]     =   "b2b";
                $this->load->view("structure",$this->data);
            }
        public function reversal()
            {
                $this->data["subtitle"] =   "Reversal";
                $this->data["view"]     =   "reversal";
                $this->load->view("structure",$this->data);
            }
        public function transactionstatus()
            {
                $this->data["subtitle"] =   "Transaction Status";
                $this->data["view"]     =   "transactions";
                $this->load->view("structure",$this->data);
            }
        public function test($type)
            {
                switch($type)
                    {
                        case "token":
                                                echo $this->mpesa->generatetoken();
                                                break;
                        case "c2b_reg":
                                                echo $this->mpesa->C2B_REGISTER();
                                                break;
                        case "trans_status":
                                                echo $this->mpesa->transactionstatus("MAI71H3ZPB","AG_20180118_000044aaee99e2e4f9d2",'600771',"shortcode","mpesa payment","px");
                                                break;
                        case "checkout":
                                                var_dump($this->mpesa->checkout("254713154085",1,"DPM278","payment of dpm"));
                                                break;
                        case "balance":
                                                echo $this->mpesa->accountbalance("test");
                                                break;
                        case "b2c":
                                                $this->mpesa->B2C("SalaryPayment","5000","salary","salaries deployment");
                                                break;
                        case "b2b":
                                                echo $this->mpesa->B2B("BusinessToBusinessTransfer","5000","A0007","salaries");
                                                break;
                        case "default":
                                                echo "invalid test";
                                                break;
                    }
            }
	}

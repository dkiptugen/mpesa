<?php
class Transaction_model extends MY_Model
	{
		public function __construct()
			{
				parent::__construct();
			}
		public function precheckout($data)
			{
				try{
						$d 	= 	[
									"merchantrequestid"	=>	$data["MerchantRequestID"],
									"checkoutrequestid"	=>	$data["CheckoutRequestID"],
									"referenceno"		=>	$data["refno"],
									"typeid"			=>	1
							  	];
						$this->db->insert("checkout",$d);
						if($this->db->affected_rows()<1)
							{
								write_file(APPPATH.'logs/mysql.log',"\n".$this->db->_error_message(). "\n","a+");
							}
					}
				catch(Exception $e)
					{
						write_file(APPPATH.'logs/mysql.log',"\n".$this->db->_error_message(). "  ".$e->getMessage(). "\n","a+");
					}
			}
		public function checkout($data)
			{
				try {
					    $d 	=	[
					    			"amount" 				=>	$data["amount"],
					    			"mpesaReceiptNumber"	=> 	$data["mpesaReceiptNumber"],
					    			"transactiondate"		=> 	date("Y-m-d H:i:s",strtotime($data["transactionDate"])),
					    			"phoneno"				=>	$data["phoneNumber"],
					    			"status"				=> 	1

					    		];
						$this->db->update("checkout",$d);
						if($this->db->affected_rows()<1)
							{
								write_file(APPPATH.'logs/mysql.log',"\n".$this->db->_error_message(). "\n","a+");
							}
					}
				catch (Exception $e) 
					{
						write_file(APPPATH.'logs/mysql.log',"\n".$this->db->_error_message(). "  ".$e->getMessage(). "\n","a+");
					}				
			}
		public function b2b($data)
			{

			}
		public function b2c($data)
			{

			}
		public function c2b($data)
			{

			}
		public function reversal($data)
			{

			}
	}
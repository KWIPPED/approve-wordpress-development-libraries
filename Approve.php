<?php
class Approve{
	private $items = [];
	private $current_total=0;
	private $approve_id = null;
	private $approve_url = null;
	private $cacert_file = null;

	/**
	 * Constructor
	 */
	function __construct() {
		$this->approve_id = ApproveWordPressDevelopmentLibraries::$approve_id;
		$this->approve_url = ApproveWordPressDevelopmentLibraries::$approve_url;
		$this->cacert_file= isset(ApproveWordPressDevelopmentLibraries::$test) && ApproveWordPressDevelopmentLibraries::$test ? 
			"/usr/local/etc/openssl/cert.pem" : __DIR__."/cacert.pem";
	}

	/**
	 * Adds equipment to the current instance of Approve.
	 */
	public function add($model,$price,$quantity,$type){
		$tmp = [];
		$tmp["model"]=$model;
		$tmp["quantity"]=$quantity;
		$tmp["type"]=$type;
		//In Approve the quantity is a representation of how many items are in the total.
		$tmp["price"]=$price ? $price : 0 ;
		$this->current_total+=($tmp["price"]*$quantity);
		$this->items[]=(object)$tmp;
	}

	/**
	 * Returns URL, teaser text, and teaser raw.
	 */
	public function get_approve_information(){
		$teaser = "";
		if(function_exists('curl_version')){
			$teaser_raw = $this->get_teaser($this->current_total);
			if(!empty($teaser_raw)){
				$teaser = "Finance for $".$teaser_raw."/mo";
			}
			else{
				$teaser = null;
			}
		}
		else{
			$teaser = "N/A Your server does not suppor CURL requests. Please ask your system administrator to enable it.";
		}

		$data =  [
			"url"=>$this->approve_url."/approve/finance?approveid=".$this->approve_id.(sizeof($this->items)>0 ? "&items=".urlencode(json_encode($this->items)) : null),
			"teaser"=>$teaser,
			"teaser_raw"=>$teaser_raw,
			"items"=>$this->items
		];

		return $data;
	}

	/**
	 * Returns teaser raw.
	 */
	public function get_teaser($amount){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->approve_url."/api/v2/approve-widget/finance-teasers/".$amount);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		if(!empty($this->cacert_file)) curl_setopt ($ch, CURLOPT_CAINFO, $this->cacert_file);
		//var_dump(openssl_get_cert_locations());
		$headers = array();
		$headers[] = 'Authorization: Basic '.$this->approve_id;
		$headers[] = 'Content-Type: application/json';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			return 'Error:' . curl_error($ch);
		}
		curl_close($ch);

		$data = json_decode($result);
		if($data->lease_teaser[0]->monthly_rate> 1)
			$teaser = number_format($data->lease_teaser[0]->monthly_rate,0);
		else
			$teaser = null;
		return $teaser;
	}
}
?>
<?php namespace EID\Http\Controllers;

use EID\Http\Requests;
use EID\Http\Controllers\Controller;

use EID\VLAPI;
use EID\Mongo;

use EID\LiveData;
use Log;
use DateTime;
use DateInterval;

class APIResultsController extends Controller {

	public function __construct()
    {
        $this->mongo=Mongo::connect();
    }

	public function facility_list(){
		return view('api_results.facility_list', ['sect'=>'results']);
	}

	public function facility_list_data(){
		$cols = ['facility', 'coordinator_name', 'coordinator_contact', 'coordinator_email'];
		$params = $this->get_params($cols);
		$params['hub'] = \Auth::user()->hub_id;
		$facilities = $this->getFacilities($params);

		$data = [];
		foreach ($facilities['data'] as $record) {
			extract($record);
			$facility_str = "<a href='/api/results/$pk'>$facility</a>";
			$data[] = [$facility_str, $coordinator_name, $coordinator_contact, $coordinator_email];
		}
		
		return [
			"draw" => \Request::get('draw'),
			"recordsTotal" => $facilities['recordsTotal'],
			"recordsFiltered" => $facilities['recordsFiltered'], 
			"data"=> $data
			];
	}

	public function results($facility_id){
		$facility = $this->mongo->api_facilities->findOne(['pk'=>(int)$facility_id]);
		$facility_name = isset($facility['facility'])?$facility['facility']:"";
		$tab = \Request::has('tab')?\Request::get('tab'):'pending';
		return view('api_results.results', compact('facility_id', 'facility_name','tab'));
	}

	public function results_data($facility_id){
		$cols = ['select','form_number', 'patient.art_number', 'patient.other_id', 'date_collected', 'date_received', 'result.resultsqc.released_at', 'options'];
		$params = $this->get_params($cols);
		$params['facility_id'] = $facility_id;

		$samples = $this->getSamples($params);

		$data = [];
		foreach ($samples['data'] as $sample) {
			extract($sample);
			$select_str = "<input type='checkbox' class='samples' name='samples[]' value='$_id'>";
			$url = "/api/result/$_id";
			$links = ['Print' => "javascript:windPop('$url')",'Download' => "$url?&pdf=1"];
			$released_at = $result['resultsqc']['released_at']?$result['resultsqc']['released_at']:$rejectedsamplesrelease['released_at'];
			$data[] = [
				$select_str, 
				$form_number, 
				$patient['art_number'], 
				$patient['other_id'], 
				\MyHTML::localiseDate($date_collected, 'd-M-Y'), 
				\MyHTML::localiseDate($date_received, 'd-M-Y'), 
				\MyHTML::localiseDate($released_at, 'd-M-Y'),
				\MyHTML::dropdownLinks($links)];
		}

		return [
			"draw" => \Request::get('draw'),
			"recordsTotal" => $samples['recordsTotal'],
			"recordsFiltered" => $samples['recordsFiltered'], 
			"data"=> $data
			];
	}

	public function result($id=""){
		if(!empty($id)){
			$cond = ['_id'=>$this->_id($id)];
		}else{
			$samples = \Request::get("samples");
			if(count($samples)==0){
				return "please select at least one sample";
			}else{
				$objs = array_map(function($id){ return $this->_id($id); }, $samples);
				$cond = ['_id'=>['$in'=>$objs]];
			}
		}
		
		$vldbresult = $this->mongo->api_samples->find($cond);
		$printed=$downloaded=false;
		if( \Request::has('pdf')) $downloaded = true;
		else $printed = true;

		$log_update = [
			'result.resultsqc.printed'=>$printed, 
			'result.resultsqc.downloaded'=>$downloaded, 
			'result.resultsqc.print_date'=>date('Y-m-d H:i:s'),
			'result.resultsqc.printed_by'=>\Auth::user()->username, 
			];
			
		//$this->mongo->api_samples->update($cond,['$set'=>$log_update], ['multiple'=>true]);

		if(\Request::has('pdf')){
			$pdf = \PDF::loadView('api_results.result_slip', compact("vldbresult"));
			return $pdf->download('vl_results_'.session('facility').'.pdf');
		}
		return view('api_results.result_slip', compact('vldbresult'));
	}

	private function _id($id){
		return new \MongoId($id);
	}

	private function get_params($cols){
    	$order = \Request::get('order');
    	$tab = \Request::get('tab');
    	$orderby = [$cols[0]=>1];		
		if(isset($order[0])){
			$col = $cols[$order[0]['column']];
			$dir = $order[0]['dir'];
			$orderby = $dir=='asc'?[$col=>1]:[$col=>-1];
		}

		$search = \Request::has('search')?\Request::get('search')['value']:"";
		$start = \Request::get('start');
		$length = \Request::get('length');
		$printed = $tab=='completed'?true:false;

		return compact('orderby','search', 'start', 'length', 'printed');
    }



	private function getFacilities($params){
		$ret=[];
		extract($params);
		$cond=[];
		if(!empty($hub)) $cond['$and'][]=["hub"=>$hub];
		$ret['recordsTotal'] = $this->mongo->api_facilities->find($cond)->count();
		if(!empty($search)) $cond['$and'][] = ['facility'=>new \MongoRegex("/$search/i")];

		$ret['data'] = $this->mongo->api_facilities->find($cond)->sort($orderby)->skip($start)->limit($length);
		$ret['recordsFiltered'] = $this->mongo->api_facilities->find($cond)->count();

		return $ret;
	}

	private function getSamples($params){
		$ret=[];
		extract($params);
		$cond=[];
		$cond['$and'][] = ['$or'=>[['result.resultsqc.released'=>true], ['rejectedsamplesrelease.released'=>true]]];
		$cond['$and'][] = ["facility.pk"=>(int)$facility_id];
		/*if($printed==false){
			$cond['$and'][] = ['result.resultsqc.printed'=>false, 'result.resultsqc.downloaded'=>false];
		}else{
			$cond['$and'][] = ['$or'=>[['result.resultsqc.printed'=>true], ['rejectedsamplesrelease.downloaded'=>true]]];
		} */
		$ret['recordsTotal'] = $this->mongo->api_samples->find($cond)->count();
		if(!empty($search)){
			$mongo_search = new \MongoRegex("/$search/i");
			$cond['$and'][] = ['form_number' => $mongo_search];
		} 
		$ret['data'] = $this->mongo->api_samples->find($cond)->sort($orderby)->skip($start)->limit($length);
		$ret['recordsFiltered'] = $this->mongo->api_samples->find($cond)->count();

		return $ret;
	}


}
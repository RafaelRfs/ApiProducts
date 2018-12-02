<?php
require_once('config.php');
require_once('db.php');

$request = isset($_REQUEST)? $_REQUEST : "" ;  
$post = isset($_REQUEST['method']) ? trim($_REQUEST['method']) : "";
$data = array();
$db =  new Db();
$db->setTable(TABLE);

if($post == 'listProducts'){
  $limit = 	isset($request['limit'])? strip_tags(trim($request['limit'])) : "";  
  $search = isset($request['search'])? true : false;
  $termSearch = isset($request['search'])? strip_tags(trim($request['search'])) : "";
  $orderBy = isset($request['orderBy'])? strip_tags(trim($request['orderBy'])) : "";
  $groupBy = isset($request['groupBy'])? strip_tags(trim($request['groupBy'])) : "";
  $where = isset($request['where']) ? $request['where'] : array();
  if($search == true){
     $searchParams[0] = 'nome';
	 $db->setSearchParams($searchParams);
	 $db->setDistinct();
	 $where = array();
	 $where['nome'] =  $termSearch;
  }
  $data =  $db->ReadPdo($where,"",$limit,$orderBy, $groupBy);
}

else if($post == 'addProducts'){
	$dt = isset($request['data']) && is_array($request['data'])? $request['data'] : array();
	$db->Create($dt);
	$data =  $db->ReadPdo(array(),"",1,' id DESC ','');
}

else if($post == 'updateProducts'){
	$id  = isset($request['id']) && ((int)$request['id'] > 0 && (int)$request['id'] <= 99999)?  (int)$request['id'] : 0;
	$dt = isset($request['data']) && is_array($request['data'])? $request['data'] : array();
	if($id > 0)$db->Update($id,'id',$dt);
}

else if($post == 'deleteProducts'){
	$id  = isset($request['id']) && ((int)$request['id'] > 0 && (int)$request['id'] <= 99999)?  (int)$request['id'] : 0;
	if($id > 0) $db->Delete($id,'id');
}

$db->writeDataIntoFile();
echo json_encode($data);
<?php
class Db{
private $conn, $table = 'users', $logfile = 'LogDb.txt', $numbers_indices_search  = array(), 
$name_indices_search = array(), $search_params = array(), $camps = '', $distinct = false, $indiceOR = array(), $relationship = array(),$whereType = array(), $setCustomWhere  = "";
function __construct(){
		$this->getConn();
	}
	
function getConn(){
	try{	
	if(is_null($this->conn)){
	$this->conn =  new PDO(TYPEBD.':host='.HOST.';dbname='.DBNAME.';charset=utf8', USER,PASS, array(PDO::ATTR_PERSISTENT => true, PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'));	
	$this->conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	}
	return $this->conn;
	}catch(Exception $e){	
	   $this->writeError($e);	
	}
}
function closeConn(){
	$this->conn = null;
}
public function setTable($table){
	$this->table = $table;
	}
	
function writeError($e){
	$errorx = $e->getMessage();
	$fp = fopen($this->logfile, 'a');
	$write = fwrite($fp,date("d-m-Y H:i:s ").": Erro-> ");
	$write = fwrite($fp,$errorx.PHP_EOL.PHP_EOL);
	fclose($fp);
	$data['error'] = 'Erro -> '.$errorx;
	echo json_encode($data);
	exit();
}	
public function writeDataIntoFile(){
	$op = fopen(FILE_DATE_MODIFIED, 'w');
	fwrite($op, date('Y-m-d H:i:s'));
	fclose($op);	
}
public function getFileDataLast(){
	$data = "";	
	if(file_exists(FILE_DATE_MODIFIED)){
	$lines = file(FILE_DATE_MODIFIED);
	foreach($lines as $line)
	{
	$data = $line;
	}
	}
	return $data;
	}
			
public function getTable(){ return $this->table;}
public function getNumbersIndicesSearch(){return $this->numbers_indices_search;}
public function setNumbersIndicesSearch($indicesSearch){$this->numbers_indices_search[] = $indicesSearch;}
public function resetNumbersIndicesSearch(){$this->numbers_indices_search = array();}
public function setNameIndicesNumbers($ind, $value){ $this->name_indices_search[$ind] = $value;}
public function getNameIndicesNumbers($ind){ return $this->name_indices_search[$ind];} 
public function resetNameIndicesNumbers(){$this->name_indices_search = array();}
public function setSearchParams($searchs = array()){
	if(is_array($searchs)){
	$this->search_params = $searchs;
	}
	}
public function getSearchParams(){
	return $this->search_params;
	}	
public function addSearchParams($val){
	$this->search_params[] = $val;
	}
public function resetSearchParams(){$this->search_params = array();}
public function setSearchString($strSearch){
	$strSearch = explode(',', $strSearch);
	$this->setSearchParams($strSearch);
	}
public function isIndiceSearch($ind){
	$verify = false;
	foreach($this->getNumbersIndicesSearch() as $inds){
		if($inds == $ind){ $verify = true; 
		}
		}
	return $verify;
	}
public function setCustomWhere($wher){ $this->setCustomWhere = $wher; }
public function getCustomWhere(){ 
$wher = "";
if(trim($this->setCustomWhere) <> ''){
	$wher = (strpos(strtoupper($this->setCustomWhere), "WHERE") )? $this->setCustomWhere : ' WHERE '.$this->setCustomWhere;
	}
return $wher ;
}
public function resetCustomWhere(){ $this->setCustomWhere = "";}
public function setCamps($params){ $this->camps = $params;}
public function addCamps($value){ if($this->camps <> '') $this->camps = $value; else $this->camps .= $value; }
public function getCamps(){ return $this->camps; }
public function resetCamps(){ $this->camps = "";}
public function getSqlCamps(){
	$val = "*";
	if(trim($this->camps) <> '') $val = $this->getCamps();
	if($this->getDistinct()){  $val = " DISTINCT ".$val;   }
	return $val;
	}
public function setWhereType($ind, $value){ $this->whereType[$ind] = $value; }
public function getWhereType($ind){
	$where = "=";
	if(isset($this->whereType[$ind]) && trim($this->whereType[$ind]) <> ''){
		$where = $this->whereType[$ind];
		}
	return $where;
	}
public function resetWhereType(){ $this->whereType = array();}
public function setDistinct(){ $this->distinct = true; }
public function getDistinct(){ return $this->distinct;}
public function resetDistinct(){ $this->distinct = false;}
public function getIndicesOR(){ return $this->indiceOR;}
public function setIndicesOR($inds = array()){   if(is_array($inds)) $this->indiceOR = $inds; else $this->indiceOR = array();    }
public function resetIndicesOR(){ $this->setIndicesOR();}
public function setIndicesOrString($value){
	$strIndiceOr = explode(',',$value);
	$this->setIndicesOR($strIndiceOr);
	}
public function verifyIsAndOR($value){
	$resultado = " AND ";
	foreach($this->indiceOR as $inds){
		if($inds == $value){ $resultado = " OR ";}
		}
return $resultado ;	
}
public function addRelationship($table_with_alias = "" , $connected_camps, $joinType = " INNER "){
	$sql_relationship = " ".trim($joinType)." JOIN ".$table_with_alias." ON ".$connected_camps." ";
	$this->relationship[] = $sql_relationship;
}
public function getRelationships(){
	return $this->relationship;
	}
public function getRelationshipsString(){
	$string_relationships = "";
	foreach($this->getRelationships() as $relat){
		$string_relationships .= " ".$relat." ";
		}
	return $string_relationships;
	}
	
public function resetRelationship(){
	$this->relationship =  array();
	}
	
private function getStr($data){
	$dt =  array();
	$indices = is_array($data)? array_keys($data) : array();
	$indices_search = $this->getSearchParams();
	$dt['ind'] = $indices;
	$count = count($indices);
	$dt['count'] = $count; 
    $num = 0;
    $dt['str'] = '';
	$dt['qst'] = '';
    $dt['cmb'] = '';
	$dt['read'] = '';
	
    foreach($indices as $ind){
	$dt['str'] .=$ind;
	$dt['qst'] .='?';
	$dt['cmb'] .= $ind.$this->getWhereType($ind).'?';
	$dt['read'] .= (!$this->verifyIfIsSearch($indices_search, $ind)) ? $ind.$this->getWhereType($ind).'?' : $ind." LIKE ?";
	
	if($this->verifyIfIsSearch($indices_search, $ind)){ 
	$index_pesquisa = $num + 1;
	$this->setNumbersIndicesSearch($index_pesquisa);
	$this->setNameIndicesNumbers($index_pesquisa,":".$ind);
	}
	
	if($num+1 < $count){ 
	$dt['str'] .=','; 
	$dt['qst'] .=',';
	$dt['cmb'] .=',';
	$valor_next = $indices[$num+1];
	$dt['read'] .= $this->verifyIsAndOR($valor_next);	
	}
	$num++;
	}
	
	return $dt;
	}
function prepareSql($pdo, $dta,$dx){
	$count = $dx['count'];
	$count2 = count($dta);
	if($count == $count2){
      for($i = 0; $i < $count ; $i++){
		  $v = $i + 1 ;
		  $pdo = $this->getVarTp($v,$dta[$dx['ind'][$i]], $pdo);
		}
	$pdo->execute();  
	}
	}
	
function Reset(){
	$this->resetNumbersIndicesSearch();
	$this->resetNameIndicesNumbers();
	$this->resetSearchParams();
	$this->resetCamps();
	$this->resetDistinct();
	$this->resetIndicesOR();
	$this->resetRelationship();
	$this->resetWhereType();
	$this->resetCustomWhere();
	}	
	
function prepareSql2($pdo, $dta,$dx, $reset = 1){
	$count = is_array($dx)? $dx['count'] : 0;
	$count2 = count($dta);
	if($count == $count2){
      for($i = 0; $i < $count ; $i++){
		  $v = $i + 1 ;
		  $pdo = $this->getVarTp($v,$dta[$dx['ind'][$i]], $pdo); 
		}
	$pdo->execute();  
	}
	
	if($reset == 1){
	$this->Reset();
	}
	return $pdo;
	}
	
		
public function verifyIfIsSearch($indices_search, $value){
	$search = false;
	if(is_array($indices_search)){
	foreach($indices_search as $inds){
		if((string)$inds == (string)$value){
			$search = true;				
		}
	}
	}
	return $search;
}
			
function getVarTp($indice,  $value, $pdo){
	$tp_data = gettype($value);
	$isSearchValue = $this->isIndiceSearch($indice);
	
	if($isSearchValue === true){	
        $search = "%".(string)$value."%";
		$pdo->bindValue($indice,$search , PDO::PARAM_STR );	
    }else{
	switch($tp_data){
		case "integer":
		$pdo->bindValue($indice, $value, PDO::PARAM_INT );
		break;
	
		case "string":
		$pdo->bindValue($indice, $value, PDO::PARAM_STR );
		break;
		
		case "NULL":
		$pdo->bindValue($indice, $value, PDO::PARAM_NULL );
		break;
		
		case "":
		$pdo->bindValue($indice, $value, PDO::PARAM_NULL );
		break;
			
		case "double":
		$pdo->bindValue($indice, $value, PDO::PARAM_STR );
		break;
		
		case "boolean":
		$pdo->bindValue($indice, $value, PDO::PARAM_BOOL);
		break;
	
		default:
		$pdo->bindValue($indice, $value, PDO::PARAM_STR );	
	}
	}
	return $pdo;
}	
		
function Prepare($sql,$arg = '',$arg2 = '',$arg3='',$arg4='',$arg5 = ''){
		try{
		$data = array();
		$dados = $this->getConn()->prepare($sql);			
		if(trim($arg) <> '' ){   $dados = $this->getVarTp(1,  $arg, $dados);   }
		if(trim($arg2) <> '' ){  $dados = $this->getVarTp(2,  $arg2, $dados);   }
		if(trim($arg3) <> '' ){  $dados = $this->getVarTp(3,  $arg3, $dados);   }
		if(trim($arg4) <> '' ){  $dados = $this->getVarTp(4,  $arg4, $dados);    }
		if(trim($arg5) <> '' ){  $dados = $this->getVarTp(5,  $arg5, $dados);     }
		$dados->execute();
		$data['count'] = $dados->rowCount();
		$data['data'] = $this->fetchPdo($dados);
		return $data;
		}catch(Exception $e){ $this->writeError($e);}
	}	
	
public function Create($data,$table = ''){
	try{
	$tab = trim($table) == '' ? $this->table : $table;
	$dt = $this->getStr($data);
	$sql = "INSERT INTO ".$tab."(".$dt['str'].") Values(".$dt['qst'].")";
	$pdo = $this->getConn()->prepare($sql);
	$this->prepareSql($pdo,$data,$dt);
	}catch(Exception $e){
		$this->writeError($e);	
		}
    }
public function Read($where = '', $table = '', $limit = "",$orderby = ""){
	try{
	$tab = trim($table) == '' ? $this->table : $table;
	$sql = "SELECT * FROM ".$tab;
	$sql = strip_tags(trim($where == ''))? $sql : $sql." WHERE ".$where;
	$sql = (trim($orderby) <> '')? $sql." ORDER BY ".$orderby : $sql; 
	$sql = (trim($limit) <> '') ? $sql.$limit  :$sql ;	
	$pdo = $this->getConn()->query($sql);
	return $this->fetchPdo($pdo);
	}catch(Exception $e){
		$this->writeError($e);	
		}
	}
public function fetchPdo($pdo){
  	$dt = array();
	while($arr = $pdo->fetch()){
		$dt[] = $arr;
		}
	return $dt;
}
	
public function ReadPdo($where = array(), $table = '', $limit = "",$orderby = "", $groupby = ""){
	try{
	if(!is_array($where) && trim($where) == ''){
		$where = array();
	}		
	$tab = trim($table) == '' ? $this->table : $table;
	$sql = "SELECT ".$this->getSqlCamps()." FROM ".$tab." ";
	$strWher =  is_array($where) && count($where) > 0 ? $this->getStr($where) : '';
	$str_where = "";
	if((is_array($where) && isset($strWher['read']) && strpos($strWher['read'], "WHERE"))){
		$str_where =  ($strWher['read'] <> '')?  $strWher['read']  : '';
		}
	else if(is_array($where) && isset($strWher['read']) && !(strpos($strWher['read'], "WHERE"))){
		$str_where = (count($where) > 0) ? " WHERE ".$strWher['read'] : '';
		}
	else if(!(is_array($where)) && !(strpos($where, "WHERE")) && trim($where) <> ''){
		$str_where = (trim($where) <>  '')? " WHERE ".$where : '';
		}
	$sql .= $this->getRelationshipsString();	
	$sql .= ($this->getCustomWhere() == '')? $str_where : $this->getCustomWhere();
	$groupby = trim($groupby) <> '' && !(strpos(strtoupper($groupby), 'GROUP BY')) ? " GROUP BY ".$groupby : $groupby;
	$orderby = trim($orderby) <> '' && !(strpos(strtoupper($orderby),'ORDER BY'))  ? " ORDER BY ".$orderby : $orderby;
	$limit   = trim($limit) <> ''   && !(strpos(strtoupper($limit),'LIMIT')) ?  " LIMIT ".$limit : $limit;
	$sql .= $groupby.$orderby.' ';
	$sql_all = $sql;
	$sql .= $limit <> '' ? $limit : '';
	$pdo = $this->getConn()->prepare($sql);
	$pdo = $this->prepareSql2($pdo,$where,$strWher, 0);
	$dat = array();
	$pdo2 = $this->getConn()->prepare($sql_all);
	$pdo2 = $this->prepareSql2($pdo,$where,$strWher,1);
	$dat['sql'] = $sql;
	$data['where'] = $where;
	$dat['count'] = $pdo->rowCount();
	$dat['count_all'] = $pdo2->rowCount();
	$dat['data'] = $this->fetchPdo($pdo);
	unset($pdo);
	unset($pdo2);
    return $dat;
	}catch(Exception $e){
		$this->writeError($e);	
		}
	}		
public function getStringWhere($where = array(), $and = "AND", $search = 0){
	$wher = '';
	$count = count($where);
	$buscar = ' LIKE :buscar ';
		$num = 0;
		if($count > 0){
			$wher = " WHERE ";
			$indices  = ($search == 0)? array_keys($where) : $where;
			foreach($indices as $ind){
				$aux_count = $count -1;
				if($search == 0){
				$wher .=$ind.'="'.$where[$ind].'"';
				}else{
				$wher .='`'.$ind.'`'.$buscar;
					}
				
				if(($num) <> $aux_count ){
					$wher .=' '.$and.' ';
					}
				else{
					$wher .='';
					}	
				++$num;
				}//Fecha o Foreach			
			}
	return $wher;
	}
	//Retorna o sql WHERE
	
public function Update($id,$camp = 'id',$data,$table){
	try{
	$tab = trim($table) == '' ? $this->table : $table;
	$dt =  $this->getStr($data);
	$sql = "UPDATE ".$tab." SET ".$dt['cmb']." WHERE ".$camp."='".$id."'";
	$pdo = $this->getConn()->prepare($sql);
	$this->prepareSql($pdo,$data,$dt);
	}catch(Exception $e){
	$this->writeError($e);	
		}
	}
	
public function Delete($id, $camp='id', $table = ''){
	try{
	$tab = trim($table) == '' ? $this->table : $table;
	$sql = "DELETE FROM ".$tab." WHERE ".$camp."='".$id."'";
	$pdo = $this->getConn()->query($sql);
	}catch(Exception $e){
	$this->writeError($e);	
		}
	}
	
public function DeleteData($where,$table = ''){
	try{
	$tab = trim($table) == '' ? $this->table : $table;
	$sql = "DELETE FROM ".$tab." WHERE ".$where;
	$pdo = $this->getConn()->query($sql);
	}catch(Exception $e){
	$this->writeError($e);	
		}
	}	
	
	
function create_guid()
{
    $microTime = microtime();
    list($a_dec, $a_sec) = explode(' ', $microTime);
    $dec_hex = dechex($a_dec * 1000000);
    $sec_hex = dechex($a_sec);
    $this->ensure_length($dec_hex, 5);
    $this->ensure_length($sec_hex, 6);
    $guid = '';
    $guid .= $dec_hex;
    $guid .= $this->create_guid_section(3);
    $guid .= '-';
    $guid .= $this->create_guid_section(4);
    $guid .= '-';
    $guid .= $this->create_guid_section(4);
    $guid .= '-';
    $guid .= $this->create_guid_section(4);
    $guid .= '-';
    $guid .= $sec_hex;
    $guid .= $this->create_guid_section(6);
    return $guid;
}	
	
public function create_guid_section($characters)
{
    $return = '';
    for ($i = 0; $i < $characters; ++$i) {
        $return .= dechex(mt_rand(0, 15));
    }
    return $return;
}
public function ensure_length(&$string, $length)
{
    $strlen = strlen($string);
    if ($strlen < $length) {
        $string = str_pad($string, $length, '0');
    } elseif ($strlen > $length) {
        $string = substr($string, 0, $length);
    }
}
public function microtime_diff($a, $b)
{
    list($a_dec, $a_sec) = explode(' ', $a);
    list($b_dec, $b_sec) = explode(' ', $b);
    return $b_sec - $a_sec + $b_dec - $a_dec;
}
	
}
?>
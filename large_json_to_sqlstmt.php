<?php
set_time_limit(0);
ini_set('memory_limit', '16G');


echo "Usage : php large_json_to_sqlstmt.php -i input.json -o output.sql -k name,_type,LotUrl\n";

$options = "i:o:k:";
$opts = getopt( $options );

if(!$opts || !array_key_exists("i",$opts) || !array_key_exists("o",$opts) || !array_key_exists("k",$opts)){
	echo "Invalid or Insufficient arguments.\n";
	exit(1);
}

echo "Started parsing input json.....\n";

$keys = explode(",",$opts[k]);

$handle = @fopen($opts["i"], "r");
if ($handle) {
    while (($buffer = fgets($handle)) !== false) {
		$values = "";
		$trimmedBuffer = preg_replace('/} *,$/', '}', $buffer);
        $jsonData=json_decode($trimmedBuffer,true);
		if($jsonData) {
			foreach($keys as $key=>$val){
				$value = getValue($jsonData,$val);
				if(!$value)
					$value = "NO_VALUE";
				
				$values = $values . "'$value' ,";
			}
			
			$values = rtrim($values,",");
			$stmt = "INSERT INTO Data VALUES($values);\n";
			file_put_contents($opts["o"], $stmt , FILE_APPEND | LOCK_EX);
		}
		else {
			file_put_contents(dirname($opts["o"])."/failed", $buffer , FILE_APPEND | LOCK_EX);
		}
    }
    if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($handle);
}

echo "Done parsing....\n";

function getValue($obj, $searchKey)
{
	$ret = null;
	foreach ($obj as $key=>$val) {
		if($searchKey === $key) {
			return $val;
		}
		switch (gettype($val)) {
			case 'object':
			case 'array':
				$ret = getValue($val,$searchKey);
				if($ret)
					return $ret;				
		}
	}
	
	return $ret;
}


?>


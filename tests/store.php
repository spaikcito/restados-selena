<?php
// Prueba aislada de transporte: no crea ni afirma una base real.
$GLOBALS['redis_test']=[];
function curl_init($url){return (object)['url'=>$url];}
function curl_setopt_array($ch,$opts){$ch->opts=$opts;return true;}
function curl_getinfo($ch,$key){return 200;}
function redis_test($c){$db=&$GLOBALS['redis_test'];switch($c[0]){
 case 'GET':return ['result'=>$db[$c[1]]??null];
 case 'SET':if(in_array('NX',$c,true)&&isset($db[$c[1]]))return ['result'=>null];$db[$c[1]]=$c[2];return ['result'=>'OK'];
 case 'HSET':$added=0;for($i=2;$i<count($c);$i+=2){if(!isset($db[$c[1]][$c[$i]]))$added++;$db[$c[1]][$c[$i]]=$c[$i+1];}return ['result'=>$added];
 case 'EVAL':if(($db[$c[3]]??null)===$c[4]){unset($db[$c[3]]);return ['result'=>1];}return ['result'=>0];
 default:throw new Exception('Unexpected command');}}
function curl_exec($ch){$command=json_decode($ch->opts[CURLOPT_POSTFIELDS],true);return json_encode(str_ends_with($ch->url,'/multi-exec')?array_map('redis_test',$command):redis_test($command));}
require dirname(__DIR__).'/source_common.php';
putenv('UPSTASH_REDIS_REST_URL=https://unit-test.upstash.io');putenv('UPSTASH_REDIS_REST_TOKEN=unit-test-only');
function check($condition,$message){if(!$condition)throw new Exception($message);}
$body=['ok'=>true,'fecha'=>selena_today(),'consultado_en'=>gmdate('c'),'resultados'=>[['loteria'=>'Lotto Activo','hora'=>'8:00 AM','numero'=>'0','animal'=>'Delfin'],['loteria'=>'Lotto Activo','hora'=>'9:00 AM','numero'=>'00','animal'=>'Ballena']]];
check(selena_store_save('365',$body),'Confirma escritura');selena_store_save('365',$body);
$hash=$GLOBALS['redis_test'][selena_store_key('resultados','365')];check(count($hash)===2,'Sin duplicados');
check(json_decode($hash['lottoactivo|8:00 AM'],true)['numero']==='0','Delfin guardado como texto');check(json_decode($hash['lottoactivo|9:00 AM'],true)['numero']==='00','Ballena guardada como texto');
check(selena_store_read('365')===$body,'Lee respuesta persistente');
$body['resultados'][0]['numero']='25';selena_store_save('365',$body);check(count($GLOBALS['redis_test'][selena_store_key('resultados','365')])===2,'Corrección sin duplicar');
$key=selena_store_key('lock','365');check(selena_store_command(['SET',$key,'a','NX','EX',35])==='OK','Reserva compartida');check(selena_store_command(['SET',$key,'b','NX','EX',35])===null,'Reserva impide duplicado');
$selena_store_lock=['key'=>$key,'token'=>'b'];selena_store_release();check(selena_store_command(['GET',$key])==='a','No libera reserva ajena');$selena_store_lock=['key'=>$key,'token'=>'a'];selena_store_release();check(selena_store_command(['GET',$key])===null,'Libera su reserva');
$old=$body;$old['fecha']='2000-01-01';$GLOBALS['redis_test'][selena_store_key('snapshot','365')]=json_encode($old);check(selena_store_read('365')===null,'No reutiliza otro día');
echo "PASS: storage transport contract, persistent read/write, no duplicates, corrections, independent zero strings, shared lock ownership, previous-day rejection.\n";

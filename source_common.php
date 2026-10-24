<?php
require_once __DIR__.'/resultados_config.php';
require_once __DIR__.'/resultados_store.php';
function selena_today(){return (new DateTimeImmutable('now',new DateTimeZone('America/Caracas')))->format('Y-m-d');}
function selena_cache_fresh($body){$at=strtotime($body['consultado_en']??'');$age=$at?time()-$at:PHP_INT_MAX;return !empty($body['ok'])&&($body['fecha']??'')===selena_today()&&$age>=0&&$age<60;}
function selena_date($text){
 if(preg_match('/\b(20\d{2})-(\d{2})-(\d{2})\b/u',$text,$m))return checkdate((int)$m[2],(int)$m[3],(int)$m[1])?$m[0]:null;
 $months=['enero'=>1,'january'=>1,'febrero'=>2,'february'=>2,'marzo'=>3,'march'=>3,'abril'=>4,'april'=>4,'mayo'=>5,'may'=>5,'junio'=>6,'june'=>6,'julio'=>7,'july'=>7,'agosto'=>8,'august'=>8,'septiembre'=>9,'september'=>9,'octubre'=>10,'october'=>10,'noviembre'=>11,'november'=>11,'diciembre'=>12,'december'=>12];
 if(preg_match('/\b(\d{1,2})\s+(?:de\s+)?([a-z]+)\s+(?:de\s+)?(20\d{2})\b/iu',$text,$m)){$month=$months[strtolower($m[2])]??0;if(checkdate($month,(int)$m[1],(int)$m[3]))return sprintf('%04d-%02d-%02d',$m[3],$month,$m[1]);}return null;
}
function selena_emit($body,$status=200){
 global $selena_cache_file,$selena_cache_lock,$selena_request_date,$selena_cache_hit,$selena_source,$selena_saved_snapshot;
 $ok=$status===200&&!empty($body['ok']);
 if($ok&&isset($selena_request_date)&&$selena_request_date!==selena_today()){$ok=false;$status=502;$body=['ok'=>false,'error'=>'Cambió la fecha durante la consulta. Recarga la página.'];}
 if(!$ok&&is_array($selena_saved_snapshot??null)&&($selena_saved_snapshot['fecha']??'')===selena_today()){
  $body=$selena_saved_snapshot;$body['aviso']='La fuente no está disponible. Se muestran los últimos resultados guardados de hoy.';$body['cache_antigua']=true;$body['desde_historial']=true;$selena_cache_hit=true;$status=200;$ok=true;
 }
 if($ok){
  $body['fecha']=selena_today();$body['resultados']=selena_filtrar($body['resultados']??[]);$body['cantidad']=count($body['resultados']);
  foreach($body['resultados'] as &$r)$r['fecha']=$body['fecha'];unset($r);
  if(isset($body['logos']))$body['logos']=array_filter($body['logos'],fn($url,$name)=>selena_loteria($name)!==null,ARRAY_FILTER_USE_BOTH);
  if(!($selena_cache_hit??false)){
   $body['almacenamiento']=['configurado'=>selena_store_configured(),'guardado'=>false];
   if(selena_store_configured()){
    try{$body['almacenamiento']['guardado']=true;$body['almacenamiento']['guardado']=selena_store_save($selena_source,$body);}
    catch(Throwable $e){$body['almacenamiento']['guardado']=false;error_log('Selena: no se pudo guardar el resultado');}
   }
  }
 }
 $json=json_encode($body,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE);
 if($ok&&!($selena_cache_hit??false)&&isset($selena_cache_file))@file_put_contents($selena_cache_file,$json,LOCK_EX);
 selena_store_release();
 if(is_resource($selena_cache_lock??null)){flock($selena_cache_lock,LOCK_UN);fclose($selena_cache_lock);}
 header('Content-Type: application/json; charset=utf-8');header('X-Content-Type-Options: nosniff');
 header('Cache-Control: '.($ok?'public, max-age=0, must-revalidate':'no-store'));
 $midnight=(new DateTimeImmutable('tomorrow',new DateTimeZone('America/Caracas')))->getTimestamp();
 $age=time()-(strtotime($body['consultado_en']??'')?:time());$ttl=!empty($body['cache_antigua'])?5:max(1,60-max(0,$age));
 header('Vercel-CDN-Cache-Control: '.($ok?'public, s-maxage='.max(1,min($ttl,$midnight-time())):'no-store'));
 http_response_code($status);echo $json;exit;
}
function selena_cache_start($source){
 global $selena_cache_file,$selena_cache_lock,$selena_request_date,$selena_cache_hit,$selena_source,$selena_saved_snapshot,$selena_store_lock;
 if(!in_array($source,['365','lotoven','hoy'],true))throw new InvalidArgumentException('Fuente inválida');
 $selena_source=$source;$selena_cache_hit=false;$selena_request_date=selena_today();$selena_saved_snapshot=null;$selena_store_lock=null;
 $dir=sys_get_temp_dir().'/selena-results-v3';if(!is_dir($dir))@mkdir($dir,0700,true);
 $selena_cache_file=$dir.'/'.$source.'.json';$selena_cache_lock=@fopen($dir.'/'.$source.'.lock','c');
 $localBusy=is_resource($selena_cache_lock)&&!flock($selena_cache_lock,LOCK_EX|LOCK_NB);
 if(is_file($selena_cache_file)){
  $data=json_decode(file_get_contents($selena_cache_file),true);
  if(is_array($data)&&($data['fecha']??'')===selena_today()&&!empty($data['ok'])){
   $selena_saved_snapshot=$data;
   if(selena_cache_fresh($data)){$selena_cache_hit=true;selena_emit($data);}
  }
 }
 if($localBusy)selena_emit(['ok'=>false,'error'=>'Hay una consulta en curso. Recarga en unos segundos.'],503);
 if(selena_store_configured()){
  try{
   $saved=selena_store_read($source);
   if($saved){$selena_saved_snapshot=$saved;if(selena_cache_fresh($saved)){$saved['desde_historial']=true;$selena_cache_hit=true;selena_emit($saved);}}
   // Una sola descarga de la fuente entre todas las instancias de Vercel.
   $key=selena_store_key('lock',$source);$token=bin2hex(random_bytes(16));
   if(selena_store_command(['SET',$key,$token,'NX','EX',35])!=='OK')selena_emit(['ok'=>false,'error'=>'Hay una consulta en curso. Recarga en unos segundos.'],503);
   $selena_store_lock=['key'=>$key,'token'=>$token];
   // Otra instancia pudo terminar entre nuestra lectura y la reserva.
   $saved=selena_store_read($source);if($saved&&selena_cache_fresh($saved)){$saved['desde_historial']=true;$selena_cache_hit=true;selena_emit($saved);}
  }catch(Throwable $e){error_log('Selena: historial no disponible; se utiliza la fuente pública');}
 }
}
function selena_require_today($date){if($date!==selena_today())selena_emit(['ok'=>false,'error'=>$date?'La fuente publica resultados de otra fecha: '.$date.'.':'No se pudo verificar la fecha de los resultados.','fecha_fuente'=>$date,'fecha_actual'=>selena_today()],502);}

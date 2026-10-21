<?php
// Caché efímera: no guarda historial ni utiliza una base de datos.
function selena_today(){return (new DateTimeImmutable('now',new DateTimeZone('America/Caracas')))->format('Y-m-d');}
function selena_date($text){
 if(preg_match('/\b(20\d{2})-(\d{2})-(\d{2})\b/u',$text,$m))return checkdate((int)$m[2],(int)$m[3],(int)$m[1])?$m[0]:null;
 $months=['enero'=>1,'january'=>1,'febrero'=>2,'february'=>2,'marzo'=>3,'march'=>3,'abril'=>4,'april'=>4,'mayo'=>5,'may'=>5,'junio'=>6,'june'=>6,'julio'=>7,'july'=>7,'agosto'=>8,'august'=>8,'septiembre'=>9,'september'=>9,'octubre'=>10,'october'=>10,'noviembre'=>11,'november'=>11,'diciembre'=>12,'december'=>12];
 if(preg_match('/\b(\d{1,2})\s+(?:de\s+)?([a-z]+)\s+(?:de\s+)?(20\d{2})\b/iu',$text,$m)){$month=$months[strtolower($m[2])]??0;if(checkdate($month,(int)$m[1],(int)$m[3]))return sprintf('%04d-%02d-%02d',$m[3],$month,$m[1]);}return null;
}
function selena_emit($body,$status=200){
 global $selena_cache_file,$selena_cache_lock,$selena_request_date,$selena_cache_hit;
 $ok=$status===200&&!empty($body['ok']);
 if($ok&&isset($selena_request_date)&&$selena_request_date!==selena_today()){$ok=false;$status=502;$body=['ok'=>false,'error'=>'Cambió la fecha durante la consulta. Actualiza de nuevo.'];}
 if($ok){$body['fecha']=selena_today();foreach($body['resultados'] as &$r)$r['fecha']=$body['fecha'];unset($r);}
 $json=json_encode($body,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE);
 if($ok&&!($selena_cache_hit??false)&&isset($selena_cache_file))@file_put_contents($selena_cache_file,$json,LOCK_EX);
 if(is_resource($selena_cache_lock??null)){flock($selena_cache_lock,LOCK_UN);fclose($selena_cache_lock);}
 header('Content-Type: application/json; charset=utf-8');header('X-Content-Type-Options: nosniff');
 header('Cache-Control: '.($ok?'public, max-age=0, must-revalidate':'no-store'));
 $midnight=(new DateTimeImmutable('tomorrow',new DateTimeZone('America/Caracas')))->getTimestamp();
 header('Vercel-CDN-Cache-Control: '.($ok?'public, s-maxage='.max(1,min(15,$midnight-time())):'no-store'));
 http_response_code($status);echo $json;exit;
}
function selena_cache_start($source){
 global $selena_cache_file,$selena_cache_lock,$selena_request_date,$selena_cache_hit;
 $selena_cache_hit=false;$selena_request_date=selena_today();
 $dir=sys_get_temp_dir().'/selena-results';if(!is_dir($dir))@mkdir($dir,0700,true);
 $selena_cache_file=$dir.'/'.$source.'.json';$selena_cache_lock=@fopen($dir.'/'.$source.'.lock','c');
 if(is_resource($selena_cache_lock))flock($selena_cache_lock,LOCK_EX);
 clearstatcache(true,$selena_cache_file);
 if(is_file($selena_cache_file)&&time()-filemtime($selena_cache_file)<15){$data=json_decode(file_get_contents($selena_cache_file),true);if(is_array($data)&&($data['fecha']??'')===selena_today()&&!empty($data['ok'])){$selena_cache_hit=true;selena_emit($data);}}
}
function selena_require_today($date){if($date!==selena_today())selena_emit(['ok'=>false,'error'=>$date?'La fuente publica resultados de otra fecha: '.$date.'.':'No se pudo verificar la fecha de los resultados.','fecha_fuente'=>$date,'fecha_actual'=>selena_today()],502);}

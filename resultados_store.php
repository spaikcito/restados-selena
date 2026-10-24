<?php
// Upstash Redis REST: credenciales únicamente en variables privadas de Vercel.
function selena_store_settings(){
 foreach([['UPSTASH_REDIS_REST_URL','UPSTASH_REDIS_REST_TOKEN'],['KV_REST_API_URL','KV_REST_API_TOKEN']] as [$url,$token])if(getenv($url)&&getenv($token))return ['url'=>getenv($url),'token'=>getenv($token)];
 return null;
}
function selena_store_configured(){return selena_store_settings()!==null;}
function selena_store_command($command,$transaction=false){
 $settings=selena_store_settings();$url=rtrim((string)($settings['url']??''),'/');
 $parts=parse_url($url);
 if(!selena_store_configured()||!extension_loaded('curl')||!$parts||($parts['scheme']??'')!=='https'||!preg_match('/(?:^|\.)upstash\.io$/i',$parts['host']??'')||isset($parts['user'])||isset($parts['pass'])||isset($parts['query']))throw new RuntimeException('Historial no conectado');
 $ch=curl_init($url.($transaction?'/multi-exec':''));
 curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>1,CURLOPT_TIMEOUT=>2,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$settings['token'],'Content-Type: application/json'],CURLOPT_POSTFIELDS=>json_encode($command,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);
 $raw=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);$data=json_decode($raw?:'',true);
 if($code!==200||!is_array($data))throw new RuntimeException('Historial temporalmente no disponible');
 if($transaction){if(count($data)!==count($command))throw new RuntimeException('Escritura sin confirmar');foreach($data as $reply)if(isset($reply['error'])||!array_key_exists('result',$reply))throw new RuntimeException('No se pudo guardar el historial');return $data;}
 if(isset($data['error'])||!array_key_exists('result',$data))throw new RuntimeException('No se pudo consultar el historial');return $data['result'];
}
function selena_store_key($kind,$source){return 'selena:v3:'.$kind.':'.selena_today().':'.$source;}
function selena_store_read($source){$raw=selena_store_command(['GET',selena_store_key('snapshot',$source)]);$body=is_string($raw)?json_decode($raw,true):null;return is_array($body)&&($body['fecha']??'')===selena_today()&&!empty($body['ok'])?$body:null;}
function selena_store_save($source,$body){
 $hash=['HSET',selena_store_key('resultados',$source)];
 foreach($body['resultados'] as $r){$hash[]=selena_normalized($r['loteria']).'|'.$r['hora'];$hash[]=json_encode($r,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);}
 if(count($hash)<4)return false;
 // Sin caducidad: los sorteos quedan guardados por fecha, fuente, lotería y hora.
 selena_store_command([$hash,['SET',selena_store_key('snapshot',$source),json_encode($body,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]],true);return true;
}
function selena_store_release(){
 global $selena_store_lock;
 if(!$selena_store_lock)return;
 try{selena_store_command(['EVAL',"if redis.call('get',KEYS[1]) == ARGV[1] then return redis.call('del',KEYS[1]) else return 0 end",1,$selena_store_lock['key'],$selena_store_lock['token']]);}catch(Throwable $e){error_log('Selena: no se pudo liberar la reserva de consulta');}
 $selena_store_lock=null;
}

function selena_history_matches($r,$query){
 $q=trim($query);if(ctype_digit($q)){$n=$q==='0'?'0':($q==='00'?'00':str_pad((string)(int)$q,2,'0',STR_PAD_LEFT));return $r['numero']===$n;}
 return str_contains(selena_normalized($r['animal']??''),selena_normalized($q));
}
function selena_store_history($date){
 $commands=[];foreach(['365','lotoven','hoy'] as $source)$commands[]=['HGETALL','selena:v3:resultados:'.$date.':'.$source];
 $replies=selena_store_command($commands,true);$rows=[];
 foreach($replies as $i=>$reply){
  $source=['365','lotoven','hoy'][$i];$values=$reply['result']??[];if(!is_array($values))continue;
  // RESP may represent hashes as alternating field/value strings or a map.
  foreach($values as $value){
   $row=is_string($value)?json_decode($value,true):null;
   if(!is_array($row)||($row['fecha']??'')!==$date)continue;
   $filtered=selena_filtrar([$row]);if(!$filtered)continue;
   $row=$filtered[0];$row['fuente']=$source;$rows[]=$row;
  }
 }
 usort($rows,function($a,$b){$name=strcmp($a['loteria'],$b['loteria']);if($name)return $name;$ta=DateTimeImmutable::createFromFormat('!g:i A',$a['hora']);$tb=DateTimeImmutable::createFromFormat('!g:i A',$b['hora']);return ($tb?$tb->getTimestamp():0)<=>($ta?$ta->getTimestamp():0);});
 return $rows;
}

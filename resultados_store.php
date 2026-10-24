<?php
// Upstash Redis REST: credenciales únicamente en variables privadas de Vercel.
function selena_store_configured(){return (bool)(getenv('UPSTASH_REDIS_REST_URL')&&getenv('UPSTASH_REDIS_REST_TOKEN'));}
function selena_store_command($command,$transaction=false){
 $url=rtrim((string)getenv('UPSTASH_REDIS_REST_URL'),'/');
 $parts=parse_url($url);
 if(!selena_store_configured()||!extension_loaded('curl')||!$parts||($parts['scheme']??'')!=='https'||!preg_match('/(?:^|\.)upstash\.io$/i',$parts['host']??'')||isset($parts['user'])||isset($parts['pass'])||isset($parts['query']))throw new RuntimeException('Historial no conectado');
 $ch=curl_init($url.($transaction?'/multi-exec':''));
 curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>1,CURLOPT_TIMEOUT=>2,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.getenv('UPSTASH_REDIS_REST_TOKEN'),'Content-Type: application/json'],CURLOPT_POSTFIELDS=>json_encode($command,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);
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

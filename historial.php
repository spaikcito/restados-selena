<?php
require_once __DIR__.'/source_common.php';
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
function history_reply($body,$status=200){http_response_code($status);echo json_encode($body,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE);exit;}
$fecha=$_GET['fecha']??selena_today();$lottery=$_GET['loteria']??'';$query=$_GET['q']??'';
if(!is_string($fecha)||!is_string($lottery)||!is_string($query)||strlen($query)>80)history_reply(['ok'=>false,'error'=>'Filtros inválidos.'],400);
$date=DateTimeImmutable::createFromFormat('!Y-m-d',$fecha,new DateTimeZone('America/Caracas'));
if(!$date||$date->format('Y-m-d')!==$fecha||$fecha>selena_today())history_reply(['ok'=>false,'error'=>'Elige una fecha válida, hasta hoy.'],400);
if($lottery!==''&&!selena_loteria($lottery))history_reply(['ok'=>false,'error'=>'Lotería no habilitada.'],400);
if(!selena_store_configured())history_reply(['ok'=>false,'error'=>'El historial todavía no está conectado.'],503);
try{
 $rows=selena_store_history($fecha);
 if($lottery!=='')$rows=array_values(array_filter($rows,fn($r)=>$r['loteria']===$lottery));
 if(trim($query)!=='')$rows=array_values(array_filter($rows,fn($r)=>selena_history_matches($r,$query)));
 header('Cache-Control: public, max-age=0, must-revalidate');header('Vercel-CDN-Cache-Control: public, s-maxage=30');
 history_reply(['ok'=>true,'fecha'=>$fecha,'resultados'=>$rows,'cantidad'=>count($rows),'origen'=>'historial','nota'=>'Solo incluye los resultados guardados cuando se consultó la página.']);
}catch(Throwable $e){error_log('Selena: no se pudo leer el historial');history_reply(['ok'=>false,'error'=>'No se pudo consultar el historial. Intenta de nuevo.'],503);}

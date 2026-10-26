<?php
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
function jaulas_reply($data,$status=200){http_response_code($status);header('Cache-Control: '.($status===200?'public, max-age=0, s-maxage=300':'no-store'));echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);exit;}
$names=['lotto-activo'=>'Lotto Activo','guacharoactivo'=>'GuacharoActivo','selvaplus'=>'SelvaPlus','guacharito'=>'Guacharito','la-granjita'=>'La Granjita','lotto-activo-rd'=>'Lotto Activo RD','loto-activo-rd-int'=>'Loto Activo RD INT'];
$id=$_GET['loteria']??'';
if(!is_string($id)||!isset($names[$id]))jaulas_reply(['ok'=>false,'error'=>'Lotería no válida'],400);
$url='https://resultados365.com/estadisticas/'.$id.'/dias-sin-salir/';
try{
 if(!extension_loaded('curl')||!class_exists('DOMDocument'))throw new RuntimeException('Extensiones no disponibles');
 $ch=curl_init($url);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_TIMEOUT=>18,CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_USERAGENT=>'Mozilla/5.0 (compatible; SelenaResultados/1.0; +local-dashboard)',CURLOPT_HTTPHEADER=>['Accept: text/html'],CURLOPT_ENCODING=>'']);
 $html=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
 if($status!==200||!is_string($html)||strlen($html)>8000000)throw new RuntimeException('Fuente no disponible');
 libxml_use_internal_errors(true);$dom=new DOMDocument();$dom->loadHTML('<?xml encoding="utf-8"?>'.$html);libxml_clear_errors();
 $xpath=new DOMXPath($dom);$animals=[];$seen=[];
 foreach($xpath->query('//tr') as $row){
  $cells=$xpath->query('./td',$row);if($cells->length<3)continue;
  $label=trim(preg_replace('/\s+/u',' ',$cells->item(0)->textContent));$last=trim($cells->item(1)->textContent);$days=trim($cells->item(2)->textContent);
  if(!preg_match('/^(\d{1,3})\s*(.+)$/u',$label,$m)||!preg_match('/^\d{2}\/\d{2}\/\d{4}$/',$last)||!preg_match('/^\d{1,5}$/',$days)||isset($seen[$m[1]]))continue;
  $seen[$m[1]]=true;$animals[]=['numero'=>$m[1],'animal'=>$m[2],'dias'=>(int)$days,'ultima_salida'=>$last];
 }
 if(!$animals)throw new RuntimeException('Tabla no disponible');
 usort($animals,fn($a,$b)=>$b['dias']<=>$a['dias']);
 jaulas_reply(['ok'=>true,'loteria'=>$names[$id],'animales'=>$animals,'fuente'=>$url,'consultado_en'=>gmdate('c')]);
}catch(Throwable $e){jaulas_reply(['ok'=>false,'error'=>'No se pudo consultar la estadística. Pulsa Actualizar o abre la fuente.'],502);}

<?php
// API local: consulta informacion publica; no requiere una clave de terceros.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');

require_once __DIR__.'/source_common.php';
function reply($body,$status=200){selena_emit($body,$status);}
selena_cache_start('365');
if (!extension_loaded('curl') || !class_exists('DOMDocument')) reply(['ok'=>false,'error'=>'Activa las extensiones curl y dom de PHP en XAMPP.'],500);
$source='https://resultados365.com/';
$ch=curl_init($source);
curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>3,CURLOPT_TIMEOUT=>16,CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_USERAGENT=>'Mozilla/5.0 (compatible; SelenaResultados/1.0; +local-dashboard)',CURLOPT_HTTPHEADER=>['Accept: text/html','Cache-Control: no-cache'],CURLOPT_ENCODING=>'']);
$html=curl_exec($ch); $error=curl_error($ch); $http=curl_getinfo($ch,CURLINFO_HTTP_CODE); 
if(!$html || $http!==200) reply(['ok'=>false,'error'=>'No se pudo consultar Resultados365 (HTTP '.$http.'). '.$error],502);
libxml_use_internal_errors(true);
$dom=new DOMDocument(); $dom->loadHTML('<?xml encoding="utf-8"?>'.$html); libxml_clear_errors();
$xp=new DOMXPath($dom);
selena_require_today(selena_date($xp->evaluate('string(//input[@id="fecha-picker"]/@value)')));
$names=['Condor Gana','Ruleta Royal','El Granjazo','GuacharoActivo','SelvaPlus','Lotto Activo','Lotto Activo RD','Loto Activo RD INT','La Granjita','CazaLoton','Monje Millonario','Chance Animal B','Chance Animal A','CentenaPlus','CentenaAnimalitos','TropiGana','Guacharito','Lotto Pantera','Ricachona','GranjitaPlus','LotoChaima','Lotto Animalito','PandaPlus','MegaAnimal','Granja Millonaria'];
// Orden largo primero, para evitar reconocer "Lotto Activo" dentro de "Lotto Activo RD".
usort($names,function($a,$b){return strlen($b)-strlen($a);});
function compactText($s) {return trim(preg_replace('/\s+/u',' ',html_entity_decode($s, ENT_QUOTES|ENT_HTML5,'UTF-8')));}
function findLottery($text,$names) {foreach($names as $name){if(preg_match('/^'.preg_quote($name,'/').'\b/iu',$text)) return $name;}return null;}
function resultFromAlt($alt,$names) {
  $alt=compactText($alt);
  $lottery=findLottery($alt,$names);
  if($lottery && preg_match('/^'.preg_quote($lottery,'/').'\s*[-–:]\s*(\d{1,3})\s+(.+)$/iu',$alt,$m)) return [$lottery,$m[1],trim($m[2])];
  foreach($names as $name) if(preg_match('/^(.+?)\s*[-–]\s*(\d{1,3})\s*[-–]\s*'.preg_quote($name,'/').'$/iu',$alt,$m)) return [$name,$m[2],trim($m[1])];
  return null;
}
function nearbyTime($node) {
  for($level=0; $level<4 && $node; $level++,$node=$node->parentNode){
    $str=compactText($node->textContent ?? '');
    if(strlen($str)>420) break;
    if(preg_match('/\b(0?[1-9]|1[0-2])\s*:\s*([0-5]\d)\s*(am|pm)\b/iu',$str,$m)) return sprintf('%d:%s %s',(int)$m[1],$m[2],strtoupper($m[3]));
    if(preg_match('/\b(0?[1-9]|1[0-2])\s*(am|pm)\b/iu',$str,$m)) return (int)$m[1].':00 '.strtoupper($m[2]);
  }
  return '';
}
// Logos de loterías, separados de las imágenes de animales.
$logos=[];
foreach($xp->query('//img[@alt]') as $img){
  $alt=compactText($img->getAttribute('alt'));
  if(!preg_match('/^Logo\s+(.+)$/iu',$alt,$m))continue;
  $name=trim($m[1]);
  $src=trim($img->getAttribute('src'));
  if(!$src)$src=trim($img->getAttribute('data-src'));
  if(str_starts_with($src,'//'))$src='https:'.$src;
  elseif(str_starts_with($src,'/'))$src='https://resultados365.com'.$src;
  $parts=parse_url($src);
  if($parts && ($parts['scheme']??'')==='https' && in_array(strtolower($parts['host']??''),['resultados365.com','www.resultados365.com'],true))$logos[$name]=$src;
}
$results=[]; $seen=[];
foreach($xp->query('//img[@alt]') as $img){
  $parsed=resultFromAlt($img->getAttribute('alt'),$names);
  if(!$parsed) continue;
  [$name,$num,$animal]=$parsed;
  if(preg_match('/^(logo|icon)/iu',$animal)) continue;
  $time=nearbyTime($img);
  $id=mb_strtolower($name.'|'.$num.'|'.$animal.'|'.$time,'UTF-8');
  if(isset($seen[$id])) continue;
  $seen[$id]=true;
  $srcImage=trim($img->getAttribute('src'));if(!$srcImage)$srcImage=trim($img->getAttribute('data-src'));
  if(str_starts_with($srcImage,'//'))$srcImage='https:'.$srcImage;
  elseif(str_starts_with($srcImage,'/'))$srcImage='https://resultados365.com'.$srcImage;
  $parts=parse_url($srcImage);$safeImage=($parts && ($parts['scheme']??'')==='https' && in_array(strtolower($parts['host']??''),['resultados365.com','www.resultados365.com'],true))?$srcImage:'';
  $results[]=['loteria'=>$name,'numero'=>str_pad($num,2,'0',STR_PAD_LEFT),'animal'=>$animal,'hora'=>$time,'imagen'=>$safeImage];
}
// Complemento para HTML que tiene el texto del sorteo pero no alt de imagen.
if(!$results){
  $text=compactText($xp->evaluate('string(//body)'));
  foreach($names as $name){
    $pattern='/'.preg_quote($name,'/').'\s+((?:0?[1-9]|1[0-2])(?::[0-5]\d)?\s*[ap]m)\s+(\d{1,3})\s+([\p{L} ]{2,32})(?=\s|$)/iu';
    if(preg_match($pattern,$text,$m)) $results[]=['loteria'=>$name,'numero'=>str_pad($m[2],2,'0',STR_PAD_LEFT),'animal'=>trim($m[3]),'hora'=>$m[1]];
  }
}
if(!$results) reply(['ok'=>false,'error'=>'La página respondió, pero no se pudieron identificar resultados. Es posible que haya cambiado su estructura.'],502);
reply(['ok'=>true,'fuente'=>$source,'consultado_en'=>gmdate('c'),'resultados'=>$results,'cantidad'=>count($results),'logos'=>$logos]);


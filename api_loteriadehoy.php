<?php
// Fuente independiente; solo se muestran resultados que realmente se puedan extraer.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
function respond($data,$code=200){if(!empty($data['ok'])&&is_array($data['resultados']??null)){require_once __DIR__.'/storage.php';selena_archive('loteriadehoy',$data['resultados']);}http_response_code($code);echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE);exit;}
if(!extension_loaded('curl')||!class_exists('DOMDocument'))respond(['ok'=>false,'error'=>'Habilita curl y dom en PHP.'],500);
$url='https://loteriadehoy.com/animalitos/resultados/';
$ch=curl_init($url);
curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>3,CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_TIMEOUT=>20,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_ENCODING=>'',CURLOPT_USERAGENT=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/124.0 Safari/537.36',CURLOPT_HTTPHEADER=>['Accept: text/html,application/xhtml+xml']]);
$html=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);$error=curl_error($ch);
if(!$html||$code!==200)respond(['ok'=>false,'error'=>'No se pudo consultar Lotería de Hoy (HTTP '.$code.'). '.$error],502);
libxml_use_internal_errors(true);$dom=new DOMDocument();$dom->loadHTML('<?xml encoding="utf-8"?>'.$html);libxml_clear_errors();
$xp=new DOMXPath($dom);
function txt($s){return trim(preg_replace('/\s+/u',' ',html_entity_decode($s,ENT_QUOTES|ENT_HTML5,'UTF-8')));}
function normalized($s){$s=mb_strtolower($s,'UTF-8');$s=iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s);return preg_replace('/[^a-z0-9]/','',$s);}
function lotteryName($s){$v=normalized($s);if(preg_match('/activord(?:int|inter)/',$v))return 'Loto Activo RD INT';if($v==='selvaplus')return 'SelvaPlus';if(str_contains($v,'monjemillonario'))return 'Monje Millonario';if(str_contains($v,'guacharito'))return 'Guacharito';if(str_contains($v,'guacharoactivo'))return 'GuacharoActivo';if(str_contains($v,'granjita')&&!str_contains($v,'plus'))return 'La Granjita';if(str_contains($v,'chaima'))return 'LotoChaima';if(str_contains($v,'rdominicana')||str_contains($v,'activord'))return 'Lotto Activo RD';if($v==='lottoactivo'||$v==='lotoactivo')return 'Lotto Activo';return null;}
function imageURL($node){if(!$node)return '';foreach(['data-original','data-src','src'] as $key){$src=trim($node->getAttribute($key));if(!$src)continue;if(str_starts_with($src,'//'))$src='https:'.$src;elseif(str_starts_with($src,'/'))$src='https://loteriadehoy.com'.$src;$parts=parse_url($src);if($parts && ($parts['scheme']??'')==='https' && in_array(strtolower($parts['host']??''),['loteriadehoy.com','www.loteriadehoy.com'],true))return $src;}return '';}
// La página utiliza un h3 por lotería, h4 con número/animal y h5 con la hora.
$current=null;$pending=null;$results=[];$lastImg='';
foreach($xp->query('//h3|//h4|//h5|//img') as $node){
 $tag=strtolower($node->nodeName);
 if($tag==='h3'){$current=lotteryName(txt($node->textContent));$pending=null;$lastImg='';continue;}
 if(!$current)continue;
 if($tag==='img'){$img=imageURL($node);if($img)$lastImg=$img;continue;}
 if($tag==='h4'){
   $text=txt($node->textContent);
   if(preg_match('/^(\d{1,3})\s+([\p{L}][\p{L}\s-]{1,38})$/u',$text,$m))$pending=['loteria'=>$current,'numero'=>str_pad($m[1],2,'0',STR_PAD_LEFT),'animal'=>trim($m[2]),'imagen'=>$lastImg];
   else $pending=null;
   continue;
 }
 if($tag==='h5'&&$pending){
   $hour=txt($node->textContent);
   if(preg_match('/\b(0?[1-9]|1[0-2]):([0-5]\d)\s*(AM|PM)\b/i',$hour,$m)){
      $pending['hora']=sprintf('%02d:%s %s',(int)$m[1],$m[2],strtoupper($m[3]));
      $results[$current.'|'.$pending['hora']]=$pending;
   }
   $pending=null;$lastImg='';
 }
}
if(!$results)respond(['ok'=>false,'error'=>'La página respondió pero no se pudieron leer resultados. Su estructura puede haber cambiado.'],502);
respond(['ok'=>true,'fuente'=>$url,'resultados'=>array_values($results),'cantidad'=>count($results),'consultado_en'=>gmdate('c')]);


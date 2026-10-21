<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
require_once __DIR__.'/source_common.php';
function answer($body,$status=200){selena_emit($body,$status);}
selena_cache_start('lotoven');
if(!extension_loaded('curl')||!class_exists('DOMDocument'))answer(['ok'=>false,'error'=>'Activa curl y dom en PHP de XAMPP.'],500);
$url='https://lotoven.com/animalitos/';
$ch=curl_init($url);
curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>3,CURLOPT_TIMEOUT=>18,CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_USERAGENT=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/124.0 Safari/537.36',CURLOPT_HTTPHEADER=>['Accept: text/html,application/xhtml+xml'],CURLOPT_ENCODING=>'']);
$html=curl_exec($ch);$err=curl_error($ch);$http=curl_getinfo($ch,CURLINFO_HTTP_CODE);
if(!$html||$http!==200)answer(['ok'=>false,'error'=>'Lotoven no respondió correctamente (HTTP '.$http.'). '.$err],502);
libxml_use_internal_errors(true);$dom=new DOMDocument();$dom->loadHTML('<?xml encoding="utf-8"?>'.$html);libxml_clear_errors();
$xp=new DOMXPath($dom);
function normtxt($v){return trim(preg_replace('/\s+/u',' ',html_entity_decode($v,ENT_QUOTES|ENT_HTML5,'UTF-8')));}
function canonical($v){$v=mb_strtolower(trim($v),'UTF-8');$v=iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$v);$v=preg_replace('/[^a-z0-9]/','',$v);if(preg_match('/activord(?:int|inter)/',$v))return 'Loto Activo RD INT';if($v==='selvaplus')return 'SelvaPlus';if(str_contains($v,'monjemillonario'))return 'Monje Millonario';if(str_contains($v,'guacharito'))return 'Guacharito';if(str_contains($v,'guacharoactivo'))return 'GuacharoActivo';if(str_contains($v,'granjita') && !str_contains($v,'plus'))return 'La Granjita';if(str_contains($v,'chaima'))return 'LotoChaima';if(str_contains($v,'rdominicana')||str_contains($v,'activord'))return 'Lotto Activo RD';if($v==='lottoactivo'||$v==='lotoactivo')return 'Lotto Activo';return null;}
// Cada cabecera «Resultados ...» delimita el bloque de una sola lotería.
// Solo se leen sus propios descendientes y hermanos hasta el siguiente título.
$headers=$xp->query('//h2|//h3|//h4');$found=[];
foreach($headers as $h){
 $heading=normtxt($h->textContent);if(!preg_match('/^Resultados\s+(.+)$/iu',$heading,$m))continue;
 $name=canonical($m[1]);if(!$name)continue;
 $buffer='';$node=$h->nextSibling;$steps=0;
 while($node && $steps++<120){
   if($node->nodeType===XML_ELEMENT_NODE){
     $tag=strtolower($node->nodeName);
     if(in_array($tag,['h2','h3','h4'],true)&&preg_match('/^Resultados\s+/iu',normtxt($node->textContent)))break;
   }
   $buffer.=' '.($node->textContent??'');$node=$node->nextSibling;
 }
 // Alternativa: cuando el título y los sorteos están envueltos en un mismo contenedor,
 // se usan nodos siguientes hasta el próximo título.
 if(!preg_match('/\b\d{1,3}\s+[\p{L}]+\s+\d{1,2}:\d\d\s*[AP]M/iu',$buffer)){
   $next=$xp->query('following::text()', $h);$buffer='';$count=0;
   foreach($next as $t){
     $parent=$t->parentNode;
     if($parent && in_array(strtolower($parent->nodeName),['h2','h3','h4'],true)&&preg_match('/^Resultados\s+/iu',normtxt($parent->textContent)))break;
     $buffer.=' '.($t->nodeValue??'');if(++$count>180)break;
   }
 }
 $buffer=normtxt($buffer);
 if(selena_date($buffer)!==selena_today())continue;
 if(preg_match_all('/(?<!\d)(\d{1,3})\s+([\p{L}][\p{L}\s\-]{1,26}?)\s+((?:0?[1-9]|1[0-2]):[0-5]\d\s*[AP]M)\b/iu',$buffer,$matches,PREG_SET_ORDER)){
   foreach($matches as $m2){$animal=normtxt($m2[2]);$time=strtoupper(preg_replace('/\s+/',' ',trim($m2[3])));if(preg_match('/(?:Resultados|Tuesday|October)/iu',$animal))continue;
     $key=$name.'|'.$time;$found[$key]=['loteria'=>$name,'numero'=>str_pad($m2[1],2,'0',STR_PAD_LEFT),'animal'=>$animal,'hora'=>$time];
   }
 }
}
// Vincular las imágenes publicadas por Lotoven a su lotería y animal.
// Nunca fabricar una URL de imagen: solo usar una encontrada en el HTML.
$images=[];
foreach($xp->query('//img[@alt]') as $img){
  $alt=normtxt($img->getAttribute('alt'));
  if(!preg_match('/^Animalito\s+(.+?)\s+([\p{L}][\p{L}\s-]*)$/iu',$alt,$mm))continue;
  $lot=null;$animalFromAlt='';
  foreach(['Lotto Activo RDominicana','Lotto Activo RD Int','Lotto Activo 2 (Monje Millonario)','El Guacharito Millonario','Selva Plus','Guacharo Activo','Lotto Activo','La Granjita','Loto Chaima'] as $candidate){
    $prefix='Animalito '.$candidate.' ';
    if(mb_strtolower(mb_substr($alt,0,mb_strlen($prefix)))===mb_strtolower($prefix)){
      $lot=canonical($candidate);$animalFromAlt=trim(mb_substr($alt,mb_strlen($prefix)));break;
    }
  }
  if(!$lot||empty($animalFromAlt))continue;
  // Priorizar variantes originales o srcset de mayor resolución en vez de thumbnails.
  $imgUrl=trim($img->getAttribute('data-original'));
  if(!$imgUrl)$imgUrl=trim($img->getAttribute('data-full'));
  $srcset=trim($img->getAttribute('srcset'));
  if(!$srcset)$srcset=trim($img->getAttribute('data-srcset'));
  if(!$imgUrl && $srcset){
    $bestPixels=-1;
    foreach(explode(',',$srcset) as $candidate){
      $partsSet=preg_split('/\s+/',trim($candidate));
      $candidateUrl=$partsSet[0]??'';
      $descriptor=$partsSet[1]??'1x';
      $resolution=preg_match('/^(\d+(?:\.\d+)?)(w|x)$/',$descriptor,$mmRes)?(float)$mmRes[1]:1;
      if($candidateUrl && $resolution>$bestPixels){$bestPixels=$resolution;$imgUrl=$candidateUrl;}
    }
  }
  if(!$imgUrl)$imgUrl=trim($img->getAttribute('data-src'));
  if(!$imgUrl)$imgUrl=trim($img->getAttribute('src'));
  if(str_starts_with($imgUrl,'//'))$imgUrl='https:'.$imgUrl;
  elseif(str_starts_with($imgUrl,'/'))$imgUrl='https://lotoven.com'.$imgUrl;
  $parts=parse_url($imgUrl);
  if(!$parts||($parts['scheme']??'')!=='https'||!in_array(strtolower($parts['host']??''),['lotoven.com','www.lotoven.com'],true))continue;
  $key=$lot.'|'.preg_replace('/[^a-z0-9]/','',strtolower(iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$animalFromAlt)));
  $images[$key]=$imgUrl;
}
foreach($found as &$entry){
  $key=$entry['loteria'].'|'.preg_replace('/[^a-z0-9]/','',strtolower(iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$entry['animal'])));
  $entry['imagen']=$images[$key]??'';
}unset($entry);
if(!$found)answer(['ok'=>false,'error'=>'Lotoven respondió pero su estructura no permitió extraer los sorteos. No se mostrarán valores de otra fuente.'],502);
answer(['ok'=>true,'fuente'=>$url,'resultados'=>array_values($found),'cantidad'=>count($found),'consultado_en'=>gmdate('c')]);



<?php
// Únicas loterías habilitadas en todo el servidor.
function selena_loterias(){return ['Lotto Activo','GuacharoActivo','Lotto Activo RD','La Granjita','SelvaPlus','Monje Millonario','Guacharito','Loto Activo RD INT'];}
function selena_normalized($value){$v=iconv('UTF-8','ASCII//TRANSLIT//IGNORE',(string)$value);return strtolower(preg_replace('/[^a-zA-Z0-9]/','',$v));}
function selena_loteria($value){$key=selena_normalized($value);foreach(selena_loterias() as $name)if(selena_normalized($name)===$key)return $name;return null;}
function selena_numero($number,$animal=''){
 $raw=trim((string)$number);$name=selena_normalized($animal);
 if(preg_match('/^0+$/',$raw)){if($name==='delfin')return '0';if($name==='ballena')return '00';return $raw==='0'?'0':'00';}
 return ctype_digit($raw)?str_pad((string)(int)$raw,2,'0',STR_PAD_LEFT):$raw;
}
function selena_filtrar($results){$filtered=[];foreach($results as $r){$name=selena_loteria($r['loteria']??'');if(!$name||empty($r['hora']))continue;$r['loteria']=$name;$r['numero']=selena_numero($r['numero']??'',$r['animal']??'');if($r['numero']==='')continue;$filtered[]=$r;}return $filtered;}

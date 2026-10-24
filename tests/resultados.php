<?php
require dirname(__DIR__).'/source_common.php';
function check($condition,$message){if(!$condition)throw new Exception($message);}
foreach([['0','Delfín','0'],['00','Delfin','0'],['0','Ballena','00'],['00','Ballena','00'],['1','Carnero','01']] as [$n,$a,$expected])check(selena_numero($n,$a)===$expected,'Ceros: '.$a);
$rows=[];foreach(selena_loterias() as $name)$rows[]=['loteria'=>$name,'numero'=>'00','animal'=>'Delfin','hora'=>'10:00 AM'];$rows[]=['loteria'=>'LotoChaima','numero'=>'25','animal'=>'Gallina','hora'=>'10:00 AM'];
$filtered=selena_filtrar($rows);check(count($filtered)===8,'Filtro de ocho');foreach($filtered as $row)check($row['numero']==='0','Delfin sin relleno');
check(!selena_cache_fresh(['ok'=>true,'fecha'=>'2000-01-01','consultado_en'=>gmdate('c')]),'Ayer no sirve');
check(selena_cache_fresh(['ok'=>true,'fecha'=>selena_today(),'consultado_en'=>gmdate('c',time()-30)]),'Reutiliza reciente');
check(!selena_cache_fresh(['ok'=>true,'fecha'=>selena_today(),'consultado_en'=>gmdate('c',time()-61)]),'Caduca a 60 segundos');
check(!selena_cache_fresh(['ok'=>true,'fecha'=>selena_today(),'consultado_en'=>gmdate('c',time()+60)]),'No acepta futuro');
check(selena_store_key('resultados','365')!==selena_store_key('resultados','lotoven'),'Fuentes independientes');
check(!selena_store_configured(),'No afirma base conectada sin credenciales');
echo "PASS: PHP eight-lottery filter, dolphin/whale, dates, 60s cache expiry and storage configuration.\n";

<?php
function selena_db(): ?PDO {
 $config=__DIR__.'/config.php'; if(!is_file($config)||!extension_loaded('pdo_mysql'))return null;
 try{$c=require $config;return new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4",$c['username'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_TIMEOUT=>2]);}catch(Throwable $e){error_log('Selena DB: '.$e->getMessage());return null;}
}
function selena_archive(string $source,array $results): void {
 $db=selena_db(); if(!$db)return;
 // Los sitios entregan sorteos del dia local de Venezuela; no adivinar dias de otros reportes.
 $date=(new DateTimeImmutable('now',new DateTimeZone('America/Caracas')))->format('Y-m-d');
 try{$st=$db->prepare('INSERT INTO resultados (fecha,proveedor,loteria,hora,numero,animal,imagen) VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE animal=VALUES(animal),imagen=VALUES(imagen),actualizado_en=CURRENT_TIMESTAMP');
 foreach($results as $r){$lot=trim((string)($r['loteria']??''));$time=trim((string)($r['hora']??''));$num=trim((string)($r['numero']??''));if(!$lot||!$time||!preg_match('/^\d{1,3}$/',$num))continue;$st->execute([$date,$source,mb_substr($lot,0,100),mb_substr($time,0,20),$num,mb_substr((string)($r['animal']??''),0,100),(string)($r['imagen']??'')]);}}catch(Throwable $e){error_log('Selena archive: '.$e->getMessage());}
}

(() => {
'use strict';
const $=id=>document.getElementById(id), normalize=s=>(s||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase().replace(/[^a-z0-9]/g,'');
// Tabla de equivalencias aportada por el usuario: El Guacharito Millonario.
// No reutilizar estos números para otras tablas sin verificar sus reglas.
const ANIMALES_GUACHARITO = {"0": "Delfín", "01": "Carnero", "02": "Toro", "03": "Ciempiés", "04": "Alacrán", "05": "León", "06": "Rana", "07": "Perico", "08": "Ratón", "09": "Águila", "10": "Tigre", "11": "Gato", "12": "Caballo", "13": "Mono", "14": "Paloma", "15": "Zorro", "16": "Oso", "17": "Pavo", "18": "Burro", "19": "Chivo", "20": "Cerdo", "21": "Gallo", "22": "Camello", "23": "Cebra", "24": "Iguana", "25": "Gallina", "26": "Vaca", "27": "Perro", "28": "Zamuro", "29": "Elefante", "30": "Caimán", "31": "Lapa", "32": "Ardilla", "33": "Pescado", "34": "Venado", "35": "Jirafa", "36": "Culebra", "37": "Tortuga", "38": "Búfalo", "39": "Lechuza", "40": "Avispa", "41": "Canguro", "42": "Tucán", "43": "Mariposa", "44": "Chigüire", "45": "Garza", "46": "Puma", "47": "Pavo real", "48": "Puercoespín", "49": "Pereza", "50": "Canario", "51": "Pelícano", "52": "Pulpo", "53": "Caracol", "54": "Grillo", "55": "Hormiguero", "56": "Tiburón", "57": "Pato", "58": "Hormiga", "59": "Pantera", "60": "Camaleón", "61": "Panda", "62": "Cachicamo", "63": "Cangrejo", "64": "Gavilán", "65": "Araña", "66": "Lobo", "67": "Avestruz", "68": "Jaguar", "69": "Conejo", "70": "Bisonte", "71": "Guacamaya", "72": "Gorila", "73": "Hipopótamo", "74": "Turpial", "75": "Guácharo", "76": "Rinoceronte", "77": "Pingüino", "78": "Antílope", "79": "Calamar", "80": "Murciélago", "81": "Cuervo", "82": "Cucaracha", "83": "Búho", "84": "Camarón", "85": "Hámster", "86": "Buey", "87": "Cabra", "88": "Erizo de mar", "89": "Anguila", "90": "Hurón", "91": "Morrocoy", "92": "Cisne", "93": "Gaviota", "94": "Paujil", "95": "Escarabajo", "96": "Caballito de mar", "97": "Loro", "98": "Cocodrilo", "99": "Guacharito", "00": "Ballena"};
const animalKey = value => (value||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase().replace(/[^a-z0-9 ]/g,' ').replace(/\s+/g,' ').trim();
const numeroPorAnimal = Object.fromEntries(Object.entries(ANIMALES_GUACHARITO).map(([n,nombre])=>[animalKey(nombre),n]));
const animalNames = Object.keys(numeroPorAnimal).sort((a,b)=>b.length-a.length);
const animalPattern = new RegExp('\\b(?:' + animalNames.map(x=>x.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')).join('|').replace(/ /g,'\\s+') + ')\\b','gi');
function numerosEnJugada(fragmento) {
 // Un número seguido por su nombre representa una jugada, no dos.
 let piece=animalKey(fragmento);
 let result=[];
 const tokenRe=/\b\d{1,2}\b|\b[a-z]+(?: [a-z]+){0,3}\b/g;
 let tokens=piece.match(tokenRe)||[];
 // Procesar nombres largos antes que palabras simples.
 const combined=piece.match(new RegExp('\\b(?:\\d{1,2}|'+animalNames.map(v=>v.replace(/ /g,'\\s+')).join('|')+')\\b','gi'))||[];
 for(let i=0;i<combined.length;i++){
  const token=animalKey(combined[i]);
  if(/^\d{1,2}$/.test(token)){
   const n=token==='0'?'0':token==='00'?'00':token.padStart(2,'0');
   result.push(n);
   if(i+1<combined.length && numeroPorAnimal[animalKey(combined[i+1])]===n)i++;
  } else if(numeroPorAnimal[token]!==undefined)result.push(numeroPorAnimal[token]);
 }
 return result;
}
function lottery(s){
  // Alias del usuario; evitar que RD INT se mezcle con RD o Lotto Activo.
  const raw=(s||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase()
    .replace(/^[\s*`#]+|[\s*`#]+$/g,'').trim();
  const q=normalize(raw).replace(/^tripleta/, '');
  if(/(?:loto|lotto)activordint|lactrdint|(?:loto|lotto)activoint|lotoactivoint/.test(q))return 'Lotto Activo RD INT';
  if(/(?:loto|lotto)activord|lactrd/.test(q))return 'Lotto Activo RD';
  if(/guacharito/.test(q))return 'Guacharito Millonario';
  if(/guacharo(?:activo|act)?/.test(q))return 'GuacharoActivo';
  if(/condor(?:gana)?/.test(q))return 'CondorGana';
  if(/(?:lagranjita|granjita|granja)/.test(q))return 'La Granjita';
  if(/selva(?:plus)?/.test(q))return 'SelvaPlus';
  if(/monje/.test(q))return 'Monje Millonario';
  if(/chaima/.test(q))return 'LotoChaima';
  if(/(?:loto|lotto)(?:activo|act)?|lact/.test(q))return 'Lotto Activo';
  // Alias cortos solo como palabra independiente, nunca como letra en otra palabra.
  const words=raw.replace(/[^a-z0-9]+/g,' ').trim().split(/\s+/);
  const first=words[0]==='dame'||words[0]==='juega'||words[0]==='pon'||words[0]==='coloca'?words[1]:words[0];
  if(first==='int'||first==='inter')return 'Lotto Activo RD INT';
  if(first==='l'||first==='activo')return 'Lotto Activo';
  if(first==='g')return 'La Granjita';
  if(first==='s')return 'SelvaPlus';
  return null;
}
function time(s){const span=(s||'').match(/(\d{1,2})\s*am\s*[-–]\s*(\d{1,2})\s*pm/i);if(span)return `RANGO ${span[1]}AM-${span[2]}PM`;const raw=(s||'').replace(/\b(\d{1,2})\s*(am|pm)\b/ig,'$1:00 $2');let m=raw.match(/\b(\d{1,2}):(\d{2})\s*(AM|PM)\b/i);if(m){let h=+m[1]%12+(m[3].toUpperCase()==='PM'?12:0);return String(h).padStart(2,'0')+':'+m[2]}let range=raw.match(/\b(\d{1,2})\s*am\s*[-–]\s*(\d{1,2})\s*pm\b/i);if(range)return `RANGO ${range[1]}AM-${range[2]}PM`;return ''}
const amount=s=>{s=s.trim();const comma=s.lastIndexOf(','),dot=s.lastIndexOf('.');if(comma>=0&&dot>=0)return Number(comma>dot?s.replace(/\./g,'').replace(',','.'):s.replace(/,/g,''));if(comma>=0)return Number(s.replace(/\./g,'').replace(',','.'));if(dot>=0){const parts=s.split('.');return Number(parts.length>2?parts.join(''):s)}return Number(s)};
// Normalize play separators only: ticket dates, totals and decimal stakes remain intact.
function normalizeTicketText(text){
 return String(text||'').replace(/\r/g,'').split('\n').map(line=>{
  if(!/[x×]\s*\d/i.test(line))return line;
  const protectedValues=[];
  const protect=value=>{const token='§'+String.fromCharCode(65+protectedValues.length)+'§';protectedValues.push([token,value]);return token;};
  let clean=line.replace(/\b(?:\d{4}-\d{2}-\d{2}|\d{2}\/\d{2}\/\d{4}|\d{1,2}\s*am\s*[-–]\s*\d{1,2}\s*pm)\b/gi,protect);
  clean=clean.replace(/[x×]\s*(\d[\d.,]*)/gi,(_,raw)=>' '+protect('x '+amount(raw.replace(/[.,]+$/,'')).toFixed(2))+' ');
  clean=clean.replace(/[,./_\-]+/g,' ');
  for(const [token,value] of protectedValues)clean=clean.replace(token,value);
  return clean.replace(/[ \t]+/g,' ').trim();
 }).join('\n');
}
function parse(text){const out={entries:[],unclear:[],dates:[],heads:[],total:null};let context=null;let lines=normalizeTicketText(text).split('\n');for(let i=0;i<lines.length;i++){
 let line=lines[i].trim();if(!line||/^[\s*`=\-_]+$/.test(line))continue;
 let day=line.match(/\b(\d{4}-\d{2}-\d{2}|\d{2}\/\d{2}\/\d{4})\b/);if(day){let d=day[1];out.dates.push(d.includes('-')?d.split('-').reverse().join('/'):d)}
 let tot=line.match(/(?:total(?:\s+ticket)?(?:\s*\([^)]*\))?\s*(?:ves|bsd|bs)?\s*[:.]?\s*)([\d.,]+)/i);if(tot)out.total=amount(tot[1]);
 const hasX=/\b[x×]\s*\d/i.test(line),lot=lottery(line);
 if(lot&&!hasX){let raw=line.replace(/^[*`\s]+|[*`\s]+$/g,'');context={lot,time:time(raw),mode:/tripleta/i.test(raw)?'tripleta':'normal',label:raw};out.heads.push(context);continue}
 if(/^(agencia|n\/t|fecha:|ticket:|total\b|verifi|no nos|despues|después|\[reimpresi|selena|hora:)/i.test(line)|| /^\d{2}\/\d{2}\/\d{4}\b/.test(line)||/^\*?\*?verificar/i.test(line))continue;
 if(!hasX){if(/\d/.test(line)&&!/^[-=*]+$/.test(line)&&context)out.unclear.push({line:i+1,text:line,reason:'Línea con números no interpretada'});continue}
 if(!context && lot && hasX){ context={lot,time:time(line),mode:/tripleta/i.test(line)?'tripleta':'normal',label:line}; line=line.replace(/^(?:(?:dame|juega|pon|coloca)\s+)?(?:loto\s*activo\s*rd\s*int|loto\s*activo\s*rd|loto\s*act(?:ivo)?|lotto\s*activo|loto|lotto|activo|la\s*granjita|granjita|granja|selva(?:\s*plus)?|condor(?:\s*gana)?|guacharito(?:\s*millonario)?|guacharo(?:\s*activo|act)?|int|inter|l|g|s)\b\s*/i,''); }
 if(!context){context={lot:'Sin especificar',time:'',mode:'normal',label:'Lotería no indicada'};out.unclear.push({line:i+1,text:line,reason:'Falta indicar la lotería; se interpretan los animales, pero debes revisar el sorteo'});}
 let remainder=line,found=0;const make=(nums,price)=>{let normalized=nums.map(n=>n==='0'?'0':n.padStart(2,'0'));if(context.mode==='tripleta'){if(normalized.length!==3){out.unclear.push({line:i+1,text:line,reason:'Tripleta sin tres números'});return}out.entries.push({lot:context.lot,draw:context.time,mode:'tripleta',nums:normalized.join('-'),amount:price,line:i+1})}else for(let num of normalized)out.entries.push({lot:context.lot,draw:context.time,mode:'normal',nums:num,amount:price,line:i+1});found++};
 // Find every X amount, and interpret the sequence immediately before it. Multiple stakes per line are supported.
 const re=/\b[x×]\s*([\d][\d.,]*)/ig;let m,start=0;while((m=re.exec(line))!==null){let piece=line.slice(start,m.index);let numbers=numerosEnJugada(piece);if(!numbers.length){out.unclear.push({line:i+1,text:piece+' X '+m[1],reason:'Monto sin números'});}else if(numbers.some(n=>+n>99)){out.unclear.push({line:i+1,text:piece,reason:'Número no válido'})}else make(numbers,amount(m[1]));start=re.lastIndex}
 if(!found&& !out.unclear.some(u=>u.line===i+1))out.unclear.push({line:i+1,text:line,reason:'No se reconoció la jugada'});
 }return out}
const id=e=>[e.lot,e.draw,e.mode,e.nums].join('|'), group=entries=>{let map=new Map();for(let e of entries){let k=id(e);let val=map.get(k)||{...e,amount:0};val.amount+=e.amount;map.set(k,val)}return map},fmt=e=>`${e.lot} · ${e.draw||'sin hora'} · ${e.mode==='tripleta'?'Tripleta ':''}${e.nums} · Bs ${e.amount.toFixed(2)}`;
function diff(a,b){
 const list=[];
 const fmtBs=n=>`Bs ${n.toFixed(2)}`;
 if(a.dates[0]&&b.dates[0]&&a.dates[0]!==b.dates[0])list.push({type:'bad',head:'Fecha equivocada',detail:`El ticket original es del ${a.dates[0]} y el registrado del ${b.dates[0]}.`});
 const orig=[...group(a.entries).values()],reg=[...group(b.entries).values()];
 const remaining=new Set(reg.map((_,i)=>i));
 const match=(o,test)=>{for(const i of remaining){if(test(o,reg[i])){remaining.delete(i);return reg[i]}}return null};
 const drawsMatch=(x,y)=>!x.draw||!y.draw||x.draw===y.draw;
 const numsMatch=(x,y)=>x.mode===y.mode&&x.nums===y.nums;
 const used=[];
 for(const o of orig){
  const exact=match(o,(x,y)=>x.lot===y.lot&&numsMatch(x,y)&&drawsMatch(x,y));
  if(exact){if(Math.abs(o.amount-exact.amount)>0.001)list.push({type:'warn',head:'Monto equivocado',kind:'amount',lot:o.lot,draw:o.draw,mode:o.mode,number:o.nums,asked:o.amount,entered:exact.amount});continue}
  const wrongLot=match(o,(x,y)=>numsMatch(x,y)&&drawsMatch(x,y)&&x.lot!==y.lot);
  if(wrongLot){list.push({type:'bad',head:'Lotería equivocada',kind:'lottery',lot:o.lot,otherLot:wrongLot.lot,draw:o.draw,mode:o.mode,number:o.nums,asked:o.amount,entered:wrongLot.amount});continue}
  const wrongNum=match(o,(x,y)=>x.lot===y.lot&&x.mode===y.mode&&drawsMatch(x,y));
  if(wrongNum){list.push({type:'bad',head:'Número incorrecto',detail:`¡Atención! En ${o.lot}, registraste el número ${wrongNum.nums}, pero te pidieron el ${o.nums}.${o.amount!==wrongNum.amount?' También está mal el monto: pidieron '+fmtBs(o.amount)+' y registraste '+fmtBs(wrongNum.amount)+'.':''}`});continue}
  const wrongTime=match(o,(x,y)=>x.lot===y.lot&&numsMatch(x,y));
  if(wrongTime){list.push({type:'bad',head:'Sorteo equivocado',detail:`¡Atención! El número ${o.nums} de ${o.lot} pertenece a otro sorteo. Comprueba el horario del ticket original.`});continue}
  list.push({type:'bad',head:'Jugada omitida',detail:`¡Atención! Olvidaste registrar el número ${o.nums} de ${o.lot}, por ${fmtBs(o.amount)}.`});
 }
 for(const i of remaining){const r=reg[i];list.push({type:'bad',head:'Jugada adicional',detail:`¡Atención! Registraste el número ${r.nums} en ${r.lot}, por ${fmtBs(r.amount)}, pero no aparece en el pedido original.`})}
 for(const u of a.unclear)list.push({type:'warn',head:'No se entendió el pedido',detail:`Línea ${u.line}: ${u.text}. ${u.reason}. Verifica manualmente antes de confirmar.`});
 for(const u of b.unclear)list.push({type:'warn',head:'No se entendió el ticket registrado',detail:`Línea ${u.line}: ${u.text}. ${u.reason}.`});
 for(const [ticket,caption] of [[a,'original'],[b,'registrado']])if(ticket.total!=null&&Math.abs(ticket.entries.reduce((sum,e)=>sum+e.amount,0)-ticket.total)>.02)list.push({type:'warn',head:'Total por revisar',detail:`En el ticket ${caption}, el total dice ${fmtBs(ticket.total)}, pero las jugadas interpretadas suman ${fmtBs(ticket.entries.reduce((sum,e)=>sum+e.amount,0))}.`});
 // Agrupar avisos repetidos de la misma jugada (misma lotería, sorteo y montos).
 // No unir jugadas de distintos horarios, modalidades o cantidades.
 const combined=[], buckets=new Map();
 for(const item of list){
  if(item.kind!=='amount' && item.kind!=='lottery'){combined.push(item);continue;}
  const key=JSON.stringify([item.kind,item.lot,item.otherLot||'',item.draw,item.mode,item.asked,item.entered]);
  let group=buckets.get(key);
  if(!group){group={...item,numbers:[]};buckets.set(key,group);combined.push(group)}
  group.numbers.push(item.number);
 }
 for(const item of combined){
  if(item.kind==='amount')item.detail=`En ${item.lot}, el número ${item.numbers.join(' ')} lo pidieron por ${fmtBs(item.asked)} y lo pusiste por ${fmtBs(item.entered)}.`;
  if(item.kind==='lottery'){const loteriaPedida=item.otherLot; const loteriaColocada=item.lot; item.detail=`¡Pilas ahí, menol! Te pidieron los números ${item.numbers.join(' ')} para ${loteriaPedida} y los pusiste en ${loteriaColocada}.${Math.abs(item.asked-item.entered)>.001?' Además, el monto en la caja 1 es '+fmtBs(item.asked)+' y en la caja 2 es '+fmtBs(item.entered)+'.':''}`;}
 }
 return combined;
}
function updateReaction(count, ready){
 const box=$('verifierReactionBox'), img=$('verifierReactionImage'),label=$('verifierReactionText');
 if(!box||!img||!label)return;
 // La reacción solo aparece cuando ambos campos contienen contenido.
 box.hidden=!ready;
 label.hidden=!ready;
 if(!ready)return;
 let src, alt, mensaje;
 if(count===0){src='assets/verificador/sin_errores_animado.gif';alt='Gato celebrando que coinciden las jugadas';mensaje='¡Coinciden las jugadas!';}
 else if(count<=2){src='assets/verificador/error_nuevo.png';alt='Reacción al detectar uno o dos errores';mensaje=`Atención: ${count} observación(es)`;}
 else{src='assets/verificador/tres_o_mas_errores.gif';alt='Reacción al detectar tres o más errores';mensaje=`Revisa el ticket: ${count} observación(es)`;}
 if(img.getAttribute('src')!==src)img.setAttribute('src',src);
 img.alt=alt;
 label.textContent=mensaje;
}
let report='';function update(){let t1=$('original').value,t2=$('registered').value;if(!t1.trim()||!t2.trim()){$('summary').textContent='Pega los dos textos para comparar.';$('diffs').replaceChildren();report='';updateReaction(0,false);return}let a=parse(t1),b=parse(t2),ds=diff(a,b);updateReaction(ds.length,true);$('summary').textContent=ds.length?`${ds.length} observación(es) · ${a.entries.length} jugadas originales y ${b.entries.length} registradas`:`✓ Coinciden ${a.entries.length} jugadas. Revisa visualmente los tickets.`;$('summary').className='summary '+(ds.length?'bad':'good');const area=$('diffs');area.replaceChildren();for(let d of ds){let node=document.createElement('div');node.className='line';let title=document.createElement('b');title.className=d.type;title.textContent=(d.type==='bad'?'✖ ':'⚠ ')+d.head;let content=document.createElement('div');content.textContent=d.detail;content.style.whiteSpace='pre-wrap';node.append(title,content);if(d.kind==='amount'||d.kind==='lottery')node.classList.add('grouped-error');area.append(node)}report=[`VERIFICADOR AG SELENA`,`${ds.length} observación(es)`,...ds.map(d=>`${d.head}\n${d.detail}`)].join('\n------------------------------\n')}
for(const field of [$('original'),$('registered')])field.addEventListener('paste',event=>{
 const text=event.clipboardData?.getData('text/plain');if(text===undefined)return;
 event.preventDefault();field.setRangeText(normalizeTicketText(text),field.selectionStart,field.selectionEnd,'end');update();
});
$('original').addEventListener('input',update);$('registered').addEventListener('input',update);$('clear').onclick=()=>{$('original').value='';$('registered').value='';update()};$('demo').onclick=()=>{$('original').value=`Agencia: agencia selena3\n20/10/2026 06:21:01 PM\nLotoAct 07:00 PM\n04,06,33,22,23 X200.00\nLaGranjita 07:00 PM\n04,06,33,22,23 X200.00\nTOTAL TICKET VES:2000.00`;$('registered').value=`LotoActivo 07:00 PM\n04,06,33,22 X200.00\nLaGranjita 07:00 PM\n04,06,33,22,23 X200.00`;update()};$('copy').onclick=async()=>{if(!report)return;try{await navigator.clipboard.writeText(report);$('copyStatus').textContent='Reporte copiado.'}catch(e){$('copyStatus').textContent='No se pudo copiar automáticamente. Prueba en localhost o revisa los permisos del navegador.'}};update();
})();
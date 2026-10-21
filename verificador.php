<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#0b141a"><link rel="icon" href="favicon.png"><title>Verificador de tickets · Agencia Selena</title><style>
:root{color-scheme:dark;font-family:system-ui,-apple-system,Segoe UI,sans-serif;background:#0b141a;color:#e9edef}*{box-sizing:border-box}body{margin:0}.wrap{max-width:1280px;margin:0 auto;padding:16px 20px 60px}header{display:flex;align-items:center;gap:14px;flex-wrap:wrap;padding:10px 0 24px;border-bottom:1px solid #2a3942}header img{width:52px;height:52px;object-fit:contain}header h1{font-size:22px;margin:0}header p{margin:3px 0;color:#a0b6be;font-size:13px}a{color:#81e8b4;text-decoration:none}.back{margin-left:auto;border:1px solid #36564d;border-radius:10px;padding:10px 14px}.intro{color:#acbec3;font-size:14px;line-height:1.6;margin:20px 0}.calc-panel{margin-top:34px;padding:18px 20px}.calc-layout{display:grid;grid-template-columns:minmax(240px,2fr) minmax(230px,1fr);gap:20px;align-items:start}.calc-panel textarea{min-height:104px;max-height:250px}.calc-output{border:1px solid #35554f;border-radius:12px;padding:14px;background:#142d2a}.calc-total{font-size:27px;color:#53e69c;font-weight:850;font-variant-numeric:tabular-nums}.calc-row{font-size:13px;border-top:1px solid #29443f;padding:7px 0;overflow-wrap:anywhere}.calc-help{font-size:12px;color:#a7c4b9;margin-top:7px}.calc-warning{color:#ffc774;font-size:12px;margin-top:9px}.columns{display:grid;grid-template-columns:1fr 1fr 1.1fr;gap:14px;margin-top:48px}.reaction-box[hidden]{display:none!important}.reaction-box{display:flex;align-items:center;justify-content:center;min-height:170px;margin:0 0 18px}.reaction-box img{display:block;width:min(100%,260px);height:170px;object-fit:contain;border-radius:12px}.reaction-label{text-align:center;font-size:12px;color:#a0b6be;margin:-7px 0 18px}.panel{background:#111b21;border:1px solid #2b4246;border-radius:16px;padding:16px;min-width:0}.panel h2{font-size:16px;margin:0 0 8px}.hint{font-size:12px;color:#9bb2b9;margin:0 0 12px;line-height:1.5}textarea{display:block;width:100%;min-height:400px;resize:vertical;background:#172a30;border:1px solid #385058;border-radius:10px;padding:13px;color:#f2f6f6;font:13px/1.6 ui-monospace,Consolas,monospace;outline:0}textarea:focus{border-color:#25d366}.actions{display:flex;gap:9px;flex-wrap:wrap;margin-top:13px}button{border:1px solid #425a60;border-radius:9px;background:#24363c;color:#eff6f2;padding:10px 13px;font-weight:700;cursor:pointer}.primary{background:#008069;border-color:#008069}button:hover{filter:brightness(1.12)}.line{border-bottom:1px solid #2d4045;padding:10px 0;font-size:13px;line-height:1.45}.line b{display:block;margin-bottom:3px}.grouped-error{overflow-wrap:anywhere}.grouped-error b{display:inline;margin:0 6px 0 0}.grouped-error div{display:inline}.bad{color:#ff8e91}.warn{color:#f5ca68}.good{color:#67eaa4}.neutral{color:#a0b6be}.meta{font-size:12px;color:#a0b6be}.summary{padding:12px;border-radius:10px;background:#1a3331;margin-bottom:10px;font-weight:700}.result-scroll{max-height:500px;overflow:auto}.pill{font-size:11px;background:#25433e;padding:3px 7px;border-radius:6px}.unsupported{font-size:12px;color:#ffd48b}.status{padding:8px 0;font-size:12px;color:#8ff0bb}@media(max-width:700px){.calc-layout{grid-template-columns:1fr}}@media(max-width:980px){.columns{grid-template-columns:1fr 1fr;margin-top:38px}.panel:last-child{grid-column:1/-1}textarea{min-height:300px}}@media(max-width:600px){.wrap{padding:12px}.columns{grid-template-columns:1fr;margin-top:24px}.panel:last-child{grid-column:auto}header h1{font-size:18px}.back{margin-left:0}textarea{min-height:260px}}
</style></head><body><div class="wrap"><header><img src="logo_agencia_selena.png" alt="Logo Agencia Selena"><div><h1>Verificador de tickets</h1><p>Agencia Selena · Compara jugadas, sorteo, lotería y montos</p></div><a class="back" href="index.php">← Volver a resultados</a></header><p class="intro">Pega el ticket o jugadas recibidas en la primera caja y lo que registraste en la segunda. El sistema identifica formatos con números separados por comas, varias apuestas en una línea y tripletas. <strong>Revisa siempre las líneas no reconocidas antes de confirmar.</strong></p><div class="columns"><section class="panel"><h2>1 · Jugadas o ticket original</h2><p class="hint">Lo solicitado por el cliente. Puedes pegar el ticket completo, con agencia, fecha y total.</p><textarea id="original" spellcheck="false" placeholder="Pega aquí el ticket del cliente…"></textarea><div class="actions"><button id="demo" type="button">Cargar ejemplo</button></div></section><section class="panel"><h2>2 · Lo que registramos</h2><p class="hint">Pega el ticket emitido o la transcripción de las jugadas que procesaste.</p><textarea id="registered" spellcheck="false" placeholder="Pega aquí lo que hiciste…"></textarea><div class="actions"><button id="clear" type="button">Limpiar ambas cajas</button></div></section><section class="panel"><div class="reaction-box" id="verifierReactionBox" hidden><img id="verifierReactionImage" src="assets/verificador/sin_errores_animado.gif" alt="Reacción del verificador"></div><p id="verifierReactionText" class="reaction-label" aria-live="polite">Esperando los tickets para verificar.</p><h2>3 · Diferencias detectadas</h2><p class="hint">Comparación por modalidad, lotería, horario, combinación y monto. El orden de los números normales no altera la comparación.</p><div id="summary" class="summary neutral">Pega los dos textos para comenzar.</div><div id="diffs" class="result-scroll" aria-live="polite"></div><div class="actions"><button class="primary" id="copy" type="button">Copiar reporte</button></div><div id="copyStatus" class="status"></div></section></div><section class="panel calc-panel" aria-labelledby="calcTitle"><h2 id="calcTitle">4 · Calculadora de jugadas</h2><p class="hint">Escribe los números y el monto para calcular el total. Ejemplo: <b>loto: 25 14 x 50</b> = <b>Bs 100,00</b>; <b>loto y granja: 25 14 x 50</b> = <b>Bs 200,00</b>.</p><div class="calc-layout"><div><textarea id="calcInput" spellcheck="false" placeholder="granja 10 5 17 35 23 32 21 14 25 x 500  0 por 700&#10;loto 17 33 por 2000  5 4 22 7 27 x 200  15 por 1500"></textarea><div class="calc-help">Puedes escribir números o nombres de animalitos, con o sin tildes. Escribe las loterías y las jugadas en la misma línea o debajo. «por», «x» y «×» significan lo mismo. Puedes escribir varios grupos seguidos: 25 14 x 50 0 por 100. Acepta «loto», «granja», «loto y granja», «loto/granja» o «loto granja». Cada jugada se multiplica por las loterías indicadas; sin encabezado se cuenta una lotería. «Para guácharo x 500» repite las selecciones del grupo anterior con el nuevo monto.</div><div class="actions"><button id="calcClear" type="button">Limpiar calculadora</button></div></div><div class="calc-output"><div class="hint">TOTAL DE JUGADAS</div><div class="calc-total" id="calcTotal">Bs 0,00</div><div class="calc-help" id="calcCount">0 selecciones · 0 líneas</div><div id="calcBreakdown" aria-live="polite"></div><div class="calc-warning" id="calcWarnings" aria-live="polite"></div></div></div></section></div><script src="verificador.js?v=20261021-caja2-fix"></script><script>
(function(){
  'use strict';
  const input=document.getElementById('calcInput');
  const totalEl=document.getElementById('calcTotal');
  const countEl=document.getElementById('calcCount');
  const details=document.getElementById('calcBreakdown');
  const warnings=document.getElementById('calcWarnings');
  const money=v=>'Bs '+v.toLocaleString('es-VE',{minimumFractionDigits:2,maximumFractionDigits:2});
  function parseMoney(s){
    s=s.trim().replace(/\s/g,'');
    if(s.includes(',')&&s.includes('.')){
      const lastComma=s.lastIndexOf(',')>s.lastIndexOf('.');
      s=lastComma?s.replace(/\./g,'').replace(',','.'):s.replace(/,/g,'');
    }else if(s.includes(',')){
      const bits=s.split(',');s=bits.length===2&&bits[1].length===3?s.replace(/,/g,''):s.replace(',','.');
    }else if(/^\d{1,3}(?:\.\d{3})+$/.test(s))s=s.replace(/\./g,'');
    return Number(s);
  }
  const cleanPlayText=s=>String(s).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').trim();
  const lotteryAliases=[
    ['INT',/^(?:(?:lotto?|loto)\s*(?:activo\s*)?(?:rd\s*)?(?:int(?:ernacional)?|inter)|inter|int)(?=$|[\s,;:/+&|.-])/],
    ['RD',/^(?:(?:lotto?|loto)\s*(?:activo\s*)?(?:r\.?\s*d\.?|rdominicana|republica\s+dominicana)|rd)(?=$|[\s,;:/+&|.-])/],
    ['GM',/^(?:granja\s*millonaria|gm)(?=$|[\s,;:/+&|.-])/],
    ['Chaima',/^(?:(?:lotto?|loto)\s*chaima|chaima)(?=$|[\s,;:/+&|.-])/],
    ['Guacharito',/^(?:el\s+)?guacharito(?:\s+millonario)?(?=$|[\s,;:/+&|.-])/],
    ['Guácharo',/^guacharo(?:\s*activo)?(?=$|[\s,;:/+&|.-])/],
    ['Selva',/^selva(?:\s*plus)?(?=$|[\s,;:/+&|.-])/],
    ['Cóndor',/^condor(?:\s*gana)?(?=$|[\s,;:/+&|.-])/],
    ['Tropi',/^(?:tropi(?:\s*gana)?|topi\s*gana)(?=$|[\s,;:/+&|.-])/],
    ['Monje',/^monje(?:\s*millonario)?(?=$|[\s,;:/+&|.-])/],
    ['Granja',/^(?:la\s+)?granj(?:a|ita)(?=$|[\s,;:/+&|.-])/],
    ['Loto',/^(?:(?:lotto?|loto)(?:\s*activo)?|activo)(?=$|[\s,;:/+&|.-])/]
  ];
  function lotteryPrefix(text){
    let rest=cleanPlayText(text).replace(/^para\b[\s:,-]*/,'');
    const names=[];
    while(rest){
      let matched=false;
      for(const [name,pattern] of lotteryAliases){
        const m=rest.match(pattern);if(!m)continue;
        names.push(name);rest=rest.slice(m[0].length).replace(/^[\s,;:/+&|.-]+/,'').replace(/^(?:y|e|para)\b[\s,;:/+&|.-]*/,'');
        matched=true;break;
      }
      if(!matched)break;
    }
    return {names:[...new Set(names)],rest};
  }
  function lotteryNames(text){const parsed=lotteryPrefix(text);return parsed.names.length&&!parsed.rest?parsed.names:null;}
  const animalNames=["Caballito de mar","Oso hormiguero","Erizo de mar","Puercoespín","Rinoceronte","Hormiguero","Hipopótamo","Murciélago","Escarabajo","Guacharito","Pavo real","Cachicamo","Guacamaya","Cucaracha","Cocodrilo","Serpiente","Elefante","Mariposa","Chigüire","Pelícano","Camaleón","Cangrejo","Avestruz","Guácharo","Pingüino","Antílope","Morrocoy","Ciempiés","Caballo","Camello","Gallina","Ardilla","Pescado","Culebra","Tortuga","Lechuza","Canguro","Canario","Caracol","Tiburón","Hormiga","Pantera","Gavilán","Bisonte","Turpial","Calamar","Camarón","Hámster","Anguila","Gaviota","Carnero","Alacrán","Ballena","Cochino","Delfín","Paloma","Iguana","Zamuro","Caimán","Venado","Jirafa","Búfalo","Avispa","Pereza","Grillo","Jaguar","Conejo","Gorila","Cuervo","Paujil","Perico","Águila","Tigre","Zorro","Burro","Chivo","Cerdo","Gallo","Cebra","Perro","Tucán","Garza","Pulpo","Panda","Araña","Cabra","Hurón","Cisne","Ratón","Gato","Mono","Pavo","Vaca","Lapa","Puma","Pato","Lobo","Búho","Buey","Loro","Toro","León","Rana","Oso","Pez"];
  function playSelections(text){
    let rest=cleanPlayText(text),selections=[];
    while(rest){
      rest=rest.replace(/^[\s,;:/+&|.-]+/,'').replace(/^y\b\s*/,'');if(!rest)break;
      const numeric=rest.match(/^\d{1,2}(?=$|[\s,;:/+&|.-])/);
      if(numeric){selections.push(numeric[0]);rest=rest.slice(numeric[0].length);continue;}
      const name=animalNames.find(name=>{const key=cleanPlayText(name);return rest===key||(rest.startsWith(key)&&/[\s,;:/+&|.-]/.test(rest[key.length]));});
      if(!name)return null;
      selections.push(name);rest=rest.slice(cleanPlayText(name).length);
    }
    return selections.length?selections:null;
  }
  function update(){
    let sumCents=0,amountOfNumbers=0,valid=0,bets=0,groups=0;
    let lotteries=[],previousWasHeader=false,blocked=false,previousSelections=[];
    details.replaceChildren();const ignored=new Set();
    input.value.split(/\r?\n/).forEach((line,i)=>{
      let rest=line.trim();if(!rest)return;
      if(!/(?:[x×]|\bpor\b)/i.test(rest)){
        const names=lotteryNames(rest);
        if(names){lotteries=previousWasHeader?[...new Set([...lotteries,...names])]:names;previousWasHeader=true;blocked=false;}
        else{ignored.add(i+1);previousWasHeader=false;blocked=true;previousSelections=[];}
        return;
      }
      let lineCents=0,lineGroups=0;
      while(rest){
        rest=rest.replace(/^[\s;,|]+/,'');if(!rest)break;
        const match=rest.match(/^([\s\S]*?)(?:[x×]|\bpor\b)\s*(?:Bs\.?\s*)?(\d+(?:[.,]\d+)*)(?=$|[\s;,|])/i);
        if(!match){ignored.add(i+1);break;}
        rest=rest.slice(match[0].length);
        const prefix=lotteryPrefix(match[1]);
        if(prefix.names.length){lotteries=prefix.names;blocked=false;}
        let selections=playSelections(prefix.rest);
        const inherited=prefix.names.length&&!prefix.rest&&previousSelections.length>0;
        if(inherited)selections=previousSelections.slice();
        previousWasHeader=false;
        if(blocked||!selections){ignored.add(i+1);blocked=true;previousSelections=[];continue;}
        const amount=parseMoney(match[2]),cents=Math.round(amount*100);
        if(!Number.isFinite(amount)||amount<=0||!Number.isSafeInteger(cents)){ignored.add(i+1);continue;}
        const count=Math.max(1,lotteries.length),subCents=selections.length*cents*count;
        if(!Number.isSafeInteger(sumCents+subCents)){ignored.add(i+1);continue;}
        previousSelections=selections.slice();
        sumCents+=subCents;lineCents+=subCents;amountOfNumbers+=selections.length;bets+=selections.length*count;groups++;lineGroups++;
        const el=document.createElement('div');el.className='calc-row';
        const label=lotteries.length?lotteries.join(' + '):'Sin lotería indicada';
        el.textContent=label+' · '+selections.join(', ')+' · '+selections.length+' selecciones × '+money(cents/100)+' × '+count+' '+(count===1?'lotería':'loterías')+' = '+money(subCents/100)+(inherited?' (mismos animalitos o números del grupo anterior)':'');
        details.append(el);
      }
      if(lineGroups){
        valid++;
        if(lineGroups>1){const subtotal=document.createElement('div');subtotal.className='calc-row';subtotal.textContent='Subtotal línea '+(i+1)+': '+money(lineCents/100);details.append(subtotal);}
      }
    });
    totalEl.textContent=money(sumCents/100);
    countEl.textContent=amountOfNumbers+' selecciones · '+bets+' apuestas · '+groups+' grupos · '+valid+' líneas calculadas';
    warnings.textContent=ignored.size?'Revisar línea(s) con partes sin calcular: '+[...ignored].join(', ')+'. Hay animalitos, loterías o jugadas no reconocidas; el total incluye solo los grupos reconocidos.':'';
  }
  input.addEventListener('input',update);
  document.getElementById('calcClear').addEventListener('click',()=>{input.value='';update();input.focus();});
  update();
})();
</script></body></html>
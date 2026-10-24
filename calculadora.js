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
    let rest=cleanPlayText(text).replace(/[.,\-/]/g,' '),selections=[];
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
  function preparePlayText(value){
    // Ignore the currency unit without modifying what the customer pasted.
    const lines=value.replace(/(\d)\s*bs\.?(?=$|[\s,;:/|.\-])/gi,'$1').replace(/\bbs\.?(?=\s|\d|$)/gi,' ').split(/\r?\n/);
    let defaultNames=[];
    const last=lines.findLastIndex(line=>line.trim());
    if(last>=0){
      const footer=lines[last].match(/((?:[x×]|\bpor\b)\s*\d+(?:[.,]\d+)*)\s+(.+)$/i);
      const names=footer?lotteryNames(footer[2]):null;
      if(names){defaultNames=names;lines[last]=lines[last].slice(0,footer.index)+footer[1];}
      else if(last>0&&lines.slice(0,last).some(line=>/(?:[x×]|\bpor\b)/i.test(line))){
        const names=lotteryNames(lines[last]);
        if(names){defaultNames=names;lines[last]='';}
      }
    }
    const blocks=[];let pending=[],start=0;
    lines.forEach((line,i)=>{
      const text=line.trim();if(!text)return;
      const operator=/(?:[x×]|\bpor\b)/i.test(text);
      if(!operator&&playSelections(text)&&!lotteryNames(text)){
        if(!pending.length)start=i+1;pending.push(text);return;
      }
      if(operator){blocks.push({text:[...pending,text].join(' '),lineNumber:pending.length?start:i+1});pending=[];}
      else{
        if(pending.length){blocks.push({text:pending.join(' '),lineNumber:start});pending=[];}
        blocks.push({text,lineNumber:i+1});
      }
    });
    if(pending.length)blocks.push({text:pending.join(' '),lineNumber:start});
    return {blocks,defaultNames};
  }
  function update(){
    let sumCents=0,amountOfNumbers=0,valid=0,bets=0,groups=0;
    let lotteries=[],previousWasHeader=false,blocked=false,previousSelections=[];
    details.replaceChildren();const ignored=new Set(),unread=[];
    if(input.value.length>20000){totalEl.textContent=money(0);countEl.textContent='0 selecciones';warnings.textContent='Divide la jugada en textos de hasta 20.000 caracteres.';return;}
    const prepared=preparePlayText(input.value);lotteries=prepared.defaultNames;
    prepared.blocks.forEach(({text:line,lineNumber})=>{
      let rest=line.trim();if(!rest)return;
      if(!/(?:[x×]|\bpor\b)/i.test(rest)){
        const names=lotteryNames(rest);
        if(names){lotteries=previousWasHeader?[...new Set([...lotteries,...names])]:names;previousWasHeader=true;blocked=false;}
        else{ignored.add(lineNumber);unread.push('Línea '+lineNumber+': '+(rest||line).trim().slice(0,100));previousWasHeader=false;blocked=true;previousSelections=[];}
        return;
      }
      let lineCents=0,lineGroups=0;
      while(rest){
        rest=rest.replace(/^[\s;,|.\-/]+/,'');if(!rest)break;
        const match=rest.match(/^([\s\S]*?)(?:[x×]|\bpor\b)\s*(?:Bs\.?\s*)?(\d+(?:[.,]\d+)*)(?=$|[\s;,|.\-/])/i);
        if(!match){ignored.add(lineNumber);unread.push('Línea '+lineNumber+': '+(rest||line).trim().slice(0,100));break;}
        rest=rest.slice(match[0].length);
        let prefix=lotteryPrefix(match[1]);
        // With a current lottery, a bare animal name remains an animal.
        // Explicit «para» changes lottery and repeats the preceding selections.
        if(lotteries.length&&!/^\s*para\b/i.test(match[1])&&playSelections(match[1]))prefix={names:[],rest:match[1]};
        if(prefix.names.length){lotteries=prefix.names;blocked=false;}
        let selections=playSelections(prefix.rest);
        const inherited=prefix.names.length&&!prefix.rest&&previousSelections.length>0;
        if(inherited)selections=previousSelections.slice();
        previousWasHeader=false;
        if(blocked||!selections){ignored.add(lineNumber);unread.push('Línea '+lineNumber+': '+(rest||line).trim().slice(0,100));blocked=true;previousSelections=[];continue;}
        const amount=parseMoney(match[2]),cents=Math.round(amount*100);
        if(!Number.isFinite(amount)||amount<=0||!Number.isSafeInteger(cents)){ignored.add(lineNumber);unread.push('Línea '+lineNumber+': '+(rest||line).trim().slice(0,100));continue;}
        const count=Math.max(1,lotteries.length),subCents=selections.length*cents*count;
        if(!Number.isSafeInteger(sumCents+subCents)){ignored.add(lineNumber);unread.push('Línea '+lineNumber+': '+(rest||line).trim().slice(0,100));continue;}
        previousSelections=selections.slice();
        sumCents+=subCents;lineCents+=subCents;amountOfNumbers+=selections.length;bets+=selections.length*count;groups++;lineGroups++;
        const el=document.createElement('div');el.className='calc-row';
        const label=lotteries.length?lotteries.join(' + '):'Sin lotería indicada';
        el.textContent=label+' · '+selections.join(', ')+' · '+selections.length+' selecciones × '+money(cents/100)+' × '+count+' '+(count===1?'lotería':'loterías')+' = '+money(subCents/100)+(inherited?' (mismos animalitos o números del grupo anterior)':'');
        details.append(el);
      }
      if(lineGroups){
        valid++;
        if(lineGroups>1){const subtotal=document.createElement('div');subtotal.className='calc-row';subtotal.textContent='Subtotal línea '+lineNumber+': '+money(lineCents/100);details.append(subtotal);}
      }
    });
    totalEl.textContent=money(sumCents/100);
    countEl.textContent=amountOfNumbers+' selecciones · '+bets+' apuestas · '+groups+' grupos · '+valid+' bloques calculados';
    warnings.textContent=ignored.size?'Total parcial. Partes sin calcular: '+[...new Set(unread)].join(' · ')+'. Revisa el nombre, la lotería y el monto. Usa «para guácharo» para cambiar de lotería.':'';
  }
  input.addEventListener('input',update);
  document.getElementById('calcClear').addEventListener('click',()=>{input.value='';update();input.focus();});
  update();
})();

const calcDisclosure=document.getElementById('calculator-disclosure');
calcDisclosure?.addEventListener('toggle',()=>{if(calcDisclosure.open)document.getElementById('calcInput').focus();});

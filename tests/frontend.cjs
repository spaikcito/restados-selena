const fs=require('fs'),vm=require('vm'),assert=require('assert/strict');
const html=fs.readFileSync(require('path').join(__dirname,'../index.php'),'utf8'),js=html.match(/<script>([\s\S]*?)<\/script>/)[1];let calls=0;const nodes=new Map(),intervals=[],listeners=[];
const el=()=>({textContent:'',value:'',innerHTML:'',classList:{add(){},remove(){},toggle(){}},addEventListener(){},style:{}});
const c=vm.createContext({console,Intl,Date,Map,Set,JSON,Number,String,AbortController,clearTimeout(){},window:{location:{reload(){}}},localStorage:{getItem:()=>null,setItem(){}},document:{getElementById:id=>{if(!nodes.has(id))nodes.set(id,el());return nodes.get(id)},querySelectorAll:()=>[],querySelector:()=>null,addEventListener(name){listeners.push(name)}},setInterval(fn){intervals.push(fn)},setTimeout(){},fetch:()=>{calls++;return new Promise(()=>{})}});
vm.runInContext(js,c);const run=s=>vm.runInContext(s,c);
assert.equal(calls,3);assert.equal(intervals.length,0);assert(!listeners.includes('visibilitychange'));run('loadPageResults();loadPageResults()');assert.equal(calls,3);
assert.equal(run('enabledLotteries.size'),8);assert(!run("enabledLotteries.has(normalize('LotoChaima'))"));
for(const [numero,animal,expected] of [['0','Delfín','0'],['00','Delfin','0'],['00','Ballena','00'],['0','Ballena','00'],['1','Carnero','01']]){c.fixture={numero,animal};assert.equal(run('resultNumber(fixture)'),expected);}
run(`state.items=[{loteria:'Lotto Activo',hora:'10:00 AM',numero:'0',animal:'Delfín',fuente:'365',imagen:'https://resultados365.com/delfin.webp'}];state.lotoven=[{loteria:'Lotto Activo',hora:'10:00 AM',numero:'00',animal:'Ballena',fuente:'lotoven',imagen:'https://lotoven.com/ballena.webp'}];`);
assert.equal(run('comparable(state.items[0])'),'mismatch');assert.equal(run('imageCandidates(state.items[0]).length'),1);
assert.match(run('drawCard(state.items[0])'),/class="value">0<\/div>/);assert.match(run('drawCard(state.lotoven[0])'),/class="value">00<\/div>/);
run(`state.headFast.clear();acceptHeaderResults(state.items,state.refreshCycle);acceptHeaderResults(state.lotoven,state.refreshCycle)`);
assert.equal(run("state.headFast.get('Lotto Activo').numero"),'0');
console.log('PASS: eight lotteries only; dolphin 0 vs whale 00 in cards/comparison/images/header; no background requests.');

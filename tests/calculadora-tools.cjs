const fs=require('fs'),vm=require('vm'),assert=require('assert/strict'),path=require('path');
const nodes={},checked=[],copied=[];const make=()=>({value:'',textContent:'',children:[],handlers:{},disabled:false,append(...items){this.children.push(...items)},replaceChildren(){this.children=[]},addEventListener(event,handler){this.handlers[event]=handler},focus(){},querySelectorAll(){return checked}});const node=id=>nodes[id]??=make();
vm.runInNewContext(fs.readFileSync(path.join(__dirname,'../calculadora.js'),'utf8'),{document:{getElementById:node,createElement:make},navigator:{clipboard:{writeText:async value=>copied.push(value)}},Number});
(async()=>{
 checked.push({value:'Loto'},{value:'Guácharo'});node('calcInput').value='36 37 x 100';node('calcLotteries').handlers.change();assert.match(node('calcTotal').textContent,/300,00/);assert.equal(node('calcLotterySummary').children.length,3);
 await node('calcCopy').handlers.click();assert.equal(copied[0],'total en 300 para proceder');
 node('calcInput').value='guacharito 99 x 100';node('calcInput').handlers.input();assert.match(node('calcTotal').textContent,/100,00/);assert.equal(node('calcLotterySummary').children.length,2);
 await node('calcCopy').handlers.click();assert.equal(copied[1],'total en 100 para proceder');
 node('calcInput').value='loto 25 x 50 texto desconocido';node('calcInput').handlers.input();await node('calcCopy').handlers.click();assert.equal(copied[2],'total en 50 para proceder');
 node('calcClear').handlers.click();assert.equal(node('calcCopy').disabled,true);assert.equal(node('calcLotterySummary').children.length,0);
 console.log('PASS: picker fallback, written lottery precedence, per-lottery summary, copy including exclusions/partial warnings, clear.');
})().catch(error=>{console.error(error);process.exitCode=1});

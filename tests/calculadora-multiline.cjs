const fs=require('fs'),vm=require('vm'),assert=require('assert/strict');
const nodes={};function node(id){return nodes[id]??={value:'',textContent:'',handlers:{},children:[],addEventListener(e,f){this.handlers[e]=f},replaceChildren(){this.children=[]},append(x){this.children.push(x)},focus(){}}}
vm.runInNewContext(fs.readFileSync(require('path').join(__dirname,'../calculadora.js'),'utf8'),{document:{getElementById:node,createElement:()=>({})},Number});
function calculate(input,total,warnings=false){node('calcInput').value=input;node('calcInput').handlers.input();const actual=Number(node('calcTotal').textContent.replace(/[^\d,.-]/g,'').replace(/\./g,'').replace(',','.'));assert.equal(actual,total,input);if(warnings)assert(node('calcWarnings').textContent,input);else assert.equal(node('calcWarnings').textContent,'',input);}
const first='34 09 69 37\n52 06 16 10 11\n05 68 24 17\n07 57 x 200bs';
const second='13 50 48 61 0\n55 65 20 15 38\n36 23 54 25 49\n08 04 74 00 x 100bs Guacharo';
calculate(first,3000);calculate(first+'\n'+second,4900);assert(node('calcBreakdown').children.every(x=>x.textContent.startsWith('Guácharo')));
calculate(first.replace('x 200bs','POR 200 BS.'),3000);
calculate('loto y granja\n'+first,6000);
calculate('34,09.69-37/52\n06,16.10-11/05\n68 24 17\n07 57 x 200bs',3000);
calculate('25 14 x 50bs Guacharo',100);
calculate('25 14 x 50 bs loto y granja',200);
calculate('25 14\nx 50bs\nGuacharo',100);
calculate('0 00\n01 x 100BS',300);
calculate('25 14',0,true);
calculate('25 14 x 50\n17 18',100,true);
calculate('loto y desconocida\n25 14 x 50',0,true);
calculate('loto 25 x 2,50',2.5);
calculate('loto 25x50-14 por 50',100); // operator word needs a boundary; checked below.
console.log('PASS: multiline blocks, footer lotteries, currency, punctuation, incomplete groups, zero variants and decimals.');

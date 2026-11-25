(()=>{"use strict";
const selector=".bp-single-product .quantity input.qty";
const getLimits=input=>{
const min=input.min!==""?Number(input.min):1;
const max=input.max!==""?Number(input.max):Infinity;
const rawStep=input.step||"1";
const step=rawStep==="any"?1:Math.max(Number(rawStep)||1,0.000001);
return{min,max,step};
};
const precision=value=>{
const text=String(value);
return text.includes(".")?text.split(".")[1].length:0;
};
const update=wrap=>{
const input=wrap.querySelector("input.qty");
if(!input)return;
const{min,max}=getLimits(input);
const value=Number(input.value);
const current=Number.isFinite(value)?value:min;
const minus=wrap.querySelector('[data-bp-quantity="minus"]');
const plus=wrap.querySelector('[data-bp-quantity="plus"]');
if(minus)minus.disabled=current<=min;
if(plus)plus.disabled=current>=max;
};
const init=root=>{
root.querySelectorAll?.(selector).forEach(input=>{
const wrap=input.closest(".quantity");
if(!wrap||wrap.dataset.bpQuantityReady)return;
wrap.dataset.bpQuantityReady="1";
const plus=document.createElement("button");
plus.type="button";
plus.className="bp-quantity-button bp-quantity-button--plus";
plus.dataset.bpQuantity="plus";
plus.setAttribute("aria-label","افزایش تعداد");
plus.textContent="+";
const minus=document.createElement("button");
minus.type="button";
minus.className="bp-quantity-button bp-quantity-button--minus";
minus.dataset.bpQuantity="minus";
minus.setAttribute("aria-label","کاهش تعداد");
minus.textContent="−";
wrap.insertBefore(plus,input);
wrap.append(minus);
update(wrap);
});
};
document.addEventListener("click",event=>{
const button=event.target.closest(".bp-quantity-button");
if(!button)return;
const wrap=button.closest(".quantity");
const input=wrap?.querySelector("input.qty");
if(!input)return;
const{min,max,step}=getLimits(input);
const current=Number(input.value);
const value=Number.isFinite(current)?current:min;
const next=button.dataset.bpQuantity==="plus"?Math.min(max,value+step):Math.max(min,value-step);
input.value=Number(next.toFixed(Math.min(precision(step),8))).toString();
input.dispatchEvent(new Event("input",{bubbles:true}));
input.dispatchEvent(new Event("change",{bubbles:true}));
update(wrap);
});
document.addEventListener("input",event=>{
if(event.target.matches(selector))update(event.target.closest(".quantity"));
});
document.addEventListener("change",event=>{
if(event.target.matches(selector))update(event.target.closest(".quantity"));
});
const start=()=>{
init(document);
const observer=new MutationObserver(records=>{
records.forEach(record=>record.addedNodes.forEach(node=>{
if(node.nodeType===1)init(node);
}));
});
observer.observe(document.body,{childList:true,subtree:true});
};
if(document.readyState==="loading")document.addEventListener("DOMContentLoaded",start,{once:true});
else start();
})();
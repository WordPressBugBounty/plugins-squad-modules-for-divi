(()=>{"use strict";var e={n:t=>{var r=t&&t.__esModule?()=>t.default:()=>t;return e.d(r,{a:r}),r},d:(t,r)=>{for(var a in r)e.o(r,a)&&!e.o(t,a)&&Object.defineProperty(t,a,{enumerable:!0,get:r[a]})},o:(e,t)=>Object.prototype.hasOwnProperty.call(e,t)};const t=window.jQuery;var r=e.n(t);r()((()=>{!function(){const e=r()(".disq_scrolling_text");e.length&&e.each(((e,t)=>{const a=r()(t).find(".scrolling-element");if(!a.length)return;const n=a.attr("data-scroll-direction"),o=a.attr("data-scroll-speed"),l=a.attr("data-repeat-text"),d=a.attr("data-scroll-pause");a.marquee({gap:50,delayBeforeStart:0,duration:o?Number.parseInt(o):15e3,direction:n||"left",duplicated:"on"===l,pauseOnHover:"on"===d})}))}()}))})();
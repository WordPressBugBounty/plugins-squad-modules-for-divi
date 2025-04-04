(()=>{"use strict";var e={n:t=>{var o=t&&t.__esModule?()=>t.default:()=>t;return e.d(o,{a:o}),o},d:(t,o)=>{for(var i in o)e.o(o,i)&&!e.o(t,i)&&Object.defineProperty(t,i,{enumerable:!0,get:o[i]})},o:(e,t)=>Object.prototype.hasOwnProperty.call(e,t)};const t=window.jQuery;var o=e.n(t);const i=window.wp.apiFetch;var s=e.n(i);const n=window.wp.i18n,r=window.lodash;var a=e.n(r);const c={config:{isDev:!1,isAdmin:!1,siteType:"single"},nonce:"",premium:{is_active:!1,has_license:!1,in_trial:!1,is_installed:!1},support:{chat:"",docs:"",tickets:"",user_email:"",user_name:"",user_site:"",video:""},version:{core:"",pro:"",builder:"",builderType:"theme",wordpress:""},notices:void 0,version_pro_current:"",version_pro_real:"",rest_api_v1:void 0,rest_api_v2:void 0,rest_api_pro:void 0,admin_menus:[],links:{},l10n:{license_username:(0,n.__)("Username","squad-modules-for-divi"),license_key:(0,n.__)("License Key","squad-modules-for-divi"),license_key_desc:(0,n.sprintf)(/* translators: %1$s: link to the account page, %2$s: link to the documentation */ /* translators: %1$s: link to the account page, %2$s: link to the documentation */
(0,n.__)('To locate your License Key, <a href="%1$s" target="_blank" rel="noreferrer">log in</a> to your account and navigate to the Account &gt; License Key page. <a href="%2$s" target="_blank" rel="noreferrer">Learn more here</a>. If you still get this message, please make sure that your Username and License Key have been entered correctly.',"squad-modules-for-divi"),"https://squadmodules.com/account/license-key/","https://squadmodules.com/docs/license-key/")},plugins:[]},d=Object.assign(c,window.DiviSquadExtra||{},window.DiviSquadProExtra||{}),l=(d.premium.is_active&&d.premium.has_license,d.rest_api_pro,d),p=async(e,t,o)=>{const i=a().merge({extensions:{copy:{rest_url:""}}},l).rest_api_v1;if(i.routes?.ExtensionCopyDuplicatePost){o.css({opacity:1,display:"block"});try{const o=await s()({path:`${i.namespace}${i.routes.ExtensionCopyDuplicatePost.root}`,method:"POST",data:e});o&&"function"==typeof t&&t(o)}catch(e){e.message&&"function"==typeof t&&t({success:!1,type:e.code||"error",message:e.message})}}};o()((e=>{const t=e(".wp-list-table .copy_this a.copy-this-post-link"),o=e(".divi-squad-ext-copy-this .ext-copy-loader-overlay");if(t.length&&"function"==typeof t.tooltipster){const i=t=>{o.css({opacity:0,display:"none"}),((e,t,o=!0)=>{if(!t)return void e.toast({heading:(0,n.__)("Squad Copy Extension","squad-modules-for-divi"),text:(0,n.__)("Something went wrong!","squad-modules-for-divi"),showHideTransition:"slide",allowToastClose:!0,position:"top-right",icon:"error",loader:!1});const i={heading:(0,n.__)("Squad Copy Extension","squad-modules-for-divi"),text:t.message,showHideTransition:"slide",allowToastClose:!0,position:"top-right",icon:t.success?"success":"error",loader:!1};e.toast(i),t.success&&o&&window.setTimeout((()=>{window.location.reload()}),500)})(e,t)},s={content:e("#squad_ext_copy_content"),contentAsHTML:!0,contentCloning:!0,animation:"grow",animationDuration:350,theme:"tooltipster-default",trigger:"hover",interactive:!0,interactiveTolerance:500,trackTooltip:!1,zIndex:9999999,anchor:"top-center",position:"bottom",restoration:"none",functionReady(e){e._$origin.parent().parent().css({position:"static"}),e.__Content.find(".squad-admin-button.fill-button").on("click",(function(){const s={post_ids:e._$origin.data("id"),site_id:parseInt(e.__Content.find("select").val())||1,posts_count:parseInt(e.__Content.find('input[type="number"]').val())||1};p(s,i,o),t.tooltipster("hide")}))},functionAfter(e){e._$origin.parent().parent().removeAttr("style")},hideOnClick:!0};t.tooltipster(s),t.on("click",(function(t){const s={post_ids:e(t.target).data("id"),site_id:1,posts_count:1};p(s,i,o),t.preventDefault()}))}}))})();
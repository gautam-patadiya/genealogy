!function(e){function t(r){if(n[r])return n[r].exports;var o=n[r]={i:r,l:!1,exports:{}};return e[r].call(o.exports,o,o.exports,t),o.l=!0,o.exports}var n={};t.m=e,t.c=n,t.d=function(e,n,r){t.o(e,n)||Object.defineProperty(e,n,{configurable:!1,enumerable:!0,get:r})},t.n=function(e){var n=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(n,"a",n),n},t.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},t.p="",t(t.s=2)}({2:function(e,t,n){e.exports=n("lous")},ARpf:function(e,t){e.exports=function(){return{closeButton:!0,debug:!1,newestOnTop:!1,progressBar:!1,positionClass:"toast-top-right",preventDuplicates:!1,onclick:null,showDuration:300,hideDuration:1e3,timeOut:5e3,extendedTimeOut:1e3,showEasing:"swing",hideEasing:"linear",showMethod:"fadeIn",hideMethod:"fadeOut"}}},U3Ou:function(e,t){$.extend(!0,$.fn.dataTable.defaults,{dom:"Brftip",stateSave:Store.user.preferences.global.dtStateSave,lengthMenu:[10,15,20,25,30],autoWidth:!0,pagingType:"full_numbers",filter:!0,stateDuration:7776e3,order:[],colReorder:!0,buttons:[{extend:"pageLength",className:"blahblah"},{extend:"colvis",text:'<i class="fa fa-eye"></i>'},{extend:"copy",text:'<i class="fa fa-clipboard"></i>'}],responsive:!0,serverSide:!0}),$.fn.dataTable.Api.register("sum()",function(){return this.flatten().reduce(function(e,t){return"string"==typeof e&&(e=1*e.replace(/[^\d.-]/g,"")),"string"==typeof t&&(t=1*t.replace(/[^\d.-]/g,"")),e+t},0)})},ef04:function(e,t){e.exports=function(){return{en:{noneSelectedText:"Nothing selected",noneResultsText:"No results match {0}",countSelectedText:function(e,t){return 1===e?"{0} item selected from {1} total items":"{0} items selected from {1} total items"},maxOptionsText:function(e,t){return[1===e?"Limit reached ({n} item max)":"Limit reached ({n} items max)",1===t?"Group limit reached ({n} item max)":"Group limit reached ({n} items max)"]},selectAllText:"Select All",deselectAllText:"Deselect All",multipleSeparator:", "},ro:{noneSelectedText:"Nu a fost selectat nimic",noneResultsText:"Nu exista niciun rezultat {0}",countSelectedText:"{0} din {1} selectat(e)",maxOptionsText:["Limita a fost atinsa ({n} {var} max)","Limita de grup a fost atinsa ({n} {var} max)",["iteme","item"]],multipleSeparator:", ",selectAllText:"Selecteaza tot",deselectAllText:"Deselecteaza tot"}}}},lous:function(e,t,n){moment.locale(Store.user.preferences.global.lang),Chart.defaults.global.animationDuration=600,Chart.defaults.global.legend.display=!0,Chart.defaults.global.legend.labels.boxWidth=15,Chart.defaults.global.legend.fullWidth=!1,toastr.options=n("ARpf"),n("U3Ou"),window.bootstrapSelect=n("ef04"),$.fn.selectpicker.defaults=bootstrapSelect()[Store.user.preferences.global.lang]||bootstrapSelect().en,NProgress.configure({template:'<div class="bar" role="bar"><div class="peg"></div></div>'}),axios.interceptors.request.use(function(e){return e.headers["X-CSRF-TOKEN"]=Laravel.csrfToken,NProgress.start(),e}),axios.interceptors.response.use(function(e){return NProgress.done(),e},function(e){return NProgress.done(),Promise.reject(e)})}});
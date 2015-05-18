/* ======================================================================================= */
/* merged js/tinybox/tinybox.js                                                            */
/* ======================================================================================= */
var TINY={};

function T$(i){return document.getElementById(i)}

TINY.box=function(){
	var p,m,b,fn,ic,iu,iw,ih,ia,f=0;
	return{
		show:function(c,u,w,h,a,t){
			if(!f){
				p=document.createElement('div'); p.id='tinybox';
				m=document.createElement('div'); m.id='tinymask';
				b=document.createElement('div'); b.id='tinycontent';
				document.body.appendChild(m); document.body.appendChild(p); p.appendChild(b);
				m.onclick=TINY.box.hide; window.onresize=TINY.box.resize; f=1
			}
			if(!a&&!u){
				p.style.width=w?w+'px':'auto'; p.style.height=h?h+'px':'auto';
				p.style.backgroundImage='none'; b.innerHTML=c
			}else{
				b.style.display='none'; p.style.width=p.style.height='100px'
			}
			this.mask();
			ic=c; iu=u; iw=w; ih=h; ia=a; this.alpha(m,1,80,3);
			if(t){setTimeout(function(){TINY.box.hide()},1000*t)}
		},
		fill:function(c,u,w,h,a){
			if(u){
				p.style.backgroundImage='';
				var x=window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject('Microsoft.XMLHTTP');
				x.onreadystatechange=function(){
					if(x.readyState==4&&x.status==200){TINY.box.psh(x.responseText,w,h,a)}
				};
				x.open('GET',c,1); x.send(null)
			}else{
				this.psh(c,w,h,a)
			}
		},
		psh:function(c,w,h,a){
			if(a){
				if(!w||!h){
					var x=p.style.width, y=p.style.height; b.innerHTML=c;
					p.style.width=w?w+'px':''; p.style.height=h?h+'px':'';
					b.style.display='';
					w=parseInt(b.offsetWidth); h=parseInt(b.offsetHeight);
					b.style.display='none'; p.style.width=x; p.style.height=y;
				}else{
					b.innerHTML=c
				}
				this.size(p,w,h)
			}else{
				p.style.backgroundImage='none'
			}
		},
		hide:function(){
			TINY.box.alpha(p,-1,0,3)
		},
		resize:function(){
			TINY.box.pos(); TINY.box.mask()
		},
		mask:function(){
			m.style.height=TINY.page.total(1)+'px';
			m.style.width=''; m.style.width=TINY.page.total(0)+'px'
		},
		pos:function(){
			var t=(TINY.page.height()/2)-(p.offsetHeight/2); t=t<10?10:t;
			p.style.top=(t+TINY.page.top())+'px';
			p.style.left=(TINY.page.width()/2)-(p.offsetWidth/2)+'px'
		},
		alpha:function(e,d,a){
			clearInterval(e.ai);
			if(d==1){
				e.style.opacity=0; e.style.filter='alpha(opacity=0)';
				e.style.display='block'; this.pos()
			}
			e.ai=setInterval(function(){TINY.box.ta(e,a,d)},20)
		},
		ta:function(e,a,d){
			var o=Math.round(e.style.opacity*100);
			if(o==a){
				clearInterval(e.ai);
				if(d==-1){
					e.style.display='none';
					e==p?TINY.box.alpha(m,-1,0,2):b.innerHTML=p.style.backgroundImage=''
				}else{
					e==m?this.alpha(p,1,100):TINY.box.fill(ic,iu,iw,ih,ia)
				}
			}else{
				var n=Math.ceil((o+((a-o)*.5))); n=n==1?0:n;
				e.style.opacity=n/100; e.style.filter='alpha(opacity='+n+')'
			}
		},
		size:function(e,w,h){
			e=typeof e=='object'?e:T$(e); clearInterval(e.si);
			var ow=e.offsetWidth, oh=e.offsetHeight,
			wo=ow-parseInt(e.style.width), ho=oh-parseInt(e.style.height);
			var wd=ow-wo>w?0:1, hd=(oh-ho>h)?0:1;
			e.si=setInterval(function(){TINY.box.ts(e,w,wo,wd,h,ho,hd)},20)
		},
		ts:function(e,w,wo,wd,h,ho,hd){
			var ow=e.offsetWidth-wo, oh=e.offsetHeight-ho;
			if(ow==w&&oh==h){
				clearInterval(e.si); p.style.backgroundImage='none'; b.style.display='block'
			}else{
				if(ow!=w){var n=ow+((w-ow)*.5); e.style.width=wd?Math.ceil(n)+'px':Math.floor(n)+'px'}
				if(oh!=h){var n=oh+((h-oh)*.5); e.style.height=hd?Math.ceil(n)+'px':Math.floor(n)+'px'}
				this.pos()
			}
		}
	}
}();

TINY.page=function(){
	return{
		top:function(){return document.documentElement.scrollTop||document.body.scrollTop},
		width:function(){return self.innerWidth||document.documentElement.clientWidth||document.body.clientWidth},
		height:function(){return self.innerHeight||document.documentElement.clientHeight||document.body.clientHeight},
		total:function(d){
			var b=document.body, e=document.documentElement;
			return d?Math.max(Math.max(b.scrollHeight,e.scrollHeight),Math.max(b.clientHeight,e.clientHeight)):
			Math.max(Math.max(b.scrollWidth,e.scrollWidth),Math.max(b.clientWidth,e.clientWidth))
		}
	}
}();


	
/* ======================================================================================= */
/* merged js/magestore/luckydraw.js                                                        */
/* ======================================================================================= */
var Luckydraw = Class.create();
Luckydraw.prototype = {
	initialize: function(ulId,numberLen,btnAction,dialUrl,countTime){
		this.ulId = ulId;
		this.numberLen = numberLen;
		this.index = -1;
		this.btnAction = btnAction;
		this.dialUrl = dialUrl;
		this.countTime = countTime;
		
		this.dialing = this.dialing.bindAsEventListener(this);
		this.completeDial = this.completeDial.bindAsEventListener(this);
		this.successDial = this.successDial.bindAsEventListener(this);
	},
	/* Start Dial */
	randomDec: function(){
		return Math.floor(Math.random()*10);
	},
	startDial: function(){
		for (var i=0; i<this.numberLen; i++){
			var numberI = $(this.ulId+'-'+i);
			//numberI.removeClassName('unactive');
			//numberI.addClassName('active');
			numberI.down('.digital').innerHTML = this.randomDec();
		}
		this.intervalCache = setInterval(this.dialing,this.countTime);
	},
	dialing: function(){
		var delta = 0;
		if (this.index == -1) delta = 1;
		for (var i=this.index+delta; i<this.numberLen; i++){
			var numberI = $(this.ulId+'-'+i);
			numberI.down('.digital').innerHTML = this.randomDec();
		}
	},
	/* Dialing */
	dial: function(callBack){
		if (typeof callBack == "function") this.callBack = callBack;
		var params = '';
		if ($('luckydraw-register-form'))
			params = $('luckydraw-register-form').serialize();
		else
			this.startDial();
		new Ajax.Request(this.dialUrl,{
			method: 'post',
			postBody: params,
			parameters: params,
			onException: function (xhr, e){
				window.location.reload();
			},
			onComplete: this.completeDial
		});
	},
	completeDial: function(xhr){
		if (xhr.responseText.isJSON()){
			var response = xhr.responseText.evalJSON();
			if (response.error){
				if (response.message) alert(response.message);
				if ($(this.btnAction)) $(this.btnAction).removeClassName('disable');
				this.stopDial(false);
				if (typeof this.callBack == "function"){
					this.callBack(response);
					this.callBack = false;
				}
				return false;
			}
			if (response.refresh){
				window.location.reload();
				return false;
			}
			if (response.luckycode){
				this.response = response;
				this.stopDial(response.luckycode);
				return true;
			}
		}
		this.stopDial(false);
		window.location.reload();
	},
	/* Stop Dial */
	successDial: function(){
		if (this.index == this.numberLen || this.index == -1){
			clearInterval(this.successInterval);
			clearInterval(this.intervalCache);
			this.index = -1;
			if (typeof this.callBack == "function"){
				this.callBack(this.response);
				this.callBack = false;
				this.response = false;
			}
		} else {
			var numberI = $(this.ulId+'-'+this.index);
			var luckycode = this.luckycode + '';
			numberI.down('.digital').innerHTML = luckycode.charAt(this.index);
			this.index++;
			numberI.removeClassName('unactive');
			numberI.addClassName('active');
		}
	},
	stopDial: function(luckycode){
		if (luckycode){
			this.luckycode = luckycode;
			this.index = 0;
			this.successInterval = setInterval(this.successDial,this.countTime*7);
			return true;
		}
		clearInterval(this.intervalCache);
		for (var i=0; i<this.numberLen; i++){
			var numberI = $(this.ulId+'-'+i);
			//numberI.removeClassName('active');
			//numberI.addClassName('unactive');
			numberI.down('.digital').innerHTML = '?';
		}
	}
}

var LuckyDrawCountdown = Class.create();
LuckyDrawCountdown.prototype = {
	initialize: function(container,timeLeft,nowTime){
		this.container = container;
		this.timeLeft = timeLeft;
		this.nowTime = parseInt(nowTime) * 1000;
		this.traceTime = {
			second: ['minute',59],
			minute: ['hour',23],
			hour: ['day',30],
			day: ['month',11],
			month: ['year',0]
		};
		this.startCounter = this.startCounter.bindAsEventListener(this);
		this.intervalCache = setInterval(this.startCounter,1000);
		this.startCounter();
	},
	countDown: function(fieldName,resetValue){
		if (this.isZeroTime()) return false;
		if (this.timeLeft[fieldName] <= 0){
			var traceTime = this.traceTime[fieldName];
			if (traceTime){
				this.timeLeft[fieldName] = resetValue;
				var traceReset = traceTime[1];
				if (traceTime[0] == 'day'){
					var now = new Date(this.nowTime);
					traceReset = 31 - new Date(now.getYear(),now.getMonth(),32).getDate();
				}
				this.countDown(traceTime[0],traceReset);
			}
		} else {
			this.timeLeft[fieldName] = this.timeLeft[fieldName] - 1;
		}
		return true;
	},
	isZeroTime: function(){
		var t = this.timeLeft;
		if (t.year <= 0 && t.month <= 0 && t.day <= 0 && t.hour <= 0 && t.minute <= 0 && t.second <=0)
			return true;
		return false;
	},
	startCounter: function(){
		if (this.isZeroTime()){
			clearInterval(this.intervalCache);
			window.location.reload();
			return false;
		}
		this.countDown('second',59);
		this.nowTime += 1000;
		this.showHtml();
	},
	showHtml: function(){
		this.updateHtml('year',true);
		this.updateHtml('month',true);
		this.updateHtml('day',true);
		this.updateHtml('hour',false);
		this.updateHtml('minute',false);
		this.updateHtml('second',false);
	},
	updateHtml: function(fieldName,hasLabels){
		if (!$(this.container)) return false;
		if (this.timeLeft[fieldName] <= 0 && hasLabels){
			$(this.container).down('.'+fieldName).hide();
			return true;
		}
		var timeHtml = this.timeLeft[fieldName];
		if (hasLabels){
			if (this.timeLeft[fieldName]>1) {
				$(this.container).down('.'+fieldName).down('.labels').show();
				$(this.container).down('.'+fieldName).down('.label').hide();
			} else {
				$(this.container).down('.'+fieldName).down('.labels').hide();
				$(this.container).down('.'+fieldName).down('.label').show();
			}
		} else if (timeHtml < 10) {
			timeHtml = '0' + timeHtml;
		}
		$(this.container).down('.'+fieldName).down('.value').innerHTML = timeHtml;
		return false;
	}
}

function showPopupPosition(width,height){
	var  screenX    = typeof window.screenX != 'undefined' ? window.screenX : window.screenLeft;
	var	 screenY    = typeof window.screenY != 'undefined' ? window.screenY : window.screenTop;
	var	 outerWidth = typeof window.outerWidth != 'undefined' ? window.outerWidth : document.body.clientWidth;
	var	 outerHeight = typeof window.outerHeight != 'undefined' ? window.outerHeight : (document.body.clientHeight - 22);
	var left = parseInt(screenX + ((outerWidth - width) / 2), 10);
	var top = parseInt(screenY + ((outerHeight - height) / 2.5), 10);
	return 'width='+width+',height='+height+',left='+left+',top='+top;
};


// divs and selects by Leo Tsvaigboim | juleo@mail.ru
// keep these two lines and you're free to use this code

var data = {
		divitems: new Array()
	}
	//menu layers data
	data["divitems"][1] = new Array("about-company","about-company.html",477);
	data["divitems"][2] = new Array("about-holiday","about-holiday.html",410);
	data["divitems"][3] = new Array("services","services.html",338);
	data["divitems"][4] = new Array("royal-table","royal-table.html",271);
	data["divitems"][5] = new Array("holiday-wizard","holiday-wizard.html",218);
	data["divitems"][6] = new Array("contacts","contacts.html",192);
	data["divitems"][7] = new Array("news","news.html",135);

//layers object
var divs = {
	divsnumber : 10,
	offsetX : 904, // в данном случае - ширина шапки
	offsetY : 453, // в данном случае отступ меню по вертикали от верхней границы окна
	offsetdY : 35, // отступ по вертикали между пунктами меню
	divstext : '',
	delayshowtime : 10,
	delayhidetime1 : 500,
	delayhidetime2 : 500,
	defdiv : 'blank',
	currdiv : 0,
	delayshowvar : 0,
	delayhidevar1 : 0,
	delayhidevar2 : 0,
	itemcell : false,
	isfirst : true,
	
	load : function ()
	{
		for ( var i = 1; i < data["divitems"].length; i++ )
		{
			if (data["divitems"][i] && data["divitems"][i].length != 0)
			{
				divs.divstext += '<div id="div' + i + '" class="u02">';
				divs.divstext += '<table cellpadding="0" cellspacing="0"><tr><td height="15" align="right" valign="bottom">';
				divs.divstext += '<a href="' + data['divitems'][i][1] + '">';
				divs.divstext += '<img src="i/icn03-' + data['divitems'][i][0] + '.gif" height="15" alt="" border="0" class="m01a" id="divanchor'+ i +'"><img src="i/bg03.gif" width="14" height="14" alt="" class="m01b"></a>';
				divs.divstext += '</td></tr></table>';
				divs.divstext += '</div>';
			}
		}
		divs.divstext += '<div id="blank" class="u05_1"><img src="i/0.gif" width="1" height="1"></div>';
		if (document.getElementById('divscontainer'))
		{
				divs.container = new lib.dhtmlobject('divscontainer');
				divs.container.write(divs.divstext);
		}
	},
	
	init : function ()
	{
//		divs.load();
//		alert(1)
		divs.objs = new Array();
		divs.anchors = new Array();
//		divs.bullets = new Array();
		divs.objs[0] = new lib.dhtmlobject(divs.defdiv);
//		divs.anchors[0] = new lib.dhtmlobject(divs.defdiv);
//		divs.bullets[0] = new lib.dhtmlobject(divs.defdiv);
		for ( var i = 1; i < divs.divsnumber; i++ )
		{
			if (document.getElementById('div' + i)) {
				divs.objs[i] = new lib.dhtmlobject('div' + i);
//				if(document.getElementById('divbullet' + i))
//				{
//					divs.anchors[i] = new lib.dhtmlobject('divanchor' + i);
//					divs.bullets[i] = new lib.dhtmlobject('divbullet' + i);
					divs.objs[i].css.left = getX(document.getElementById('headerCell')) + divs.offsetX - data['divitems'][i][2]-divs.objs[i].el.clientWidth;
					divs.objs[i].css.top = getY(document.getElementById('headerCell')) + divs.offsetY + (i-1)*divs.offsetdY;
					divs.objs[i].show();
//				}
			}
			else {
				divs.objs[i] = divs.objs[0];
//				if(document.getElementById('divanchor' + i))
//				{
//					divs.anchors[i] = new lib.dhtmlobject('divanchor' + i);
//				}
			}
		}
	},
	
	repos : function ()
	{
		for ( var i = 1; i < divs.divsnumber; i++ )
		{
			if (document.getElementById('div' + i)) {
//				if(document.getElementById('divbullet' + i))
//				{
					divs.objs[i].css.left = getX(document.getElementById('headerCell')) + divs.offsetX - data['divitems'][i][2]-divs.objs[i].el.clientWidth;
					divs.objs[i].css.top = getY(document.getElementById('headerCell')) + divs.offsetY + (i-1)*divs.offsetdY;
//				}
			}
		}
	},
	
	divclick : function (el)
	{
		re = /divanchor/;
		num = el.id.replace(re, "");
		if (data["divitems"][num] && data["divitems"][num].length != 0)
		{
			if (data["divitems"][num][1] != "#")
			{
				window.location = data["divitems"][num][1];
				return false;
			}
		}
	},
	
	hidediv : function ()
	{
		if (divs.currdiv!=0)
		{
			divs.objs[divs.currdiv].hide();
			divs.objs[0].show();
		}
		else
		{
			divs.objs[0].hide();
		}
		if (divs.itemcell)
		{
			cc(divs.anchors[divs.currdiv].el,0, 1);
		}
	},
	
	delayhidediv1 : function ()
	{
		clearTimeout(divs.delayshowvar);
		divs.delayhidevar1 = setTimeout("divs.hidediv()",divs.delayhidetime1);
	},
	
	delayhidediv2 : function ()
	{
		divs.delayhidevar2 = setTimeout("divs.hidediv()",divs.delayhidetime2);
	},
	
	showdiv : function ()
	{
		divs.objs[divs.currdiv].show();
		divs.objs[0].hide();
		clearTimeout(divs.delayhidevar1);
		clearTimeout(divs.delayhidevar2);
		if (divs.itemcell)
		{
			cc(divs.anchors[divs.currdiv].el,1, 1);
		}
	},
	
	delayshowdiv : function (el)
	{
		re = /divanchor/;
		num = el.id.replace(re, "");
		if (divs.isfirst)
		{
			if (divs.objs[num].css.top != getY(document.getElementById('divbullet' + num)) + divs.offsetY)
			{
//				pageInit();
				divs.repos();
			}
			divs.isfirst = false;
		}
		clearTimeout(divs.delayhidevar1);
		if (divs.currdiv != num) {
			divs.hidediv();
			divs.currdiv = num;
			divs.delayshowvar = setTimeout("divs.showdiv()",divs.delayshowtime);
		}
		else {
			divs.delayshowvar = setTimeout("divs.showdiv()",divs.delayshowtime);
		}
	}
}

function pageInit ()
{
	divs.load();
	divs.init();
}

function pageResize ()
{
	divs.repos();
}

//window.onload = pageInit;
window.onresize = pageResize;



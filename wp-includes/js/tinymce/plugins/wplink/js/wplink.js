var wpLink;(function(f){var b={},e={},d,a,c;wpLink={timeToTriggerRiver:150,minRiverAJAXDuration:200,riverBottomThreshold:5,lastSearch:"",init:function(){b.dialog=f("#wp-link");b.url=f("#url-field");b.title=f("#link-title-field");b.openInNewTab=f("#link-target-checkbox");b.search=f("#search-field");e.search=new a(f("#search-results"));e.recent=new a(f("#most-recent-results"));e.elements=f(".query-results",b.dialog);f("#wp-link-update").click(wpLink.update);f("#wp-link-cancel").click(function(){tinyMCEPopup.close()});e.elements.delegate("li","click",wpLink.selectInternalLink);b.search.keyup(wpLink.searchInternalLinks);b.dialog.bind("dialogopen",wpLink.refresh)},refresh:function(){var g;d=tinyMCEPopup.editor;e.elements.find(".selected").removeClass("selected");b.url.val("http://").focus();b.title.val("");if(g=d.dom.getParent(d.selection.getNode(),"A")){b.url.val(g.href);b.title.val(d.dom.getAttrib(g,"title"));if("_blank"==d.dom.getAttrib(g,"target")){b.openInNewTab.attr("checked","checked")}}if(!e.recent.ul.children().length){e.recent.ajax()}},update:function(){var l,h=tinyMCEPopup.editor,i={href:b.url.val(),title:b.title.val(),target:b.openInNewTab.attr("checked")?"_blank":""},m,k,g,j=i.title?i.title:i.href;tinyMCEPopup.restoreSelection();m=h.dom.getParent(h.selection.getNode(),"A");if(!i.href){if(h.selection.isCollapsed()){tinyMCEPopup.close();return}else{if(m){tinyMCEPopup.execCommand("mceBeginUndoLevel");g=h.selection.getBookmark();h.dom.remove(m,1);h.selection.moveToBookmark(g);tinyMCEPopup.execCommand("mceEndUndoLevel");tinyMCEPopup.close();return}}}tinyMCEPopup.execCommand("mceBeginUndoLevel");if(m==null){h.getDoc().execCommand("unlink",false,null);if(h.selection.isCollapsed()){l=h.dom.create("a",{href:"#mce_temp_url#"},j);h.selection.setNode(l)}else{tinyMCEPopup.execCommand("CreateLink",false,"#mce_temp_url#",{skip_undo:1})}tinymce.each(h.dom.select("a"),function(o){if(h.dom.getAttrib(o,"href")=="#mce_temp_url#"){m=o;h.dom.setAttribs(m,i)}})}else{h.dom.setAttribs(m,i)}k=f(m).children();if(k.length!=1||k.first().not("img")){h.focus();h.selection.select(m);h.selection.collapse(0);tinyMCEPopup.storeSelection()}tinyMCEPopup.execCommand("mceEndUndoLevel");tinyMCEPopup.close()},selectInternalLink:function(){var g=f(this);if(g.hasClass("unselectable")){return}g.siblings(".selected").removeClass("selected");g.addClass("selected");b.url.val(g.children(".item-permalink").val());b.title.val(g.children(".item-title").text())},searchInternalLinks:function(){var h=f(this),i,g=h.val();if(g.length>2){e.recent.element.hide();e.search.element.show();if(wpLink.lastSearch==g){return}wpLink.lastSearch=g;i=h.siblings("img.waiting").show();e.search.change(g);e.search.ajax(function(){i.hide()})}else{e.search.element.hide();e.recent.element.show()}},delayedCallback:function(i,g){var l,k,j,h;if(!g){return i}setTimeout(function(){if(k){return i.apply(h,j)}l=true},g);return function(){if(l){return i.apply(this,arguments)}j=arguments;h=this;k=true}}};a=function(i,h){var g=this;this.element=i;this.ul=i.children("ul");this.waiting=i.find(".river-waiting");this.change(h);i.scroll(function(){g.maybeLoad()})};f.extend(a.prototype,{ajax:function(j){var h=this,i=this.query.page==1?0:wpLink.minRiverAJAXDuration,g=wpLink.delayedCallback(function(k,l){h.process(k,l);if(j){j(k,l)}},i);this.query.ajax(g)},change:function(g){if(this.query&&this._search==g){return}this._search=g;this.query=new c(g);this.element.scrollTop(0)},process:function(g,k){var h="",i=true,j=k.page==1;if(!g){if(j){h+='<li class="unselectable"><span class="item-title"><em>'+wpLinkL10n.noMatchesFound+"</em></span></li>"}}else{f.each(g,function(){h+=i?'<li class="alternate">':"<li>";h+='<input type="hidden" class="item-permalink" value="'+this["permalink"]+'" />';h+='<span class="item-title">';h+=this["title"]?this["title"]:"<em>"+wpLinkL10n.untitled+"</em>";h+='</span><span class="item-info">'+this["info"]+"</span></li>";i=!i})}this.ul[j?"html":"append"](h)},maybeLoad:function(){var h=this,i=this.element,g=i.scrollTop()+i.height();if(!this.query.ready()||g<this.ul.height()-wpLink.riverBottomThreshold){return}setTimeout(function(){var j=i.scrollTop(),k=j+i.height();if(!h.query.ready()||k<h.ul.height()-wpLink.riverBottomThreshold){return}h.waiting.show();i.scrollTop(j+h.waiting.outerHeight());h.ajax(function(){h.waiting.hide()})},wpLink.timeToTriggerRiver)}});c=function(g){this.page=1;this.allLoaded=false;this.querying=false;this.search=g};f.extend(c.prototype,{ready:function(){return !(this.querying||this.allLoaded)},ajax:function(i){var g=this,h={action:"wp-link-ajax",page:this.page};if(this.search){h.search=this.search}this.querying=true;f.post(ajaxurl,h,function(j){g.page++;g.querying=false;g.allLoaded=!j;i(j,h)},"json")}});f(document).ready(wpLink.init)})(jQuery);
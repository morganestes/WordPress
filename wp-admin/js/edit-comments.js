var theList,theExtraList,toggleWithKeyboard=false;(function(a){setCommentsList=function(){var d,f,i,m=0,h,j,e,l;d=a('.tablenav input[name="_total"]',"#comments-form");f=a('.tablenav input[name="_per_page"]',"#comments-form");i=a('.tablenav input[name="_page"]',"#comments-form");h=function(o,n){var p=a("#"+n.element);if(p.is(".unapproved")){p.find("div.comment_status").html("0")}else{p.find("div.comment_status").html("1")}a("span.pending-count").each(function(){var q=a(this),s,r;s=q.html().replace(/[^0-9]+/g,"");s=parseInt(s,10);if(isNaN(s)){return}r=a("#"+n.element).is("."+n.dimClass)?1:-1;s=s+r;if(s<0){s=0}q.closest("#awaiting-mod")[0==s?"addClass":"removeClass"]("count-0");g(q,s);k()})};j=function(r,v){var x=a(r.target).attr("className"),o,p,q,u,w,t,s=false;r.data._total=d.val()||0;r.data._per_page=f.val()||0;r.data._page=i.val()||0;r.data._url=document.location.href;if(x.indexOf(":trash=1")!=-1){s="trash"}else{if(x.indexOf(":spam=1")!=-1){s="spam"}}if(s){o=x.replace(/.*?comment-([0-9]+).*/,"$1");p=a("#comment-"+o);note=a("#"+s+"-undo-holder").html();p.find(".check-column :checkbox").attr("checked","");if(p.siblings("#replyrow").length&&commentReply.cid==o){commentReply.close()}if(p.is("tr")){q=p.children(":visible").length;t=a(".author strong",p).text();u=a('<tr id="undo-'+o+'" class="undo un'+s+'" style="display:none;"><td colspan="'+q+'">'+note+"</td></tr>")}else{t=a(".comment-author",p).text();u=a('<div id="undo-'+o+'" style="display:none;" class="undo un'+s+'">'+note+"</div>")}p.before(u);a("strong","#undo-"+o).text(t+" ");w=a(".undo a","#undo-"+o);w.attr("href","comment.php?action=un"+s+"comment&c="+o+"&_wpnonce="+r.data._ajax_nonce);w.attr("className","delete:the-comment-list:comment-"+o+"::un"+s+"=1 vim-z vim-destructive");a(".avatar",p).clone().prependTo("#undo-"+o+" ."+s+"-undo-inside");w.click(function(){v.wpList.del(this);a("#undo-"+o).css({backgroundColor:"#ceb"}).fadeOut(350,function(){a(this).remove();a("#comment-"+o).css("backgroundColor","").fadeIn(300,function(){a(this).show()})});return false})}return r};e=function(n,o,p){if(o<m){return}if(p){m=o}d.val(n.toString());a("span.total-type-count").each(function(){g(a(this),n)})};function k(t){var s=a("#dashboard_right_now"),p,r,q,o;t=t||0;if(isNaN(t)||!s.length){return}p=a("span.total-count",s);r=a("span.approved-count",s);q=c(p);q=q+t;o=q-c(a("span.pending-count",s))-c(a("span.spam-count",s));g(p,q);g(r,o)}function c(o){var p=parseInt(o.html().replace(/[^0-9]+/g,""),10);if(isNaN(p)){return 0}return p}function g(p,q){var o="";if(isNaN(q)){return}q=q<1?"0":q.toString();if(q.length>3){while(q.length>3){o=thousandsSeparator+q.substr(q.length-3)+o;q=q.substr(0,q.length-3)}q=q+o}p.html(q)}l=function(n,p){var u,q,s,w=a(p.target).parent().is("span.untrash"),o=a(p.target).parent().is("span.unspam"),v,t;function x(r){if(a(p.target).parent().is("span."+r)){return 1}else{if(a("#"+p.element).is("."+r)){return -1}}return 0}v=x("spam");t=x("trash");if(w){t=-1}if(o){v=-1}a("span.pending-count").each(function(){var r=a(this),z=c(r),y=a("#"+p.element).is(".unapproved");if(a(p.target).parent().is("span.unapprove")||((w||o)&&y)){z=z+1}else{if(y){z=z-1}}if(z<0){z=0}r.closest("#awaiting-mod")[0==z?"addClass":"removeClass"]("count-0");g(r,z);k()});a("span.spam-count").each(function(){var r=a(this),y=c(r)+v;g(r,y)});a("span.trash-count").each(function(){var r=a(this),y=c(r)+t;g(r,y)});if(a("#dashboard_right_now").length){s=t?-1*t:0;k(s)}else{u=d.val()?parseInt(d.val(),10):0;u=u-v-t;if(u<0){u=0}if(("object"==typeof n)&&m<p.parsed.responses[0].supplemental.time){q=p.parsed.responses[0].supplemental.pageLinks||"";if(a.trim(q)){a(".tablenav-pages").find(".page-numbers").remove().end().append(a(q))}else{a(".tablenav-pages").find(".page-numbers").remove()}e(u,p.parsed.responses[0].supplemental.time,true)}else{e(u,n,false)}}if(theExtraList.size()==0||theExtraList.children().size()==0||w||o){return}theList.get(0).wpList.add(theExtraList.children(":eq(0)").remove().clone());b()};var b=function(q){var o=a.query.get(),n=listTable.get_total_pages(),p=a("input[name=_per_page]","#comments-form").val();if(o.paged>n){return}if(q){theExtraList.empty();o.number=Math.min(8,p)}else{o.number=1;o.offset=p-1}o.paged++;listTable.fetch_list(o,function(r){theExtraList.get(0).wpList.add(r.rows)})};theExtraList=a("#the-extra-comment-list").wpList({alt:"",delColor:"none",addColor:"none"});theList=a("#the-comment-list").wpList({alt:"",delBefore:j,dimAfter:h,delAfter:l,addColor:"none"}).bind("wpListDelEnd",function(o,n){var p=n.element.replace(/[^0-9]+/g,"");if(n.target.className.indexOf(":trash=1")!=-1||n.target.className.indexOf(":spam=1")!=-1){a("#undo-"+p).fadeIn(300,function(){a(this).show()})}});a(listTable).bind("changePage",b)};commentReply={cid:"",act:"",init:function(){var b=a("#replyrow");a("a.cancel",b).click(function(){return commentReply.revert()});a("a.save",b).click(function(){return commentReply.send()});a("input#author, input#author-email, input#author-url",b).keypress(function(c){if(c.which==13){commentReply.send();c.preventDefault();return false}});a("#the-comment-list .column-comment > p").dblclick(function(){commentReply.toggle(a(this).parent())});a("#doaction, #doaction2, #post-query-submit").click(function(c){if(a("#the-comment-list #replyrow").length>0){commentReply.close()}});this.comments_listing=a('#comments-form > input[name="comment_status"]').val()||""},addEvents:function(b){b.each(function(){a(this).find(".column-comment > p").dblclick(function(){commentReply.toggle(a(this).parent())})})},toggle:function(b){if(a(b).css("display")!="none"){a(b).find("a.vim-q").click()}},revert:function(){if(a("#the-comment-list #replyrow").length<1){return false}a("#replyrow").fadeOut("fast",function(){commentReply.close()});return false},close:function(){var b;if(this.cid){b=a("#comment-"+this.cid);if(this.act=="edit-comment"){b.fadeIn(300,function(){b.show()}).css("backgroundColor","")}a("#replyrow").hide();a("#com-reply").append(a("#replyrow"));a("#replycontent").val("");a("input","#edithead").val("");a(".error","#replysubmit").html("").hide();a(".waiting","#replysubmit").hide();if(a.browser.msie){a("#replycontainer, #replycontent").css("height","120px")}else{a("#replycontainer").resizable("destroy").css("height","120px")}this.cid=""}},open:function(b,d,k){var l=this,e,f,i,g,j=a("#comment-"+b);l.close();l.cid=b;a("td","#replyrow").attr("colspan",a("table.widefat thead th:visible").length);e=a("#replyrow");f=a("#inline-"+b);i=l.act=(k=="edit")?"edit-comment":"replyto-comment";a("#action",e).val(i);a("#comment_post_ID",e).val(d);a("#comment_ID",e).val(b);if(k=="edit"){a("#author",e).val(a("div.author",f).text());a("#author-email",e).val(a("div.author-email",f).text());a("#author-url",e).val(a("div.author-url",f).text());a("#status",e).val(a("div.comment_status",f).text());a("#replycontent",e).val(a("textarea.comment",f).val());a("#edithead, #savebtn",e).show();a("#replyhead, #replybtn",e).hide();g=j.height();if(g>220){if(a.browser.msie){a("#replycontainer, #replycontent",e).height(g-105)}else{a("#replycontainer",e).height(g-105)}}j.after(e).fadeOut("fast",function(){a("#replyrow").fadeIn(300,function(){a(this).show()})})}else{a("#edithead, #savebtn",e).hide();a("#replyhead, #replybtn",e).show();j.after(e);a("#replyrow").fadeIn(300,function(){a(this).show()})}if(!a.browser.msie){a("#replycontainer").resizable({handles:"s",axis:"y",minHeight:80,stop:function(){a("#replycontainer").width("auto")}})}setTimeout(function(){var n,h,o,c,m;n=a("#replyrow").offset().top;h=n+a("#replyrow").height();o=window.pageYOffset||document.documentElement.scrollTop;c=document.documentElement.clientHeight||self.innerHeight||0;m=o+c;if(m-20<h){window.scroll(0,h-c+35)}else{if(n-20<o){window.scroll(0,n-35)}}a("#replycontent").focus().keyup(function(p){if(p.which==27){commentReply.revert()}})},600);return false},send:function(){var b={};a("#replysubmit .waiting").show();a("#replyrow input").each(function(){b[a(this).attr("name")]=a(this).val()});b.content=a("#replycontent").val();b.id=b.comment_post_ID;b.comments_listing=this.comments_listing;b.p=a("[name=p]").val();a.ajax({type:"POST",url:ajaxurl,data:b,success:function(c){commentReply.show(c)},error:function(c){commentReply.error(c)}});return false},show:function(b){var e,g,f,d;if(typeof(b)=="string"){this.error({responseText:b});return false}e=wpAjax.parseAjaxResponse(b);if(e.errors){this.error({responseText:wpAjax.broken});return false}e=e.responses[0];g=e.data;f="#comment-"+e.id;if("edit-comment"==this.act){a(f).remove()}a(g).hide();a("#replyrow").after(g);this.revert();this.addEvents(a(f));d=a(f).hasClass("unapproved")?"#ffffe0":"#fff";a(f).animate({backgroundColor:"#CCEEBB"},600).animate({backgroundColor:d},600);a.fn.wpList.process(a(f))},error:function(b){var c=b.statusText;a("#replysubmit .waiting").hide();if(b.responseText){c=b.responseText.replace(/<.[^<>]*?>/g,"")}if(c){a("#replysubmit .error").html(c).show()}}};a(document).ready(function(){var e,b,c,d;setCommentsList();commentReply.init();a(document).delegate("span.delete a.delete","click",function(){return false});if(typeof QTags!="undefined"){ed_reply=new QTags("ed_reply","replycontent","replycontainer","more")}if(typeof a.table_hotkeys!="undefined"){e=function(f){return function(){var h,g;h="next"==f?"first":"last";g=a("."+f+".page-numbers");if(g.length){window.location=g[0].href.replace(/\&hotkeys_highlight_(first|last)=1/g,"")+"&hotkeys_highlight_"+h+"=1"}}};b=function(g,f){window.location=a("span.edit a",f).attr("href")};c=function(){toggleWithKeyboard=true;a("input:checkbox","#cb").click().attr("checked","");toggleWithKeyboard=false};d=function(f){return function(){var g=a('select[name="action"]');a("option[value="+f+"]",g).attr("selected","selected");a("#doaction").click()}};a.table_hotkeys(a("table.widefat"),["a","u","s","d","r","q","z",["e",b],["shift+x",c],["shift+a",d("approve")],["shift+s",d("spam")],["shift+d",d("delete")],["shift+t",d("trash")],["shift+z",d("untrash")],["shift+u",d("unapprove")]],{highlight_first:adminCommentsL10n.hotkeys_highlight_first,highlight_last:adminCommentsL10n.hotkeys_highlight_last,prev_page_link_cb:e("prev"),next_page_link_cb:e("next")})}})})(jQuery);
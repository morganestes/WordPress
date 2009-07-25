(function(a){inlineEditPost={init:function(){var c=this,d=a("#inline-edit"),b=a("#bulk-edit");c.type=a("table.widefat").hasClass("page")?"page":"post";c.what="#"+c.type+"-";d.keyup(function(f){if(f.which==27){return inlineEditPost.revert()}});b.keyup(function(f){if(f.which==27){return inlineEditPost.revert()}});a("a.cancel",d).click(function(){return inlineEditPost.revert()});a("a.save",d).click(function(){return inlineEditPost.save(this)});a("td",d).keydown(function(f){if(f.which==13){return inlineEditPost.save(this)}});a("a.cancel",b).click(function(){return inlineEditPost.revert()});a("#inline-edit .inline-edit-private input[value=private]").click(function(){var e=a("input.inline-edit-password-input");if(a(this).attr("checked")){e.val("").attr("disabled","disabled")}else{e.attr("disabled","")}});a("a.editinline").live("click",function(){inlineEditPost.edit(this);return false});a("#bulk-title-div").parents("fieldset").after(a("#inline-edit fieldset.inline-edit-categories").clone()).siblings("fieldset:last").prepend(a("#inline-edit label.inline-edit-tags").clone());a("span.catshow").click(function(){a(".inline-editor ul.cat-checklist").addClass("cat-hover");a(".inline-editor span.cathide").show();a(this).hide()});a("span.cathide").click(function(){a(".inline-editor ul.cat-checklist").removeClass("cat-hover");a(".inline-editor span.catshow").show();a(this).hide()});a('select[name="_status"] option[value="future"]',b).remove();a("#doaction, #doaction2").click(function(f){var g=a(this).attr("id").substr(2);if(a('select[name="'+g+'"]').val()=="edit"){f.preventDefault();c.setBulk()}else{if(a("form#posts-filter tr.inline-editor").length>0){c.revert()}}});a("#post-query-submit").click(function(f){if(a("form#posts-filter tr.inline-editor").length>0){c.revert()}})},toggle:function(c){var b=this;a(b.what+b.getId(c)).css("display")=="none"?b.revert():b.edit(c)},setBulk:function(){var d="",c=this.type,b;this.revert();a("#bulk-edit td").attr("colspan",a(".widefat:first thead th:visible").length);a("table.widefat tbody").prepend(a("#bulk-edit"));a("#bulk-edit").addClass("inline-editor").show();a('tbody th.check-column input[type="checkbox"]').each(function(f){if(a(this).attr("checked")){var g=a(this).val(),e;e=a("#inline_"+g+" .post_title").text()||inlineEditL10n.notitle;d+='<div id="ttle'+g+'"><a id="_'+g+'" class="ntdelbutton" title="'+inlineEditL10n.ntdeltitle+'">X</a>'+e+"</div>"}});a("#bulk-titles").html(d);a("#bulk-titles a").click(function(){var e=a(this).attr("id").substr(1);a('table.widefat input[value="'+e+'"]').attr("checked","");a("#ttle"+e).remove()});if(c=="post"){b="post_tag";a('tr.inline-editor textarea[name="tags_input"]').suggest("admin-ajax.php?action=ajax-tag-search&tax="+b,{delay:500,minchars:2,multiple:true,multipleSep:", "})}},edit:function(b){var o=this,j,d,g,n,i,h,k,m,l,c=true,p,e;o.revert();if(typeof(b)=="object"){b=o.getId(b)}j=["post_title","post_name","post_author","_status","jj","mm","aa","hh","mn","ss","post_password"];if(o.type=="page"){j.push("post_parent","menu_order","page_template")}if(o.type=="post"){j.push("tags_input")}d=a("#inline-edit").clone(true);a("td",d).attr("colspan",a(".widefat:first thead th:visible").length);if(a(o.what+b).hasClass("alternate")){a(d).addClass("alternate")}a(o.what+b).hide().after(d);g=a("#inline_"+b);for(k=0;k<j.length;k++){a(':input[name="'+j[k]+'"]',d).val(a("."+j[k],g).text())}if(a(".comment_status",g).text()=="open"){a('input[name="comment_status"]',d).attr("checked","checked")}if(a(".ping_status",g).text()=="open"){a('input[name="ping_status"]',d).attr("checked","checked")}if(a(".sticky",g).text()=="sticky"){a('input[name="sticky"]',d).attr("checked","checked")}if(n=a(".post_category",g).text()){a("ul.cat-checklist :checkbox",d).val(n.split(","))}i=a("._status",g).text();if(i!="future"){a('select[name="_status"] option[value="future"]',d).remove()}if(i=="private"){a('input[name="keep_private"]',d).attr("checked","checked");a("input.inline-edit-password-input").val("").attr("disabled","disabled")}h=a('select[name="post_parent"] option[value="'+b+'"]',d);if(h.length>0){m=h[0].className.split("-")[1];l=h;while(c){l=l.next("option");if(l.length==0){break}p=l[0].className.split("-")[1];if(p<=m){c=false}else{l.remove();l=h}}h.remove()}a(d).attr("id","edit-"+b).addClass("inline-editor").show();a(".ptitle",d).focus();if(o.type=="post"){e="post_tag";a('tr.inline-editor textarea[name="tags_input"]').suggest("admin-ajax.php?action=ajax-tag-search&tax="+e,{delay:500,minchars:2,multiple:true,multipleSep:", "})}return false},save:function(e){var d,b,c=a(".post_status_page").val()||"";if(typeof(e)=="object"){e=this.getId(e)}a("table.widefat .inline-edit-save .waiting").show();d={action:"inline-save",post_type:this.type,post_ID:e,edit_date:"true",post_status:c};b=a("#edit-"+e+" :input").serialize();d=b+"&"+a.param(d);a.post("admin-ajax.php",d,function(f){a("table.widefat .inline-edit-save .waiting").hide();if(f){if(-1!=f.indexOf("<tr")){a(inlineEditPost.what+e).remove();a("#edit-"+e).before(f).remove();a(inlineEditPost.what+e).hide().fadeIn()}else{f=f.replace(/<.[^<>]*?>/g,"");a("#edit-"+e+" .inline-edit-save").append('<span class="error">'+f+"</span>")}}else{a("#edit-"+e+" .inline-edit-save").append('<span class="error">'+inlineEditL10n.error+"</span>")}},"html");return false},revert:function(){var b;if(b=a("table.widefat tr.inline-editor").attr("id")){a("table.widefat .inline-edit-save .waiting").hide();if("bulk-edit"==b){a("table.widefat #bulk-edit").removeClass("inline-editor").hide();a("#bulk-titles").html("");a("#inlineedit").append(a("#bulk-edit"))}else{a("#"+b).remove();b=b.substr(b.lastIndexOf("-")+1);a(this.what+b).show()}}return false},getId:function(c){var d=c.tagName=="TR"?c.id:a(c).parents("tr").attr("id"),b=d.split("-");return b[b.length-1]}};a(document).ready(function(){inlineEditPost.init()})})(jQuery);
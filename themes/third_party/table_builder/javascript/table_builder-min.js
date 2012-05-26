if(!Table_Builder){var Table_Builder}Table_Builder=function(b,a,k,f,i,c,g){Table_Builder.instances.push(this);var j=this;var h="#table_builder_"+b;var d=$(h);j.id=b;j.name=a;j.rows=k?k:[];j.activeMenu=false;j.celltypes=i;j.columns=f?f:[];j.insertIndex=false;j.override=false;j.editColumnObj=false;j.editColumnIndex=false;j.totalNewRows=0;j.totalNewCols=0;j.ui={wrapper:d,table:d.find("table"),thead:d.find("table thead tr"),tbody:d.find("table tbody"),rows:d.find("table tbody tr"),field:{columnName:d.find('input[name="column_name"]'),columnTitle:d.find('input[name="column_title"]'),columnType:d.find('select[name="column_type"]')},menu:{addColumn:d.find(".ui-tb-add-column-menu"),editColumn:d.find(".ui-tb-edit-column-menu"),deleteColumn:d.find(".ui-tb-delete-column-menu"),column:d.find(".ui-tb-column-menu")},button:{cancel:d.find(".ui-tb-cancel"),addColumn:d.find('a[href="#tb-add-column"]'),submitAddColumn:d.find(".ui-tb-add-column-menu button"),submitEditColumn:d.find(".ui-tb-edit-column-menu button"),addRow:d.find('a[href="#tb-add-row"]')}};j.totalRows=j.ui.tbody.find("tr").length;j.totalColumns=j.ui.thead.find("th").length;$(function(){$.each(i,function(n,m){var o=m.col_id;var l=m.display_name!=""?m.display_name:m.type;j.ui.field.columnType.append('<option value="'+o+'">'+l+"</option>")})});j.init=function(l){j.ui.table=d.find("table");j.ui.tbody=j.ui.table.find("tbody");j.ui.rows=j.ui.tbody.find("tr");j.ui.thead.find(".ui-tb-resizable").resizable({handles:"e",resize:function(m,n){if(j.hideMenu){j.hideMenu()}j.inactive();j.resizeCol(this,m)}});if(!l){}else{}};j.addToHead=function(l){var l=$(l);if(j.ui.thead.length==0){j.ui.table.append('<thead><tr><th class="ui-tb-id-column"></th></tr></thead>');j.ui.thead=j.ui.table.find("thead tr")}if(j.insertIndex===false){j.ui.thead.append(l)}else{alert();$(l).insertAfter()}j.init(true);return l};j.addToBody=function(m){m=$(m);if(j.ui.tbody.length==0){j.ui.table.append("<tbody></tbody>");j.ui.tbody=j.ui.table.find("tbody")}j.ui.tbody.append(m);var l=false;j.ui.tbody.tableDnD({onDragClass:"ui-tb-dragging",dragHandle:"ui-tb-id-column",onDragStart:function(n,o){l=$(o).index()},onDrop:function(n,o){j.reorder()}});return m};j.inactive=function(){j.ui.wrapper.find(".active").removeClass("active")};j.reorder=function(){var l=[];$(j.ui.tbody.find("tr")).each(function(o,p){var m=$(p).find("td:first");var n=parseInt(m.html())-1;j.rows[n].index=o;l.push(j.rows[n]);$.each(j.rows[n].cells,function(q,r){var s=j.columns[q].name;r.ui.td.find('*[name="'+j.name+"["+n+"]["+s+']"]').attr("name",j.name+"["+o+"]["+s+"]")});m.html(o+1)});j.rows=l};j.addRow=function(l){if(j.totalColumns==0){alert("You must add a column before adding a row.");return}if(!l){l=j.totalRows}var m=new Table_Builder.Row(j,l);j.rows.push(m);j.totalRows++};j.deleteRow=function(l){};j.getRows=function(){};j.getRow=function(){};j.insertRow=function(m,l){};j.addColumn=function(){var o=j.ui.field.columnTitle.val();var l=j.ui.field.columnName.val();var n=j.ui.field.columnType.val();if(j.isValid(j.ui.menu.addColumn)){if(!j.isDuplicateColumn(l,n)){if(!o||o==""){o="Col "+(j.totalColumns+1)}var m=new Table_Builder.Column(j,o,l,n);if(j.totalRows>0){$.each(j.rows,function(q,r){var p=new Table_Builder.Cell(r,m);if(j.insertIndex===false){r.ui.tr.append(p.ui.td)}else{alert(j.insertIndex)}})}j.totalColumns++;j.ui.thead=d.find("thead tr");j.ui.menu.addColumn.find("input, select").val("");j.hideMenu()}}j.insertIndex=false};j.editColumn=function(o,l,m){if(j.isValid(j.ui.menu.editColumn)){var n={title:j.ui.menu.editColumn.find('input[name="column_title"]').val(),name:j.ui.menu.editColumn.find('input[name="column_name"]').val(),type:j.ui.menu.editColumn.find('*[name="column_type"]').val()};j.editColumnObj.edit(n)}};j.isValid=function(l){if(l){return l.find(".validate").isValid({invalid:function(m){l.find(".invalid:first").focus()}})}return false};j.isDuplicateColumn=function(l,n){var m=false;$.each(j.columns,function(o,p){if(p.name==l||p.field==n){m=true;j.ui.field.columnName.addClass("invalid");alert("You can't have two columns with the same name")}});return m};j.deleteColumn=function(l){};j.resizeCol=function(n,m){var l=$(n).index()};j.getColumns=function(){};j.getColumn=function(){};j.insertColumn=function(m,l){};j.render=function(){};j.showMenu=function(o,n,m,p){var l={my:"center top",bottom:"center bottom",offset:"0 0"};if(!m){var m=l}if(typeof m=="function"){p=m;m=l}if(!p){p={}}if(!m.my){m.my=l.my}if(!m.bottom){m.bottom=l.bottom}if(!m.offset){m.offset=l.offset}j.ui.wrapper.find(".validate").validate();j.callback(p.click);if(j.hideMenu){j.hideMenu()}if(o.css("display")=="none"){j.activeMenu=o;j.inactive();o.find(".validate:first").focus();o.find(".validate").val("").removeClass("invalid");j.callback(p.show,o);o.fadeIn(function(){if(typeof p.visible=="function"){o.find(".validate:first").focus();j.callback(p.visible,o)}});o.position({my:m.my,at:m.at,offset:m.offset,of:n});if(!j.hideMenu||j.override){o.unbind("keypress").keypress(function(q){if(q.keyCode==13){if(j.isValid(o)){j.callback(p.valid,o)}return false}});$(document).unbind("keypress").keypress(function(q){if(q.keyCode==27){if(j.hideMenu){j.hideMenu(p)}}});j.override=false}j.hideMenu=function(q){if(p){j.callback(p.hide,o);o.fadeOut(function(){j.callback(p.hidden,o);if(typeof q=="function"){q(o)}})}o.fadeOut()}}else{o.fadeOut(function(){j.callback(p.hide,o)})}};j.callback=function(m,l){if(typeof m=="function"){m(l)}};j.ui.button.addColumn.click(function(m){var l={my:"left top",at:"left bottom",offset:"0 10"};j.showMenu(j.ui.menu.addColumn,$(this),l,{visible:function(n){j.ui.field.columnTitle.focus();n.find("button").unbind("click").click(function(){j.addColumn()})},valid:function(n){n.find("button").click()}})});j.ui.button.addRow.click(function(){j.addRow()});j.ui.button.submitEditColumn.live("click",function(){j.editColumn()});j.ui.button.cancel.live("click",function(){j.hideMenu()});var e=false;j.ui.wrapper.find('th a[href="#ui-tb-column-menu"]').live("click",function(o){var p=$(this);var m=p.parent().parent().index()-1;var n=j.columns[m];j.editColumnObj=n;var l={my:"center top",at:"center bottom",offset:"3 10"};if(e!==false&&e!=m){j.ui.menu.column.hide();j.hideMenu=false}j.showMenu(j.ui.menu.column,p,l,{click:function(){},show:function(){if(j.hideMenu){}var q={my:"left top",at:"left bottom",offset:"-15 12"};j.ui.menu.column.find('a[href="#edit-column"]').unbind("click").click(function(){j.showMenu(j.ui.menu.editColumn,p.parent(),q,{show:function(){j.override=true;j.ui.menu.editColumn.find('input[name="column_name"]').val(n.name);j.ui.menu.editColumn.find('input[name="column_title"]').val(n.title);j.ui.menu.editColumn.find('*[name="column_type"]').val(n.type);p.addClass("active")},hide:function(){p.removeClass("active")},valid:function(){j.editColumn()},visible:function(){}})});j.ui.menu.column.find('a[href="#delete-column"]').unbind("click").click(function(){j.showMenu(j.ui.menu.deleteColumn,p.parent(),q,{show:function(){j.override=true;p.addClass("active")},hide:function(){p.removeClass("active")},visible:function(){}})});j.ui.menu.column.find('a[href="#insert-after"], a[href="#insert-before"], ').unbind("click").click(function(){j.insertIndex=e;if($(this).attr("href")=="#insert-after"){j.insertIndex++}j.showMenu(j.ui.menu.addColumn,p.parent(),q,{show:function(){j.override=true;p.addClass("active")},hide:function(){p.removeClass("active")},valid:function(){j.addColumn()}})});p.addClass("active")},hide:function(){p.removeClass("active")}});e=m;j.editColumnIndex=e;return false});j.init();return j};Table_Builder.Row=function(b,a){var c=this;c.table=b;c.cells=[];c.index=a;c.isNew=true;c.isDragging=false;c.isMenuShowing=false;c.ui={tr:$('<tr><td class="ui-tb-id-column">'+(b.rows.length+1)+"</td></tr>")};c.addCell=function(d){c.ui.tr.append(d.ui.td);c.cells.push(d)};b.addToBody(c.ui.tr);$.each(b.columns,function(e,f){var d=new Table_Builder.Cell(c,f);c.addCell(d)});return c};Table_Builder.Cell=function(c,b){var a=this;a.type=b.celltype;a.html=a.type.html.replace("{DEFAULT}",c.table.name+"["+c.index+"]["+b.name+"]");a.row=c;a.hasFocus=false;a.ui={td:$("<td>"+a.html+"</td>")};a.focus=function(){};a.blur=function(){};return a};Table_Builder.Column=function(d,e,a,c){var b=this;b.title=e;b.name=a;b.type=c;b.celltype=d.celltypes[c];b.html=b.celltype.html;b.ui={th:$('<th class="ui-tb-resizable"><div class="ui-tb-relative"><a href="#ui-tb-column-menu" class="ui-tb-column-button">&#x25BE;</a><span class="title">'+b.title+"</span></div></th>")};d.addToHead(b.ui.th);b.edit=function(f){var g=b.name;b.title=f.title;if(b.type==f.type&&b.name!=f.name){b.name=f.name;$.each(d.rows,function(h,j){j.cells[d.editColumnIndex].ui.td.find('*[name="'+d.name+"["+h+"]["+g+']"]').attr("name",d.name+"["+h+"]["+f.name+"]")})}if(b.type!=f.type){b.type=f.type;b.celltype=d.celltypes[f.type];b.html=b.celltype.html;$.each(d.rows,function(j,k){var h=new Table_Builder.Cell(k,b);k.cells[d.editColumnIndex].ui.td.html(h.html)})}d.hideMenu(function(){d.inactive()});d.ui.thead.find("th:nth-child("+(d.editColumnIndex+2)+") .title").html(f.title)};d.columns.push(b);return b};Table_Builder.instances=[];
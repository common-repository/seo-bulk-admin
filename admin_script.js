function tacwp_postmgrAdminClass(pass){}
if (typeof tacwp_postmgrAdminClass != 'undefined'){
	tacwp_postmgrAdminClass.prototype.loader = function(args){
		var title = 'SEO Bulk Admin';
		if(args.hasOwnProperty('title')){title = args.title;}
		var message = '';if(args.hasOwnProperty('message')){message = args.message;}
		var hasnotice = false;
		var notice = '';
		if(args.hasOwnProperty('notice')){
			hasnotice = true;
			notice = args.notice;
		}
		if(args.hasOwnProperty('response')){
			var response = args.response;
			if(response.indexOf('_[[IFACE]]_') !== -1){
				var splt = response.split('_[[IFACE]]_');
				message = splt[0];
				var dats = splt[1];
				var data = JSON.parse(dats);
				if(data.hasOwnProperty('title')){title = data.title;}
				if(data.hasOwnProperty('notice')){notice = data.notice;}
			}else{
				message = response;
			}
		}
		if(jQuery('#APLOADER').length === 0){
			var txt = '<div id="APLOADER" class="pmgr-loader">';
				txt += '<div class="pmgr-loader-window">';
					txt += '<div class="button pmgr-loader-close" onclick="tacwp_postmgr.loader_remove();">Close</div>';
					txt += '<div id="pmgrLoaderTitle" class="pmgr-loader-title">'+title+'</div>';
					txt += '<div id="pmgrLoaderNotice" class="pmgr-loader-notice">'+notice+'</div>';
					txt += '<div id="pmgrLoaderMessage" class="pmgr-loader-message">'+message+'</div>';
				txt += '</div>';
			txt += '</div>';
			jQuery('body').append(txt);
		}else{
			jQuery('#APLOADER').show();
			if(title!=''){jQuery('#APLOADER').find('.pmgr-loader-title').html(title);}
			if(hasnotice){jQuery('#APLOADER').find('.pmgr-loader-notice').html(notice);}
			if(message!=''){jQuery('#APLOADER').find('.pmgr-loader-message').html(message);}
		}
		var thit = jQuery('#pmgrLoaderTitle').outerHeight();
		var nhit = jQuery('#pmgrLoaderNotice').outerHeight();
		var hit = 30+(thit+nhit);
		var maxhit = 'calc(100% - '+hit+'px)';
		jQuery('#pmgrLoaderMessage').css('max-height',maxhit);
	}
	tacwp_postmgrAdminClass.prototype.loader_remove = function(){
		jQuery('#APLOADER').remove();
	}
	tacwp_postmgrAdminClass.prototype.processing = function(mess){
		var message = tacwp_postmgr.spinner(mess);
		if(jQuery('#APROCESSING').length === 0){
			var txt = '<div id="APROCESSING" class="pmgr pmgr-processing">';
				txt += '<div class="pmgr-processing-window fcol">';
					txt += '<div class="button pmgr-processing-close" onclick="tacwp_postmgr.processing_remove();">Close</div>';
					txt += '<div class="pmgr-processing-message">'+message+'</div>';
				txt += '</div>';
			txt += '</div>';
			jQuery('body').append(txt);
		}else{
			jQuery('#APROCESSING').show();
			if(message!=''){jQuery('#APROCESSING').find('.pmgr-processing-message').html(message);}
		}
	}
	tacwp_postmgrAdminClass.prototype.processing_remove = function(){
		jQuery('#APROCESSING').remove();
	}
	tacwp_postmgrAdminClass.prototype.notice = function(mess,refresh){
		clearTimeout(postmgrTimeout);
		if(jQuery('#APNOTICE').length === 0){
			var txt = '<div id="APNOTICE" class="pmgr-notice">'+mess+'</div>';
			jQuery('body').append(txt);
		}else{
			jQuery('#APNOTICE').html(mess);
		}
		jQuery('#APNOTICE').show();
		var timer = 3000;
		if(tacwp_postmgr.is_numeric(refresh)){timer = refresh*1000;}
		if(mess.substr(0,5)=='ERROR'){jQuery('#APNOTICE').addClass('iserror');}else{jQuery('#APNOTICE').removeClass('iserror');}
		postmgrTimeout = setTimeout(function() {
			clearTimeout(postmgrTimeout);
			jQuery('#APNOTICE').fadeOut('fast');
			if(!tacwp_postmgr.is_numeric(refresh) && refresh===true){window.location.reload();}
		}, timer);
	}
	tacwp_postmgrAdminClass.prototype.spinner = function(pass){
		var RAT = '';
		RAT += '<div id="loading-spinner" class="pmgr-spinner">';
			RAT += '<div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>';
			RAT += '<div id="loading-message">'+pass+'</div>';
		RAT += '</div>';
		return RAT;
	}
	tacwp_postmgrAdminClass.prototype.getUrlVars = function(tab){
		var vars = [];
		var urlqry = window.location.search.substring(1);
		var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
		var len = hashes.length;
		for(var i = 0; i < len; i++){
			var hash = hashes[i].split('=');
			var key = hash[0];
			var val = '';
			if(hash[1]){val = hash[1];}
			vars[key] = val;
		}
		return vars;
	}
	tacwp_postmgrAdminClass.prototype.getUrlVar = function(tab,def){
		var vars = tacwp_postmgr.getUrlVars();
		if(vars[tab]){return vars[tab];}
		return def;
	}
	tacwp_postmgrAdminClass.prototype.isvalid = function(val){
		if(val=='' || val==undefined || val==null){return false;}
		return true;
	}
	tacwp_postmgrAdminClass.prototype.in_array = function(needle, haystack){
		var length = haystack.length;
		for(var i = 0; i < length; i++) {
			if(haystack[i] == needle) return true;
		}
		return false;
	}
	tacwp_postmgrAdminClass.prototype.is_numeric = function(n){
		return !isNaN(parseFloat(n)) && isFinite(n);
	}
	tacwp_postmgrAdminClass.prototype.submitQuery = function(){
		var url = tacwp_postmgr_data_object.adminURL;
		var tab = tacwp_postmgr.getUrlVar('tab');
		if(this.isvalid(tab)){url += '&tab='+tab;}
		var search = jQuery('#search').val();
		if(this.isvalid(search)){url += '&search='+search;}
		var orderby = jQuery('#orderby').val();
		if(this.isvalid(orderby)){url += '&orderby='+orderby;}
		var bytype = jQuery('#bytype').val();
		var before = jQuery('#before').val();
		var after = jQuery('#after').val();
		var searchby = jQuery('#searchby').val();
		var column = jQuery('#column').is(':checked');
		if(bytype=='' || bytype=='post'){
			var category = jQuery('#bycategory').val();
			if(this.isvalid(category)&& category!=0){url += '&category='+category;}
		}
		if(bytype=='product'){
			var woocategory = jQuery('#woocategory').val();
			if(this.isvalid(woocategory)&& woocategory!=0){url += '&woocategory='+woocategory;}
		}
		if(this.isvalid(bytype)){url += '&bytype='+bytype;}
		if(this.isvalid(before)){url += '&before='+before;}
		if(this.isvalid(after)){url += '&after='+after;}
		if(this.isvalid(searchby)){url += '&searchby='+searchby;}
		if(column){url += '&column=post_modified';}
		window.location = url;
	}
	tacwp_postmgrAdminClass.prototype.clearQuery = function(){
		var url = tacwp_postmgr_data_object.adminURL;
		var tab = tacwp_postmgr.getUrlVar('tab');
		if(this.isvalid(tab)){url += '&tab='+tab;}
		window.location = url;
	}
	tacwp_postmgrAdminClass.prototype.showAll = function(){
		jQuery('#search').val('all');
		tacwp_postmgr.submitQuery();
	}
	tacwp_postmgrAdminClass.prototype.is_woo = function(){
		var iswoo = tacwp_postmgr_data_object .iswoo;
		if(tacwp_postmgr.isvalid(iswoo)){
			return true;
		}
		return false;
	}
	tacwp_postmgrAdminClass.prototype.togglePosts = function(){
		if(jQuery('.toggle-posts').hasClass('ison')){
			jQuery('.toggle-posts').removeClass('ison');
			jQuery('.post-checkbox').prop('checked',false);
		}else{
			jQuery('.toggle-posts').addClass('ison');
			jQuery('.post-checkbox').prop('checked',true);
		}
	}
	tacwp_postmgrAdminClass.prototype.deletePost = function(field){
		var obj = jQuery(field);
		var val = obj.is(':checked');
		obj.closest('.process-side').attr('data-delete',val);
	}
	tacwp_postmgrAdminClass.prototype.selectMethod = function(field){
		var obj = jQuery(field);
		var val = obj.val();
		obj.closest('.process-side').attr('data-method',val);
	}
	tacwp_postmgrAdminClass.prototype.selectPostType = function(field){
		var obj = jQuery(field);
		var val = obj.val();
		obj.closest('.pmgr').attr('data-post-type',val);
	}
	tacwp_postmgrAdminClass.prototype.toggleTagCloud = function(field){
		var obj = jQuery(field);
		var tag = obj.attr('data-tagname');
		if(obj.hasClass('ison')){
			obj.removeClass('ison');
			var how = 'remove';
		}else{
			obj.addClass('ison');
			var how = 'add';
		}
		var tags = jQuery('#tags').val();
		if(tags==''){
			var arr = [];
		}else{
			var arr = tags.split(',');
		}
		if(how=='add'){
			if(this.in_array(tag,arr)==false){
				arr.push(tag);
			}
		}else{
			var newarr = [];
			for(i in arr){
				var itag = arr[i];
				if(itag!='' && itag!=tag){newarr.push(itag);}
			}
			arr = newarr;
		}
		console.log(arr);
		var newval = arr.join(',');
		jQuery('#tags').val(newval);
		jQuery('#tags').html(newval);
	}
	tacwp_postmgrAdminClass.prototype.process = function(){
		if(postmgrProcessing==true){
			tacwp_postmgr.notice('Your previous request is still being processed, you cannot interrupt it. If you wish to start a new process you must refresh the page first.');
		}
		var pass = {};
		pass['tab'] = jQuery('.pmgr').attr('data-tab');
		jQuery('.process-side').find('.proc-input').each(function(){
			var obj = jQuery(this);
			var id = obj.attr('id');
			var type = obj.attr('data-proc-type');
			if(type=='select' || type=='text' || id=='topage'){
				pass[id] = jQuery('#'+id).val();
			}else if(type=='checkbox'){
				pass[id] = jQuery('#'+id).is(':checked');
			}
		});
		var category = [];
		jQuery('#tocategories').find('input[type="checkbox"]').each(function(){
			var obj = jQuery(this);
			if(obj.is(':checked')){
				var id = obj.attr('id').replace('in-category-','');
				category.push(id);
			}
		});
		pass['categories'] = category;
		var woocategory = [];
		jQuery('#toproductcat').find('input[type="checkbox"]').each(function(){
			var obj = jQuery(this);
			if(obj.is(':checked')){
				var id = obj.attr('id').replace('in-product_cat-','');
				woocategory.push(id);
			}
		});
		pass['woocategory'] = woocategory;
		var post_type = jQuery('#resultsbox').attr('data-post-type');
		pass['post_type'] = post_type;
		var version = jQuery('#resultsbox').attr('data-version');
		pass['version'] = version;
		var runit = true;
		var posts = [];
		jQuery('.post-checkbox').each(function(){
			var obj = jQuery(this);
			if(obj.is(':checked')){
				var id = obj.attr('id');
				var postid = id.replace('post_','');
				posts.push(postid);
			}
		});
		if(posts.length<1){
			runit = false;
			alert('You must choose at least one post to process.');
		}else{
			pass['posts'] = posts;
		}
		var method = jQuery('#method').val();
		pass['method'] = method;
		if(tacwp_postmgr.isvalid(method)==false){
			runit = false;
			alert('You must choose a batch Process to run.');
		}
		if(typeof tacwp_postmgrproAdminClass.prototype.processFilter === "function"){
			var check = tacwp_postmgrproAdminClass.prototype.processFilter(pass);
			if(check===false){
				runit = false;
			}else{
				pass = check;
			}
		}
		if(method=='category'){
			if(post_type=='product'){
				if(woocategory.length<1){
					runit = false;
					alert('You must choose at least one product category to assign.');
				}
			}else{
				if(category.length<1){
					runit = false;
					alert('You must choose at least one category to assign.');
				}
			}
		}
		if(runit==true){
			postmgrProcessing = false;
			tacwp_postmgr.processing('Processing request, this may take some time depending on the number of posts being processed.');
			var security = tacwp_postmgr_data_object .ajax_nonce;
			jQuery.post(ajaxurl, { 'action': 'tacwp_postmgr_ajax_handler' , 'how': 'process' , 'pass': pass ,'security':security }, function(response) {
				if(response=='OK'){
					tacwp_postmgr.processing('Batch process finished: the page will refresh in a moment.');
					setTimeout(function(){window.location.reload();},1000);
				}else if(response.substr(0,6)=='ERROR:'){
					tacwp_postmgr.notice(response);
					tacwp_postmgr.processing_remove();
				}
			});
		}
	}
	tacwp_postmgrAdminClass.prototype.setupForm = function(){
		var tab = tacwp_postmgr.getUrlVar('tab');
		if(tab==undefined || tab==''){
			jQuery('.search-field').keyup(function(event){
				if(event.which==13){
					event.preventDefault();
					tacwp_postmgr.submitQuery();
				}
			});
		}
	}
	var tacwp_postmgr = new tacwp_postmgrAdminClass();
	var postmgrTimeout;
	var postmgrProcessing = false;
	jQuery(document).ready(function(){ tacwp_postmgr.setupForm(); });
}
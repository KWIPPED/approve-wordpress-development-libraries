window.kwipped_approve = window.kwipped_approve || {};
window.kwipped_approve.ajax_url = php_vars.ajax_url
window.kwipped_approve.tools = {};
window.kwipped_approve.tools.current_items = [];

//Available on the page. Example call
//kwipped_approve.get_teaser("sdfsdfsd").then(function(data){console.log(data)}).catch(function(error){console.log("BOO BOO"+error)});
window.kwipped_approve.tools.get_teaser=function(value){
	var self = this;
	return new Promise(function(resolve,reject){
		var info = {value:value};
		var data = {action: "get_approve_teaser_devtools",data:info};
		jQuery.ajax({
			url:window.kwipped_approve.ajax_url,
			type:'POST',
			data,
			success:function(data){
				resolve(data);
			},
			error:function(error){
				resolve(reject);
			}
		});
	});
}

//e.g.
//window.kwipped_approve.tools.add_item("m1",10000,1,"new_product")
window.kwipped_approve.tools.add_item=function(model,price,qty,type){
	var errors = "";
	var separator = "";
	if(!model){
		errors = errors+separator+"The model field cannot be empty.";
		separator=" ";
	}
	if(!price){
		errors = errors+separator+"The price field cannot be empty.";
		separator=" ";
	}
	price = parseFloat(price);
	if(isNaN(price)){
		errors = errors+separator+"The price field cannot must be a number.";
		separator=" ";
	}
	if(!qty){
		errors = errors+separator+"The qty field cannot be empty.";
		separator=" ";
	}
	if(!type){
		errors = errors+separator+"The type field cannot be empty.";
		separator=" ";
	}
	if(errors){
		return {
			success:false,
			message:errors
		}
	}
	this.current_items.push({
		model:model,
		price:price,
		qty:qty,
		type:type
	});
	return {
		success:true
	}
}

window.kwipped_approve.tools.remove_item=function(index){
	this.current_items.splice(index,1);
}

window.kwipped_approve.tools.clear_items=function(){
	this.current_items = [];
}

//e.g window.kwipped_approve.tools.get_approve_information().then(function(response){console.log(response)});
window.kwipped_approve.tools.get_approve_information = function(){
	var self = this;
	return new Promise(function(resolve,reject){
		var info = {items:self.current_items};
		var data = {action: "get_approve_information_devtools",data:info};
		jQuery.ajax({
			url:window.kwipped_approve.ajax_url,
			type:'POST',
			data,
			success:function(data){
				resolve(data);
			},
			error:function(error){
				resolve(reject);
			}
		});
	});
}

window.kwipped_approve.tools.get_current_items = function(){
	return this.current_items;
}
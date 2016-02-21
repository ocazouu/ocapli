/*

app_model_configs: 
	- Self-generated object available from api ocapli.
	- Contain all of models configs.
	- Set at start from XHR Request 

*/
var app_model_configs;

/* 
	OrmManager: Class extend by RenderForm or RenderList.
*/
var OrmManager = new Class({
	initialize: function(config){
		/* 
			Instantiates OrmManager but does not execute 
			Generaly used for multiple instance with menu, list etc..
		*/
		this.alert_contain = false;
		if(!config){
			return this;
		}
		/* 
			Instance model configs (self-generate columns structures and info from api ocapli) 
		*/
		this.model = this.checkModel([config["model"]]);

		if(!this.model){
			return false;
		}

		this.model_config = app_model_configs[this.model]["columns"];

		/* 
			Essential config: target needs to build the module
		*/
		if(!$(config["target"])){
			this.renderError("Undefined target with OrmManager: " + JSON.encode(this.config));
			return false;
		}

		this.config = config;
		/*
			Render modules of (RenderForm, RenderList) 
		*/
		this.onStart();
	},
	checkModel: function(model){
		/* 
			Essential config: model valid to build the module
		*/
		if(!model){
			this.renderError("Invalid model");
			return false;
		}
		/* 
			Essential config: model needs to be a key of app_model_configs
		*/
		if(!app_model_configs[model]){
			this.renderError("Undefined model");
			return false;
		}

		return app_model_configs[model];
	},
	getApiRequest: function(config){

		if(!config){
			this.renderError("Undefined config with getApiRequest");
			return false;
		}

		var action,method;
		var self   = this;
		var query  = config["url"].parseQueryString();
		var url    = INDEX_API + "?model=" + config["model"] + "&" + config["url"];
		var data   = config["data"];
		var model  = this.checkModel(config["model"]);

		var onComplete = config["onComplete"] ? config["onComplete"] : (function(response){
			configs = self.checkResponse(response);
			if(configs){
				this.renderAlert("Action success");
			}
		});

		if(!model){
			return false;
		}
		if(query["action"]){

			action = query["action"];

			     if(action === "create") {method = "POST";}
			else if(action === "update") {method = "PUT";}
			else if(action === "delete") {method = "DELETE";}
			else                         {method = "GET";}

			new Request({
				url:        url,
				method:     method,
				data:       data,
				onComplete: onComplete
			}).send();
		}
		else{
			self.renderError("Invalid query attributs: action not found.");
			return false;
		}
		return true;
	},
	checkResponse: function(response){
		var configs = JSON.decode(response);
		if(configs && !configs["error"]){
			return configs;
		}
		else if(configs && configs["error_text"]){
			this.renderError(configs["error_text"]);
		}
		else{
			this.renderError("Invalid response from getApiRequest: " + response);
		}
		return false;
	},
	renderAlert: function(text){
		var color = arguments[1] ? arguments[1] : "#02B531"

		if(this.alert_contain){
			this.alert_contain.dispose();
		}
		this.alert_contain = new Element("div", {
			"style": "background:rgba(255,255,255,0.8);position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999"
		}).inject($(document.body));

		var message_contain = new Element("div", {
			"style": "background:#FFF;width:40%;margin:25% auto 0 auto;padding:10px;color:" + color + ";border-radius:5px;box-shadow:0 10px 30px #999",
			"html": text
		}).inject(this.alert_contain);

		setTimeout(function(){
			if(this.alert_contain){
				this.alert_contain.dispose();
			}
		}.bind(this), 1000);
	},
	renderError: function(error){
		this.renderAlert("<b>Error:</b> " + error, "#FB4E4E");
	},
	load: function(config){
		this.initialize(config);
	},
	reload: function(config){
		this.initialize(config);
	}
});

/* 
	RenderForm: Set new form from model configs (global app_model_configs) and custom config (OneOrm.config)

	set à new form exemple:

	var OneOrm = RenderForm({
		model: "Videos",                               // String: name of one app model
		target: "#target_object",                      // String/Object: empty node html.
		target: $("target_object"),                    //                Or an empty object.
		hidden_fields: "id,created_at,updated_at",     // String:  Fields not displayed , no data sent.
		record: "id=3"                                 // QueryString: to edit record (update). filtered with every fields in app_model_configs 
	});

*/

var RenderForm = new Class({
	Extends: OrmManager,
	onStart:function(){

		this.output         = new Element("form", {"class": "form_orm"});
		this.dom_target     = this.config["target"];
		this.record         = this.config["record"] ? this.config["record"] : false;
		this.action         = this.record ? "update" : "create";
		this.fields         = this.config["fields"] ? this.config["fields"] : false;
		this.hidden_fields  = (this.config["hidden_fields"] ? this.config["hidden_fields"] : "").split(",");

		/*
			If record , sets the form with data to update
		*/
		if(this.record){
			this.getApiRequest({
				model: this.model,
				url: "action=index&" + this.record,
				onComplete: this.loadData.bind(this)
			});
		}
		/*
			If no record , sets the form to create
		*/
		else
		{
			return this.render();
		}
		return true;
	},
	loadData: function(response){
		record_data = this.checkResponse(response);
		if(record_data){
			this.record_data = record_data[0];
			this.render();
		}
	},
	sendData: function(ev,el){
		ev.preventDefault();
		var url = "action=" + this.action + (this.record ? ("&" + this.record) : "");
		this.getApiRequest({
			model:  this.model,
			url:  url,
			data: this.output.toQueryString(),
			onComplete:  this[this.record ? "afterUpdate" : "afterCreate"].bind(this) 
		});
	},
	deleteRecord: function(ev,el){
		ev.preventDefault();
		var url = "action=delete" + (this.record ? ("&" + this.record) : "");
		this.getApiRequest({
			model:  this.model,
			url:  url,
			onComplete:  this.afterDelete.bind(this)
		});
	},
	resetForm: function(ev, el){
		ev.preventDefault();
		this.config["record"] = false;
		this.onStart();
	},
	render: function(){
		/* clean last form */
		this.dom_target.set("html", "");

		var fields = this.model_config["structures"];

		var error_with_render_field = false;
		/* Inject fields */
		Object.each(fields, function(field, key){
			if(this.renderFieldByType(field, key) === "critical_error"){
				error_with_render_field = true;
			}
		}.bind(this));

		if(error_with_render_field){
			return false;
		}
		/* Inject action menu */
		this.renderActionMenu();

		/* inject form */
		this.output.inject(this.dom_target);

		return true;
	},
	renderActionMenu: function(){
		var self = this,
		menu_orm = new Element("div", {"class": "menu_orm"});

		new Element("div", {
			"style": "font-size:11px;margin:10px 0 0 0",
			"html": "<i>Exemple de balisage :</i><br /><b>Lien:</b> " + ('<a href="">Le texte du lien</a>').toHtmlEntities() + "<br /><b>Image:</b> " + ('<img src="" />').toHtmlEntities()
		}).inject(menu_orm);
		/*
			Set a new form to create 
		*/
		new Element("a",{
			"class": "button_orm",
			"text":  "New",
			"href":  "#",
			"events": {click: this.resetForm.bind(this)}
		}).inject(menu_orm);
		/* 
			Delete record 
		*/
		if(this.record){
			new Element("a",{
				"class": "button_orm",
				"text":  "Delete",
				"href":  "#",
				"events": {click: this.deleteRecord.bind(this)}
			}).inject(menu_orm);
		}
		/*
			Create or update record 
		*/
		new Element("a",{
			"class": "button_orm",
			"text":  "Send",
			"href":  "#",
			"events": {click: this.sendData.bind(this)}
		}).inject(menu_orm);

		menu_orm.inject(this.output);
	},
	renderContainForm: function(){
		styles = arguments[0] ? arguments[0] : false;
		return new Element("div", {
			"class": "contain_form",
			"style": styles ? styles : false
		});
	},
	renderTitle: function(field, key, type){
		new Element("div", {
			"text":  key.replace("_", " ") + ":", // todo replace_all
			"class": "orm_title"
		}).inject(this.output);
	},
	renderInput: function(field, key){
		var contain_form = this.renderContainForm();
		new Element("input",{
			"name": key,
			"value": this.record ? this.record_data[key] : ""
		}).inject(contain_form);
		contain_form.inject(this.output);
	},
	renderSelect: function(field, key){
		var self         = this;
		var contain_form = this.renderContainForm();
		var select       = new Element("select",{
			"name": key
		});

		var args_enum = field["type"].replace("enum(","").replace(")","");
		var options = args_enum.split(",");

		select.inject(contain_form);

		Array.each(options, function(option){
			var value_option  = option.substring(1,(option.length - 1));
			var render_option = new Element("option", {
				"text": value_option
			});

			if(self.record){
				if(self.record_data[key] === value_option){
					render_option.set("selected", "selected");
				}
			}
			else if(field["default"] === value_option){
				render_option.set("selected", "selected");
			}

			render_option.inject(select);
		});

		contain_form.inject(this.output);
	},
	renderTextarea: function(field, key){
		var contain_form = this.renderContainForm();
		new Element("textarea",{
			"name": key,
			"value": this.record ? this.record_data[key] : ""
		}).inject(contain_form);
		contain_form.inject(this.output);
	},
	renderFieldByType: function(field, key){
		var type;

		field = this.model_config["structures"][key];
		if(!field || this.hidden_fields.contains(key)){return false;}

		type = this.getTypeByField(field, key);

		this.renderTitle(field, key, type);

		     if(type === "input")        {this.renderInput(field, key);}
		else if(type === "textarea")     {this.renderTextarea(field, key);}
		else if(type === "select")       {this.renderSelect(field, key);}
		else if(type === "checkbox")     {this.renderCheckbox(field, key);}
		else if(type === "radio")        {this.renderRadio(field, key);}
		else if(type === "date")         {this.renderDate(field, key);}
		else if(type === "autocomplete") {new RenderAutocomplete(this, this.fields, field, key);}
		else {
			this.renderError("Render field failed, invalid custom type");
			return "critical_error";
		}
		return true;
	},
	getTypeByField: function(field, key){
		/* Check if custom type */
		if(this.fields){
			if(this.fields[key]){
				if(this.fields[key]["type"]){
					return this.fields[key]["type"];
				}
			}
		}
		/* Set type with config structures */
		if(field["is_int"]){
			return "input";
		}else if(field["type"] == "text"){
			return "textarea";
		}else if(field["type"].contains("enum")){
			return "select";
		}else{
			return "input";
		}
	},
	afterCreate: function(response){
		configs = this.checkResponse(response);
		if(configs){
			if(this.config["afterCreate"]){
				this.config["afterCreate"].bind(this)();
			}
			else{
				this.renderAlert("Action create is success.");
				this.output.dispose();
			}
		}
	},
	afterUpdate: function(response){
		configs = this.checkResponse(response);
		if(configs){
			if(this.config["afterUpdate"]){
				this.config["afterUpdate"].bind(this)();
			}
			else{
				this.renderAlert("Action update is success.");
				this.output.dispose();
			}
		}
	},
	afterDelete: function(response){
		configs = this.checkResponse(response);
		if(configs){
			if(this.config["afterDelete"]){	
				this.config["afterDelete"].bind(this)();
			}
			else{
				this.renderAlert("Action delete is success.");
				this.output.dispose();
			}
		}
	}
});

/*

*/
var RenderAutocomplete = new Class({

	initialize: function(orm, config, field, key){

		var self = this;
		var data         = {
			show: "",
			store: ""
		}

		this.list         = false;
		this.key          = key;
		this.config       = config[key];
		this.model        = orm.checkModel(this.config["model"]);
		this.store        = this.config["store"];
		this.show         = this.config["show"];
		this.search_scope = this.config["search_scope"];
		this.boundEventCloseList = this.setEventCloseList.bind(this);

		if(!this.model){
			return false;
		}

		if(!this.show){
			orm.renderError("Missing attributs with Autocomplete config, show is needed");
			return false;
		}

		if(!this.store){
			orm.renderError("Missing attributs with Autocomplete config, store is needed");
			return false;
		}

		if(!this.search_scope){
			this.search_scope = this.show;
		}

		if(!this.store){
			this.store = this.show;
		}


		if(orm.record && orm.record_data[key] != "0" && orm.record_data[key] != ""){

			orm.getApiRequest({
				model: this.model,
				url: "action=index&select[]=" + this.store + "&" + "select[]=" + this.show + "&" + this.store + "=" + orm.record_data[key],
				onComplete: function(response){
					scope = orm.checkResponse(response);
					if(scope && scope[0] && scope[0][self.show]){
						self.filter.set("value", scope[0][self.show]);
						self.store_form.set("value", scope[0][self.store]);
					}
				}
			});

		}

		this.renderForm(orm, data);

		return true;
	},
	renderForm: function(orm, data){

		var sendFilter;
		var self = this;
		var contain_form = orm.renderContainForm("position:relative");

		this.store_form = new Element("input",{
			"type": "hidden",
			"value": data["show"],
			"name": this.key
		});

		this.store_form.inject(contain_form);

		this.filter = new Element("input",{
			"value": data["show"],
			"events": {
				keyup: function(ev){
					clearTimeout(sendFilter);
					sendFilter = setTimeout((function(){
						self.onUseAutocomplete(orm, this.get("value"));
					}).bind(this), 300);
				},
				blur: function(){
					if(this.get("value") == ""){
						self.store_form.set("value", "");	
					}
				}
			}
		});

		this.filter.inject(contain_form);

		contain_form.inject(orm.output);
	},
	onUseAutocomplete: function(orm, value){

		var conditions = "";
		var self = this;

		if(value.length < 2){
			if(this.list){
				this.list.dispose();
			}
			return false;

			if(value.length === 0){
				this.store_form.set("value", 0);
			}
		}

		conditions += "&conditions[0][field]=" + this.search_scope;
		conditions += "&conditions[0][operator]=" + "LIKE";
		conditions += "&conditions[0][value]=" + escape("%" + value + "%");

		orm.getApiRequest({
			url: "action=index" + conditions,
			model: this.model,
			onComplete: function(response){
				results = orm.checkResponse(response);
				if(results){
					self.renderList(orm, results);
				}
			}
		});
	},
	renderList: function(orm, results){

		var self  = this;
		var show  = this.show;
		var store = this.store;
		var doc   = $(document.body);

		if(this.list){
			this.list.dispose();
		}

		if(results.length === 0)
		{
			return false;
		}

		this.list = new Element("div", {
			"class": "list_autocomplete",
			"style": "position:absolute",
		});

		Array.each(results, function(record){

			/* key "show" to force a custom display */
			if(record["show"]){
				result = record["show"];
			}else{
			/* else set the default config box-shadow */
				result = record[self.config["show"]];
			}

			new Element("div",{
				"tabindex": 0,
				"html": result,
				"events": {
					click: function(){
						if(!record[show]){
							orm.renderError("Undefined show value on autocomplete result");
							return false;
						}
						if(!record[store]){
							orm.renderError("Undefined store value on autocomplete result");
							return false;
						}
						self.filter.set("value", record[show])
						self.store_form.set("value", record[store])
					}
				}
			}).inject(self.list);

		});

		this.list.inject(this.filter, "after");
		doc.removeEvent("click", this.boundEventCloseList);
		doc.addEvent("click", this.boundEventCloseList);

	},
	setEventCloseList: function(){
		if(this.list){
			this.list.dispose();
		}
		$(document.body).removeEvent("click", this.boundEventCloseList);
	}
});


var UploadFiles = new Class({
	initialize: function(){

		var targetFormVideo = new Element("div");
		var afterAction = (function(){
			this.renderAlert("Oklm.");
			targetFormVideo.set("html","");
		});

		targetFormVideo.inject($("form_video"),"after");
		var VideosForm = new RenderForm(false);

	},

	renderMenu: function(){

		new Element("a",{
			"class": "button_orm",
			"text":  "add_files",
			"href":  "#",
			"events": {
				"click": function(ev){
					ev.preventDefault();
					VideosForm.load({
						model: "Files",
						target: targetFormVideo,
						hidden_fields: "id,created_at,updated_at",
						afterCreate: afterAction
					});
				}
			}
		}).inject(menu_orm);

	}
});
/* 
	#!! At domready: Instance OrmManager after ormManagerReady !!#

	Instance configs with xhr request.... 
	And fireEvent ormManagerReady. 
*/
(function(){

	if(typeof INDEX_API === "undefined"){
		console.log("Global variable INDEX_API is undefined.");
		return false;
	}

	new Request({
		url : INDEX_API + "?action=get_configs",
		onComplete : function(response){
			app_model_configs = JSON.decode(response);
			if(app_model_configs && !app_model_configs["error"]){
				window.fireEvent("ormManagerReady");
			}
			else if(app_model_configs && app_model_configs["error_text"]){
				console.log(app_model_configs["error_text"]);
			}
			else{
				console.log("Invalid response for instance configs: " + response);
			}
		}
	}).send();

})();


String.prototype.toHtmlEntities = function() {
    return this.replace(/./gm, function(s) {
        return "&#" + s.charCodeAt(0) + ";";
    });
};
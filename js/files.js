var setFilesIframeComplete = (function(obj_return){

	alert(2);

});
var _E = (function(error){
	alert(error);
});

var upfiletool_class = new Class({
/* 
	# 	initialize: Utiliser pour la navigation ajax avec JSON
	# 
	# - Instance en délégation tous les éléments dont ident = upfiletool
	# - Gestion par clic et touche entrer
	# 
*/
	initialize : function(target){
		var self = this;

		target.addEvents({
			'click:relay(*[ident=upfiletool])':function(ev){
				self.clickRelay(ev,this);
			}
			,
			'keyup:relay(*[ident=upfiletool])':function(ev){
				if(ev.key=='enter' || ev.key=='space'){
					self.clickRelay(ev,this);
				}
			}
		});
	},
/* 
	# 	clickRelay: Gestion des éxécutions celon l'attribut identmethod.
	# 
	# 	Utilise identmethod pour executer une methode php nom égale à identmethod. 
	# 	Puis execute sur upfiletool_class une méthode de nom égale à identmethod qui traitera la réponse
	#
	# - Execute la délégation 
	# - Requete ajax: Post sur ocapli/upfiletool/action l'action et le config
	# - Execute la vérification de l'objet json retourné 
	# - Alerte ou execute la méthode
	#
*/
	clickRelay : function(ev,elem){
		var self        = this;
		var identmethod = elem.get('identmethod');
		new Request({
			url        : INDEX + 'ocapli/upfiletool/action',
			method     : 'post',
			data       : {
				'config'      : elem.get('config'),
				'method'      : identmethod,
				'identconfig' : elem.get('identconfig') ? elem.get('identconfig') : false,
				'identrecord' : elem.get('identrecord') ? elem.get('identrecord') : false,
				'identfile'   : elem.get('identfile')   ? elem.get('identfile')   : false
			},
			onComplete : function(response){
				var obj_return = self.preExecResponse(response);

				if(!obj_return){return false}

				if(typeof(self[identmethod]) == 'function'){
					self[identmethod](obj_return,elem);
				}
				else{
					_E("Erreur: Ce type d'evenement n'existe pas");
				}
			}
		}).send();
	},
/* 
	# 	preExecResponse:
	#
	# - Alerte ou supprime celon then
	# - Si demande par index exec: Execute la fonction si elle existe 
	#
*/
	preExecResponse : function(response){
		var obj_return = JSON.decode(response);
		if(!obj_return){
			_E('Erreur: Problème avec la gestion de la config upfiletool' + "\n\nReponse:" + response);
			return false;
		}
		if(obj_return['exec']){
			window[obj_return['exec']]();
		}
		if(obj_return['error']){
			if(obj_return['global']){
				_E(obj_return.error_txt);
			}
			return false;
		}
		if(!obj_return['config']){
			_E("Erreur: config non défini" + "\n\nMethode:" + obj_return['method'] + "\n\nReponse:" + response);
			return false;
		}
		return obj_return;
	},
/* 
	# 	render_global_view: Menu, Liste, Edition
	#
	# - Construction la fenetre global, supprime existante
	# - Si au moins deux configs: Construction du menu
	# - Si existe: Construction de la liste de fichiers
	# - Si existe: Construction de l'édition de fichier
	# 
*/
	render_global_view : function(obj_return,elem){
		var self = this;
		/* Si elle existe: Supprime la précédente fenetre au rappel de la méthode */
		var render_global_view = self.getGlobalViewThen(obj_return['config'],obj_return['identrecord'],'dispose');

		/* Construction de la fenetre global */
		render_global_view = new Element('div',{
			'identupfile' : 'render_global_view',
			'class'       : 'render_global_view',
			'identrecord' : obj_return['identrecord'],
			'config' 	  : obj_return['config']
		}).inject(elem,'after');

		var identconfig = 0;
		/* Construction du menu: boutons pour chaque config */
		if(obj_return['configs'].length > 1){
			obj_return['configs'].each(function(config){
				new Element('button',{
					'ident'       : 'upfiletool',
					'identtype'   : 'menu_config',
					'config'      : obj_return['config'],
					'identconfig' : identconfig,
					'identrecord' : obj_return['identrecord'],
					'text'        : config['name'],
					'identmethod' : config['multiple'] == 'multiple' ? 'render_list_and_edit_view' : 'render_edit_view',
					'style' 	  : 'margin:0 0 0 5px'
				}).inject(render_global_view);
				identconfig++;
			});
		}

		/* Construction fenetre vide pour liste et edition */
		new Element('div',{'identupfile' : 'list_and_edit_view'}).inject(render_global_view);
		new Element('div',{'class' : 'clear'}).inject(render_global_view);

		if(obj_return['render_list_view']){
			self.render_list_view(obj_return['render_list_view']);
		}
		if(obj_return['render_edit_view']){
			self.render_edit_view(obj_return['render_edit_view']);
		}
	},
/* 
	# 	render_list_and_edit_view:
	# - 
*/
	render_list_and_edit_view : function(obj_return){
		this.render_list_view(obj_return['render_list_view']);
		this.render_edit_view(obj_return['render_edit_view']);
	},
/* 
	# 	render_list_view:
	# 
	# - Construction fenetre de liste, supprime existante
	# - Construction de la liste
	# - 
*/
	render_list_view : function(obj_return){
		var self = this;
		var render_global_view = self.getGlobalViewThen(obj_return['config'],obj_return['identrecord'],'alert');
		if(!render_global_view){return false}

		var render_list_view = render_global_view.getElement('div[identupfile=render_list_view]');

		/* Si elle existe: Supprime la précédente fenetre au rappel de la méthode */
		if(render_list_view){render_list_view.dispose()}

		/* Creation de la fenetre de liste */
		var render_list_view = new Element('div',{
			'identupfile' : 'render_list_view',
			'class'       : 'render_list_view'
		}).inject(render_global_view.getElement('[identupfile=list_and_edit_view]'),'top');

		if(obj_return['files'].length == 0){
			new Element('div',{
				'text'   : 'Aucun fichier dans la liste.' 
			}).inject(render_list_view)
		}
		else{

			obj_return['files'].each(function(file){
				new Element('li',{
					'ident'       : 'upfiletool',
					'config'      : obj_return['config'],
					'identconfig' : obj_return['identconfig'],
					'identrecord' : obj_return['identrecord'],
					'identfile'   : file.id,
					'html'        : file.name,
					'identmethod' : 'render_edit_view'
				}).inject(new Element('ul').inject(render_list_view));
			});
		}
	},
/* 
	# 	render_edit_view:
	# 
	# - Construction fenetre d'edition, supprime existante
	# - Gestion affichage liste et menu celon config du fichier
	# - Inject l'iframe contenant le formulaire
*/
	render_edit_view : function(obj_return){
		var self = this;
		var render_global_view = self.getGlobalViewThen(obj_return['config'],obj_return['identrecord'],'alert');
		if(!render_global_view){return false}
		
		/* Si mode multiple = none: Supprime fenetre de liste  */
		if(obj_return['multiple'] == 'none'){
			var render_list_view = render_global_view.getElement('div[identupfile=render_list_view]');
			if(render_list_view){render_list_view.dispose()}
		}

		/* Marque le menu de la config chargé */
		if(render_global_view.getElement('button[identtype=menu_config]')){
			render_global_view.getElements('button[identtype=menu_config]').removeClass('active');
			render_global_view.getElement('button[identtype=menu_config][identconfig=' + obj_return['identconfig'] + ']').addClass('active');
		}

		var render_edit_view = render_global_view.getElement('div[identupfile=render_edit_view]');

		/* Si elle existe: Supprime la précédente fenetre au rappel de la méthode */
		if(render_edit_view){render_edit_view.dispose()}

		/* Creation de la fenetre d'edition */
		var render_edit_view = new Element('div',{
			'identupfile' : 'render_edit_view',
			'class'       : 'render_edit_view'
		}).inject(render_global_view.getElement('[identupfile=list_and_edit_view]'),'bottom');

		var myIFrame = new IFrame({
		    src: INDEX + 'ocapli/upfiletool/upload_form?identfile=' + obj_return['identfile'] + '&config=' + obj_return['config'] + '&identrecord=' + obj_return['identrecord'] + '&identconfig=' + obj_return['identconfig'],
		    styles: {
		        width: '101%',
		        height: 0,
		        border: 'none',
		        overflow: 'hidden'
		    },
		    events: {

		    }
		});

		myIFrame.inject(render_edit_view);
	},
/* 
	# 	getGlobalViewThen:
	# 
	# - Chercher et retourne l'objet ou false
	# - Alerte ou supprime celon then 
	#
*/
	getGlobalViewThen : function(config, identrecord, then){
		var render_global_view = doc.getElement('[identupfile=render_global_view][config=' + config + '][identrecord=' + identrecord + ']');
		if(then == 'dispose'){
			if(render_global_view){render_global_view.dispose()}
		}
		if(render_global_view){
			return render_global_view
		}
		else if(then=='alert'){
			_E('Erreur sur getGlobalViewThen : config ' + config + '  non trouvé');
		}
		return false;
	},
/* 
	# 	iframeComplete:
	# 
	#   Gestion des évenements retourné par le formulaire d'upload
	#   
	#   - Set la hauteur de l'iframe celon la taille retourné
	#   - Test et rappelle le rafrachissement des zones retourné
	#
*/
	iframeComplete : function(obj_return){
		/*__(obj_return);*/
		var self = this;
		var render_global_view = self.getGlobalViewThen(obj_return['config'],obj_return['identrecord'],'');
		render_global_view.getElement('iframe').setStyle('height',obj_return['framesizey']);

		if(obj_return['render_list_view']){
			self.render_list_view(obj_return['render_list_view']);
		}
		if(obj_return['render_edit_view']){
			self.render_edit_view(obj_return['render_edit_view']);
		}
		if(obj_return['render_global_view']){
			self.render_global_view(obj_return['render_global_view'],render_global_view.getPrevious());
		}
	}
});
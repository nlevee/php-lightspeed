(function(urlRoot, tplPrefix) {
	ObsShell.models.ServerHttpConfig = Backbone.Model.extend({
		defaults: {
			"filename": "file.conf",
			"description": "ENV / SYS / file.conf",
			"content": ""
		}
	});

	ObsShell.views.FormServerHttpConfig = Backbone.View.extend(
		_.extend(ObsShell.mixins.Modal, {
			template: _.template($('#'+tplPrefix+'form').html())
		})
	);


	ObsShell.views.LineServerHttpConfig = Backbone.View.extend({
		tagName: 'tr',
		template: _.template($('#'+tplPrefix+'line').html()),
		events: {
			'click a[data-js-action]': 'dispatchJsAction'
		},

		initialize: function(){
			// action au destroy
			this.model.on('destroy', _.bind(function() {
				this.remove();
			}, this));
			// action au change
			this.model.on('change', _.bind(function() {
				this.render();
			}, this));
		},

		render: function() {
			this.$el.html(this.template(this.model.toJSON()));
			return this;
		},

		dispatchJsAction: function(e) {
			var action = $(e.currentTarget).attr('data-js-action');
			return this[action + 'Action'].call(this, e);
		},

		destroyAction: function() {
			this.model.destroy({
				error: function(model, xhr) {
					console.log('no delete', model.toJSON(), xhr);
				}
			});
			return false;
		},
		editAction: function() {
			var model = this.model,
				view = new ObsShell.views.FormServerHttpConfig({
					model: model
				});
			$(document.body).append(view.render().el);
			view.show();
			return false;
		}
	});

	ObsShell.collections.ServerHttpConfig = Backbone.Collection.extend({
		model: ObsShell.models.ServerHttpConfig,
		url: urlRoot
	});

	ObsShell.views.ListServerHttpConfig = Backbone.View.extend({
		el: $('#serverhttpconfig-table tbody'),

		initialize:function(){
			this.collection = new ObsShell.collections.ServerHttpConfig();
			this.collection.fetch();
			this.render();

			this.collection.on('add', this.renderConf, this);
			this.collection.on('reset', this.render, this);
		},

		render: function() {
			_.each(this.collection.models, function(item){
				this.renderConf(item);
			}, this);
		},

		renderConf: function(item) {
			var view = new ObsShell.views.LineServerHttpConfig({
				model: item
			});
			this.$el.append(view.render().el);
		}
	});

	new ObsShell.views.ListServerHttpConfig();
})('ServerHttpConfig/', 'tpl-serverhttpconfig-');

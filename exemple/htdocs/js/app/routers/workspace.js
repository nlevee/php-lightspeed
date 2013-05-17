// creation des routers
(function(){
	ObsShell.routers.Workspace = Backbone.Router.extend({
		routes: {
			':element/:id/': 'viewElement',
			':element/': 'viewCollection',
			'.*': 'home'
		},

		home: function() {
			console.log('home');
		},

		viewCollection: function(element) {
			console.log(element);
		},

		viewElement: function(element, id) {
			console.log(element, id);
		}
	});
})();
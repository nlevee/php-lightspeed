var ObsShell = ObsShell || {};

// creation des branches
ObsShell.routers = ObsShell.routers || {};
ObsShell.models = ObsShell.models || {};
ObsShell.views = ObsShell.views || {};
ObsShell.collections = ObsShell.collections || {};
ObsShell.mixins = ObsShell.mixins || {};

ObsShell.mixins.InHideView = {
	tagName: 'div',

	dependOn: undefined,

	show: function(callback, force) {
		if (this.dependOn != undefined && force !== true)
			this.dependOn.show(_.bind(this.show, this, callback, true));
		else {
			this.$el.removeClass('hide');
			this.el.offsetWidth;
			this.$el.transitionEnd(callback).addClass('in');
		}
		return this;
	},

	hide: function(callback) {
		this.$el.transitionEnd(_.bind(function(e) {
			this.remove();
			if (this.dependOn != undefined)
				this.dependOn.hide(callback && _.bind(callback, this, e));
			else
				callback != undefined && callback.call(this, e);
		}, this)).removeClass('in');
		return this;
	}
};

// creation de l'element backdrop
ObsShell.views.Backdrop = Backbone.View.extend(
	_.extend(ObsShell.mixins.InHideView, {
		className: 'modal-backdrop fade hide'
	})
);

// creation de l'element modal dans les views
ObsShell.mixins.Modal = _.extend(ObsShell.mixins.InHideView, {
	className: 'form modal hide fade',
	events: {
		'click [data-js-modal]': 'dispatchJsModal'
	},

	render: function() {
		this.dependOn = new ObsShell.views.Backdrop();
		$(document.body).append(this.dependOn.render().el);
		this.$el.html(this.template(this.model.toJSON()));
		return this;
	},

	dispatchJsModal: function(e) {
		this.hide();
		return false;
	}
});

//pads left
String.prototype.rpad = function(padString, length) {
	var str = this, length = str.length + length;
	while (str.length < length)
		str = str + padString;
	return str;
};

/**
 * Ajout des fonction transitionEnd/animationEnd
 */
(function($){
	// init des vars interne
	var eventPrefix, testEl = document.createElement('div'),
		vendors = { Webkit: 'webkit', Moz: '', O: 'o', ms: 'MS' };
	// selection du prefix
	$.each(vendors, function(vendor, event){
		if (testEl.style[vendor + 'TransitionProperty'] !== undefined) {
			eventPrefix = event
			return false
		}
	});
	// normalize l'event Name
	function normalizeEvent(name) { return eventPrefix ? eventPrefix + name : name.toLowerCase() }
	// reset element de test
	testEl = null;
	// ajout des fonctions sur l'element
	$.fn.transitionEnd = function(callback){
		callback && this.one(normalizeEvent('TransitionEnd'), callback);
		return this;
	};
	$.fn.animationEnd = function(callback){
		callback && this.one(normalizeEvent('AnimationEnd'), callback);
		return this;
	};
})(Zepto);

/**
 * Ajout de la capture de tab dans les textarea
 */
$(function(){
	$(document).on('keydown', 'textarea', function(e){
		var keyCode = e.keyCode || e.which;
		if (keyCode == 9) {
			e.preventDefault();
			var $this = $(this), $first = $this.get(0),
				start = $first.selectionStart,
				end = $first.selectionEnd;
			// set textarea value to: text before caret + tab + text after caret
			$this.val($this.val().substring(0, start)
				+ "\t"
				+ $this.val().substring(end));
			// put caret at right position again
			$first.selectionStart = $first.selectionEnd = start + 1;
		} else if (keyCode == 13) {
			e.preventDefault();
			var $this = $(this), $first = $this.get(0),
				start = $first.selectionStart,
				end = $first.selectionEnd;

			// if tab on start
			var numtab = $this.val().substring(0, start).split("\n").pop().match(/^\t+/g);
			numtab = numtab != null ? numtab[0].split("\t").length-1 : 0;
			console.log(numtab);
			// set textarea value to: text before caret + tab + text after caret
			$this.val($this.val().substring(0, start)
				+ "\n".rpad("\t", numtab)
				+ $this.val().substring(end));
			// put caret at right position again
			$first.selectionStart = $first.selectionEnd = start + 1 + numtab;
		}
	});
});

$(function(){
	// create router
	var app = new ObsShell.routers.Workspace(),
		dataAction = 'data-js-action';

	// action on a:click
	$(document).on('click', 'a['+dataAction+']', function(e) {
		var $this = $(this);
		if ($this.attr(dataAction) == 'follow') {
			app.navigate($this.attr('href'), {trigger: true});
			return false;
		}
	});

	// start pushstate check
	Backbone.history.start({
		silent: true, // already on page
		pushState: true, // use pushstate
		root: "/~nlevee/00_ObsShell/htdocs/" // root path
	});
});
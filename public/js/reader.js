;
$(function()
{
	$.widget( 'molkobain.pixbox_reader',
		{
			options: {
				endpoints: {
					get_messages: null,
				}
			},

			// the constructor
			_create: function()
			{
				var me = this;

				this.element
					.addClass('molkobain-pixbox-reader')
					.attr('id', 'molkobain-pixbox-reader');

				this._bindEvents();

				this._super();
			},
			_bindEvents: function()
			{
				var me = this;

				// Ensure to be in fullscreen mode all the time
				this.element.on('click', function(oEvent){
					//me._enterFullscreen();
				});
				// Start app
				this.element.find('#mpr-kickstart-button').on('click', function(oEvent){
					oEvent.preventDefault();

					me._startApp();
				});
			},

			// events bound via _bind are removed automatically
			// revert other modifications here
			_destroy: function()
			{
				this.element.removeClass('molkobain-pixbox-reader');
			},
			// _setOptions is called with a hash of all options that are changing
			// always refresh when changing options
			_setOptions: function()
			{
				this._superApply(arguments);
			},
			// _setOption is called for each individual option that is changing
			_setOption: function( key, value )
			{
				this._super( key, value );
			},

			_startApp: function()
			{
				this._hideKickstarter();
				this._showLoader();
			},
			_getNextMessages: function()
			{
				$.post(
					this.options.endpoints.get_messages,
					{}
				)
					.success(function(){})
					.fail(function(){})
					.always(function(){});
			},

			// Helpers
			_enterFullscreen: function()
			{
				this._requestFullscreen(document.getElementById('molkobain-pixbox-reader'));
			},
			_showLoader: function()
			{
				this.element.find('.mpr-loader').show('fade', 1500);
			},
			_hideLoader: function()
			{
				this.element.find('.mpr-loader').hide('fade', 400);
			},
			_hideKickstarter: function()
			{
				this.element.find('.mpr-kickstart').hide();
			},
			// Note: oElem MUST be a DOM element, not a jQuery object
			_requestFullscreen: function(oElem)
			{
				if (oElem.requestFullscreen)
				{
					oElem.requestFullscreen();
				} else if (oElem.webkitRequestFullscreen)
				{
					var a = oElem.webkitRequestFullscreen();
				} else if (oElem.mozRequestFullScreen)
				{
					oElem.mozRequestFullScreen();
				} else if (oElem.msRequestFullscreen)
				{
					oElem.msRequestFullscreen();
				} else
				{
					if (window.console)
					{
						console.log('Fullscreen API is not supported.');
					}
				}
			},

			getOptions: function()
			{
				return this.options;
			},
		});
});

;
$(function()
{
	$.widget( 'molkobain.pixbox_reader',
		{
			options: {
				debug: true,
				list_refresh_delay: 60, // In seconds
				app_loader_delay: 3,    // In seconds
				endpoints: {
					get_posts: null,
				},
			},

			player: null,

			// the constructor
			_create: function()
			{
				var me = this;

				this.element
					.addClass('molkobain-pixbox-reader');

				this._bindEvents();

				this._super();
			},
			_bindEvents: function()
			{
				var me = this;

				// Ensure to be in fullscreen mode all the time
				this.element.on('click', function(oEvent){
					if(!me.options.debug)
					{
						me._enterFullscreen();
					}
				});
				// Start app
				this.element.find('#mpr-kickstart-button').on('click', function(oEvent){
					oEvent.preventDefault();

					me._startApp();
				});
				// Open post
				this.element.find('#mpr-post-list').on('click', '.mpr-post-list-item', function(oEvent){
					oEvent.preventDefault();
					me._onOpenPost($(this).attr('data-id'));
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
				var me = this;

				this._hideKickstarter();

				// Show loader artificially for a few seconds so the user can read the text
				var iTimeout = this.options.debug ? 0 : (this.options.app_loader_delay * 1000);
				this._showAppLoader();
				setTimeout(function(){
					me._getNextPosts();
				}, iTimeout);
			},
			_getNextPosts: function()
			{
				var me = this;

				// Hide list while loading posts
				this._hidePostList();

				// Load posts from server
				this._trace('Loading posts from server');
				$.post(
					this.options.endpoints.get_posts,
					{}
				)
					.done(function(oResponse){
						me._onGetNextPostsDone(oResponse);
					})
					.fail(function(oXHR, sStatus, sErrorMessage){
						me._onGetNextPostsFail(sErrorMessage);
					})
					.always(function(){
						// Hide loader
						me._hideAppLoader();
						// Make sure reader is visible
						me.element.find('#mpr-reader').show();
						// Show list again
						me._showPostList();
						// Plan next refresh
						setTimeout(function(){
							//me._getNextPosts();
						}, me.options.list_refresh_delay * 1000);
					});
			},

			// Callbacks
			_onGetNextPostsDone: function(oData)
			{
				this._trace('Received '+oData.count+' posts from server');

				this._clearPostList();
				for(var iPostId in oData.items)
				{
					this._addPostTolist(oData.items[iPostId]);
				}
			},
			_onGetNextPostsFail: function(sErrorMessage)
			{
				this._trace('Could not load posts, error: ' + sErrorMessage);
			},
			_onOpenPost: function(sPostId)
			{
				var me = this;

				// First clean player, just in case
				this._clearPlayer();

				// Retrieve player
				var oPlayerElem = this.element.find('#mpr-post-player');
				// Retrieve post
				var oPostElem = this.element.find('#mpr-post-list .mpr-post-list-item[data-id="'+sPostId+'"]');

				// Build player
				// - Message
				var sMessage = oPostElem.find('.mpr-pli-message').html();
				// - Medias
				oPostElem.find('.mpr-pli-medias > li').each(function(){
					var oMediaElem = $('<img />');
					var sMediaId = $(this).attr('data-id');
					var sMediaType = $(this).attr('data-type');
					var sMediaMimeType = $(this).attr('data-mimetype');
					var sMediaUrl = $(this).attr('data-url');

					if(sMediaType === 'image')
					{
						oMediaElem
							.attr('src', sMediaUrl)
							.attr('data-image', sMediaUrl);
					}
					else if(sMediaType === 'video')
					{
						oMediaElem
							.attr('data-type', 'html5video')
							.attr('data-' + sMediaMimeType.replace('/', ''), sMediaUrl)
							.attr('data-image', 'https://www.molkobain.com/support/env-production/branding/login-logo.png?t=1574449634.1035');
					}

					oMediaElem
						.attr('data-description', $(this).attr('data-message'))
						.appendTo(oPlayerElem);

				});

				// Start player
				this.player = oPlayerElem.unitegallery({
					gallery_width: window.innerWidth,
					gallery_height: window.innerHeight,
					slider_enable_arrows: false,
					slider_enable_progress_indicator: false,
					slider_enable_play_button: false,
					slider_enable_fullscreen_button: false,
					slider_enable_zoom_panel: false,
					grid_num_cols: 1,
					gridpanel_enable_handle: false,
					strippanel_enable_handle: false,
				});
				this.player.enterFullscreen();
				// oPlayerElem.addClass('mpr-opened');
			},

			// Helpers
			_clearPlayer: function()
			{
				this.element.find('#mpr-post-player')
					.removeClass('mpr-opened')
					// It's content
					.html('')
					// Any bindings / widgets
					.removeData();

				this.player = null;
			},
			_clearPostList: function()
			{
				this.element.find('#mpr-post-list').html('');
			},
			_addPostTolist: function(oPost)
			{
				var oPostListElem = this.element.find('#mpr-post-list');
				var oPostListItemElem = oPostListElem.find('.mpr-post-list-item[data-id="'+oPost.id+'"]');

				// Create element if not already there
				if(oPostListItemElem.length === 0)
				{
					oPostListItemElem = this._cloneTemplate('.mpr-post-list-item');
					oPostListItemElem
						.attr('data-id', oPost.id)
						.appendTo(oPostListElem);
				}

				// Update post data
				oPostListItemElem.find('.mpr-pli-author').attr('data-name', oPost.author.name);
				oPostListItemElem.find('.mpr-pli-author-picture').attr('src', oPost.author.picture_url);
				oPostListItemElem.find('.mpr-pli-message').text(oPost.message);
				oPostListItemElem.find('.mpr-pli-date').text(oPost.date);
				// - Medias
				for(var iMediaId in oPost.medias.items)
				{
					var oMedia = oPost.medias.items[iMediaId];
					var oMediaElem = $('<li></li>');
					for(var sProp in oMedia)
					{
						oMediaElem.attr('data-'+sProp, oMedia[sProp]);
					}

					// if(oMedia.type === 'video')
					// {
					// 	oMediaElem = $('<video>');
					// 	$('<source />')
					// 		.attr('src', oMedia.url)
					// 		.attr('type', oMedia.mimetype)
					// 		.appendTo(oMediaElem);
					// }
					// else if(oMedia.type === 'image')
					// {
					// 	oMediaElem = $('<img />')
					// 		.attr('src', oMedia.url);
					// }
					oMediaElem.appendTo(oPostListItemElem.find('.mpr-pli-medias'));
				}
			},
			_cloneTemplate: function(sSelector)
			{
				var oTemplateElem = this.element.find('#mpr-templates > '+sSelector);
				if(oTemplateElem.length === 0)
				{
					this._trace('Could not clone template `'+sSelector+'` as it was not found.');
					return false;
				}

				return oTemplateElem.clone();
			},
			_showPostList: function()
			{
				var oPostListElem = this.element.find('#mpr-post-list');
				if(!oPostListElem.is(':visible'))
				{
					oPostListElem.show('fade', 400);
				}
			},
			_hidePostList: function()
			{
				var oPostListElem = this.element.find('#mpr-post-list');
				if(oPostListElem.is(':visible'))
				{
					oPostListElem.hide('fade', 400);
				}
			},
			_showEmptyListPlaceholder: function()
			{

			},
			_showAppLoader: function()
			{
				// Note: Here we use a CSS transition to fade because otherwise the element is not properly centered
				// due to jQuery setting a "display: block" property, replacing the "display: flex"...
				var oLoaderElem = this.element.find('#mpr-loader');
				oLoaderElem.addClass('mp-opaque');
			},
			_hideAppLoader: function()
			{
				// Note: Here we use a CSS transition to fade because otherwise the element is not properly centered
				// due to jQuery setting a "display: block" property, replacing the "display: flex"...
				var oLoaderElem = this.element.find('#mpr-loader');
				oLoaderElem.removeClass('mp-opaque');
			},
			_hideKickstarter: function()
			{
				this.element.find('#mpr-kickstart').hide();
			},
			_enterFullscreen: function()
			{
				this._requestFullscreen(document.getElementById('molkobain-pixbox-reader-container'));
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
					this._trace('Fullscreen API is not supported.');
				}
			},
			_trace: function(sMessage)
			{
				if(this.options.debug && window.console)
				{
					window.console.log('Pixbox: ' + sMessage);
				}
			},

			getOptions: function()
			{
				return this.options;
			},
		});
});

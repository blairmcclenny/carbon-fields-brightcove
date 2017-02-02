window.carbon = window.carbon || {};

(function($) {

	var carbon = window.carbon;

	if (typeof carbon.fields === 'undefined') {
		return false;
	}

	carbon.fields.Model.Brightcove = carbon.fields.Model.extend({
		initialize: function() {
			carbon.fields.Model.prototype.initialize.apply(this);
		},
	});


	carbon.fields.View.Brightcove = carbon.fields.View.extend({
		events: function() {
			return _.extend({}, carbon.fields.View.prototype.events, {
				'click .carbon-file-remove': 'fileRemove',
			});
		},

		initialize: function() {
			carbon.fields.View.prototype.initialize.apply(this);

			this.listenTo(this.model, 'change:value', this.handleChange);

			this.on('field:rendered', this.initField);
		},

		handleChange: function() {
			var value = this.model.get('value');
			if (value) {
                var regex = /video_id="([0-9]+)"/;
                var matches = regex.exec(value);

                var thumbnail = $('.attachment.brightcove[data-id="' + matches[1] + '"] .thumbnail img').attr('src');
                this.$el.find('.thumbnail-image').attr('src', thumbnail);
                this.$el.find('.carbon-description, .carbon-attachment-preview').removeClass('hidden');
            }
		},

		fileRemove: function() {
            this.$el.find('.carbon-file-field').val('');
            this.$el.find('.carbon-file-field').change();
            this.$el.find('.thumbnail-image').attr('src', '');
            this.$el.find('.carbon-description, .carbon-attachment-preview').addClass('hidden');
		},
	});

}(jQuery));
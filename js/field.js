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
                'click .carbon-file-remove': 'resetValue',
                'change input.carbon-file-field': 'sync',
            });
        },

        initialize: function() {
            carbon.fields.View.prototype.initialize.apply(this);

            this.listenTo(this.model, 'change:value change:player_id change:account_id change:video_id', this.updateInput);
            this.listenTo(this.model, 'change:video_id', this.updateThumbnail);

            this.on('field:rendered', this.initField);
        },

        convertShortCodeToData: function(shortcode) {
            var regex = /\[bc_video video_id="(\d+)" account_id="(\d+)" player_id="(\w+)"\]/;
            return regex.exec(shortcode);
        },

        resetValue: function() {
            this.$el.find('.carbon-file-field').val('');
            this.$el.find('.carbon-file-field').change();
        },

        updateThumbnail: function() {
            var id = this.model.get('video_id');
            var img = this.$el.find('img');
            var preview = this.$el.find('.carbon-description, .carbon-attachment-preview');

            if (id) {
                var thumbnail = $('.attachment.brightcove[data-id="' + id + '"] .thumbnail img').attr('src');

                img.attr('src', thumbnail);
                preview.removeClass('hidden');
            } else {
                img.attr('src', '');
                preview.addClass('hidden');
            }
        },

        updateInput: function(model) {
            var name = model.get('name');

            for (var key in model.changed) {
                if (!model.changed.hasOwnProperty(key)) {
                    continue;
                }

                var $input = this.$(':input[name="' + name + '[' + key + ']"]');
                var value = model.changed[key];

                if ($input.length) {
                    $input.val(value);
                }
            }
        },

        sync: function() {
            var shortcode = this.$el.find('input.carbon-file-field').val();
            var matches = this.convertShortCodeToData(shortcode);

            if (matches) {
                this.model.set({
                    value: matches[3] + ',' + matches[2] + ',' + matches[1],
                    player_id: matches[3],
                    account_id: matches[2],
                    video_id: matches[1],
                });
            } else {
                this.model.set({
                    value: '',
                    player_id: 'default',
                    account_id: '',
                    video_id: '',
                });
            }
        },
    });

}(jQuery));
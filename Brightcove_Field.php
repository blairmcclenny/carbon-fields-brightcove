<?php

namespace Carbon_Fields\Field;

class Brightcove_Field extends Field {
    /**
     * Returns an array that holds the field data, suitable for JSON representation.
     * This data will be available in the Underscore template and the Backbone Model.
     *
     * @param bool $load  Should the value be loaded from the database or use the value from the current instance.
     * @return array
     */
    public function to_json( $load ) {
        $field_data = parent::to_json( $load );

        $thumb_url = '';
        $value = $this->get_value();

        if ( $value ) {
            $atts = shortcode_parse_atts( $value );
            $images = (new \BC_CMS_API())->video_get_images($atts['video_id']);
            $thumb_url = $images['thumbnail']['src'];
        };

        $field_data = array_merge( $field_data, array(
            'thumb_url' => $thumb_url,
        ) );

        return $field_data;
    }

    /**
     * The main Underscore template of this field
     **/
    public function template() {
        ?>
        <div class="carbon-attachment">
            <input
                    id="{{ id }}"
                    type="hidden"
                    name="{{ name }}"
                    value="{{ value }}"
                    class="regular-text carbon-file-field"
            />

            <div class="carbon-description {{{ value ? '' : 'hidden' }}}">
                <div class="carbon-attachment-preview {{{ thumb_url ? '' : 'hidden' }}}">
                    <img src="{{ thumb_url }}" class="thumbnail-image" />
                    <div class="carbon-file-remove dashicons-before dashicons-no-alt"></div>
                </div>
            </div>

            <span data-target="#{{{ id }}}" class="button brightcove-add-media">
                Select File
            </span>
        </div>
        <?php
    }

    /**
     * admin_enqueue_scripts()
     *
     * This method is called in the admin_enqueue_scripts action. It is called once per field type.
     * Use this method to enqueue CSS + JavaScript files.
     *
     */
    public static function admin_enqueue_scripts() {
        wp_enqueue_script( 'carbon-field-Brightcove', plugin_dir_url( __FILE__ ) . 'js/field.js', array( 'carbon-fields' ) );
    }
}

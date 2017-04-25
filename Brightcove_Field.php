<?php

namespace Carbon_Fields\Field;

class Brightcove_Field extends Field {
    protected $shortcode = null;
    protected $shortcode_default = '';

    protected $player_id  = null;
    protected $player_id_default = 'default';

    protected $account_id  = null;
    protected $account_id_default = '';

    protected $video_id  = null;
    protected $video_id_default = '';

    /**
     * Returns an array that holds the field data, suitable for JSON representation.
     * This data will be available in the Underscore template and the Backbone Model.
     *
     * @param bool $load  Should the value be loaded from the database or use the value from the current instance.
     * @return array
     */
    public function to_json( $load ) {
        $field_data = parent::to_json( $load );

        if ( is_int( $this->video_id ) ) {
            $images = (new \BC_CMS_API())->video_get_images($this->video_id);
            $thumb_url = is_array($images) && isset($images['thumbnail']['src']) ? $images['thumbnail']['src'] : '';
        };

        $field_data = array_merge( $field_data, array(
            'shortcode' => is_string( $this->shortcode ) ? $this->shortcode : $this->shortcode_default,
            'player_id' => is_string( $this->player_id ) ? $this->player_id : $this->player_id_default,
            'account_id' => is_int( $this->account_id ) ? $this->account_id : $this->account_id_default,
            'video_id' => is_int( $this->video_id ) ? $this->video_id : $this->video_id_default,
            'thumb_url' => isset( $thumb_url ) ? $thumb_url : '',
        ) );

        return $field_data;
    }

    /**
     * The main Underscore template of this field
     **/
    public function template() {
        ?>
        <div class="carbon-attachment">
            <input id="{{{ id }}}" class="regular-text carbon-file-field" type="hidden" name="{{{ name }}}[shortcode]" value="{{ shortcode }}" />
            <input type="hidden" name="{{{ name }}}[player_id]" value="{{{ player_id }}}" />
            <input type="hidden" name="{{{ name }}}[account_id]" value="{{{ account_id }}}" />
            <input type="hidden" name="{{{ name }}}[video_id]" value="{{{ video_id }}}" />

            <div class="carbon-description {{{ value ? '' : 'hidden' }}}">
                <div class="carbon-attachment-preview {{{ thumb_url ? '' : 'hidden' }}}">
                    <img src="{{ thumb_url }}" />
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
     * Load data from the datastore.
     * Manually set the brightcove field data fragments.
     **/
    public function load() {
        $this->store->load( $this );

        $name = $this->get_name();

        // Set the "shortcode"
        $this->set_name( $name . '-shortcode' );
        $this->store->load( $this );
        if ( $this->get_value() ) {
            $this->shortcode = $this->get_value();
        }

        // Set the "player_id"
        $this->set_name( $name . '-player_id' );
        $this->store->load( $this );
        if ( $this->get_value() ) {
            $this->player_id = $this->get_value();
        }

        // Set the "account_id"
        $this->set_name( $name . '-account_id' );
        $this->store->load( $this );
        if ( $this->get_value() ) {
            $this->account_id = (int) $this->get_value();
        }

        // Set the "video_id"
        $this->set_name( $name . '-video_id' );
        $this->store->load( $this );
        if ( $this->get_value() ) {
            $this->video_id = (int) $this->get_value();
        }

        // Set the field value
        $this->set_name( $name );
        if ($this->shortcode && $this->player_id && $this->account_id && $this->video_id) {
            $value = $this->shortcode . ',' . $this->player_id . ',' . $this->account_id . ',' . $this->video_id;
        } else {
            $value = '';
        }
        $this->set_value( $value );
    }

    /**
     * Save data to the datastore.
     * Manually save the brightcove field data fragments.
     **/
    public function save() {
        $name = $this->get_name();
        $value = $this->get_value();

        // Add the "shortcode" meta in the database
        $this->set_name( $name . '-shortcode' );
        $this->set_value( $value['shortcode'] );
        $this->store->save( $this );

        // Add the "player_id" meta in the database
        $this->set_name( $name . '-player_id' );
        $this->set_value( $value['player_id'] );
        $this->store->save( $this );

        // Add the "account_id" meta in the database
        $this->set_name( $name . '-account_id' );
        $this->set_value( $value['account_id'] );
        $this->store->save( $this );

        // Add the "video_id" meta in the database
        $this->set_name( $name . '-video_id' );
        $this->set_value( $value['video_id'] );
        $this->store->save( $this );

        // Set the value for the field
        $this->set_name( $name );
        if( ! empty( $value['shortcode'] ) &&
            ! empty( $value['player_id'] ) &&
            ! empty( $value['account_id'] ) &&
            ! empty( $value['video_id'] ) ) {
            $field_value  = $value['shortcode'] . ',' .
                            $value['player_id'] . ',' .
                            $value['account_id'] . ',' .
                            $value['video_id'];
        } else {
            $field_value = '';
        }
        $this->set_value( $field_value );

        parent::save();
    }

    /**
     * Load the field value from an input array based on it's name
     *
     * @param array $input (optional) Array of field names and values. Defaults to $_POST
     **/
    public function set_value_from_input( $input = null ) {
        if ( is_null( $input ) ) {
            $input = $_POST;
        }

        if ( ! isset( $input[ $this->name ] ) ) {
            $this->set_value( null );
        } else {
            $value = stripslashes_deep( $input[ $this->name ] );

            if ( isset( $input[ $this->name . '_-shortcode' ] ) ) {
                $this->shortcode = $input[ $this->name . '_-shortcode' ];
            }

            if ( isset( $input[ $this->name . '_-player_id' ] ) ) {
                $this->player_id = $input[ $this->name . '_-player_id' ];
            }

            if ( isset( $input[ $this->name . '_-account_id' ] ) ) {
                $this->account_id = (int) $input[ $this->name . '_-account_id' ];
            }

            if ( isset( $input[ $this->name . '_-video_id' ] ) ) {
                $this->video_id = (int) $input[ $this->name . '_-video_id' ];
            }

            $this->set_value( $value );
        }
    }

    /**
     * admin_enqueue_scripts()
     *
     * This method is called in the admin_enqueue_scripts action. It is called once per field type.
     * Use this method to enqueue CSS + JavaScript files.
     *
     */
    public static function admin_enqueue_scripts() {
        $dir = plugin_dir_url( __FILE__ );

        # Enqueue JS
        wp_enqueue_script( 'carbon-field-Brightcove', $dir . 'js/field.js', array( 'carbon-fields' ) );

        # Enqueue CSS
        wp_enqueue_style( 'carbon-field-Brightcove', $dir . 'css/field.css' );
    }
}

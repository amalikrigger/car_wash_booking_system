<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CWB_Public {
    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_assets() {
        wp_enqueue_style( 'cwb-styles', plugin_dir_url( dirname( __FILE__ ) ) . 'public/assets/css/styles.css' );
        wp_enqueue_style( 'cwb-colors', plugin_dir_url( dirname( __FILE__ ) ) . 'public/assets/css/colors.css' );
        wp_enqueue_script(
            'font-awesome-kit',
            'https://kit.fontawesome.com/c95283ecc5.js',
            [],
            null,
            true
        );
        wp_enqueue_script( 'cwb-booking-js', plugin_dir_url( dirname( __FILE__ ) ) . 'public/assets/js/cwb-booking.js', array( 'jquery' ), $this->version, true );

        $location_data = cwb_get_locations_with_configs();

        wp_localize_script('cwb-booking-js', 'cwb_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('cwb_nonce'),
            'location_fields_configs' => $location_data['location_fields_configs'],
        ));
    }
}
<?php
class CWB_Activator {
    public static function activate() {
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/database/class-database-setup.php';
        CWB_Database_Setup::initialize_database();
    }

}
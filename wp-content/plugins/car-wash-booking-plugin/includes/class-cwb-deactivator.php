<?php

/**
 * Fired during plugin deactivation.
 *
 * @link       https://your-plugin-website.com (Replace with your plugin website)
 * @since      1.0.0
 *
 * @package    Car_Wash_Booking_System
 * @subpackage Car_Wash_Booking_System/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Car_Wash_Booking_System
 * @subpackage Car_Wash_Booking_System/includes
 * @author     GCOAT (Replace with your name/author info)
 */
class CWB_Deactivator {

    /**
     * Plugin deactivation logic.
     *
     * This method is executed when the plugin is deactivated.
     * You can add actions here that should be performed on deactivation.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Perform any deactivation tasks here if needed.
        // For example, you might want to:
        // - Deactivate any scheduled cron jobs.
        // - Flush rewrite rules if you added custom post types or taxonomies.
        // - Clear caches (though often better to do this on activation/update).

        // For now, we can leave it empty if there are no specific deactivation tasks.
    }

}
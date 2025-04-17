<div class="cwb-container">
    <div class="cwb-wrapper">
        <div class="cwb-main cwb-clear-fix">
            <form class="cwb-form">
                <ul class="cwb-main-list cwb-clear-fix cwb-list-reset">
                    <?php
                    include plugin_dir_path(__FILE__) . 'booking-widget-location-list.php';
                    include plugin_dir_path(__FILE__) . 'booking-widget-vehicle-list.php';
                    include plugin_dir_path(__FILE__) . 'booking-widget-package-list.php';
                    include plugin_dir_path(__FILE__) . 'booking-widget-service-list.php';
                    include plugin_dir_path(__FILE__) . 'booking-widget-calendar.php';
                    include plugin_dir_path(__FILE__) . 'booking-widget-summary.php';
                    ?>
                </ul>
                <div id="cwb-preloader" class="" data-counter="0"></div>
            </form>
        </div>
    </div>
</div>

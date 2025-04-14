<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Shortcode to display the car wash booking form.
 */
function hwb_render_booking_form() {
    // Enqueue the stylesheet for the UI
    wp_enqueue_style('hwb-styles-css', plugin_dir_url(__FILE__) . '../assets/styles.css');
    wp_enqueue_style('hwb-colors-css', plugin_dir_url(__FILE__) . '../assets/colors.css');
    wp_enqueue_script(
        'font-awesome-kit', // Handle name
        'https://kit.fontawesome.com/c95283ecc5.js', // URL of the Font Awesome script
        [], // Dependencies (none)
        null, // Version (null means no versioning)
        true // Load in the footer
    );
    wp_enqueue_script('hwb-main-script', plugin_dir_url(__FILE__) . 'assets/main.js', ['jquery'], null, true);
    wp_enqueue_script('hwb-booking-js', plugin_dir_url(__FILE__) . '../assets/hwb-booking.js', array('jquery'), null, true);

    // Fetch location fields configurations for all locations
    $location_fields_configs = [];
    $locations = hwb_get_locations();
    foreach ($locations as $location) {
        $location_fields_configs[$location['id']] = hwb_get_location_fields_config($location['id']);
    }

    // Pass AJAX URL, nonce, and location fields config to the script.
    wp_localize_script('hwb-booking-js', 'hwb_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('hwb_nonce'),
        'location_fields_configs' => $location_fields_configs, // Pass configs to JS
    ));

    ob_start();
    ?>
    <div class="hwb-container">
        <div class="hwb-wrapper">
            <div class="hwb-main hwb-clear-fix">
                <form class="hwb-form">
                    <ul class="hwb-main-list hwb-clear-fix hwb-list-reset">
                        <!-- Location -->
                        <li class="hwb-main-list-item hwb-main-list-item-location-list hwb-clear-fix">
                            <div class="hwb-main-list-item-section-header hwb-clear-fix">
                                    <span class="hwb-main-list-item-section-header-step">
                                    <span>1</span>
                                    <span>/6</span>
                                    </span>
                                <h4 class="hwb-main-list-item-section-header-header">
                                    <span>Select location</span></h4>
                                <h5 class="hwb-main-list-item-section-header-subheader">
                                    <span>Select location below.</span>
                                </h5>
                            </div>
                            <div class="hwb-main-list-item-section-content hwb-clear-fix">
                                <ul class="hwb-location-list hwb-list-reset hwb-clear-fix">
                                    <?php
                                    $locations = hwb_get_locations();
                                    $first = true; // Flag to track the first location
                                    foreach ($locations as $location) {
                                        $selected = $first ? 'hwb-state-selected' : '';
                                        $default = $first ? 'data-default="true"' : ''; // Add data-default attribute for the first location
                                        echo "<li class='hwb-location {$selected}' data-id='" . esc_attr($location['id']) . "' {$default}>
                                                <div>
                                                    <div>" . esc_html($location['name']) . "</div>
                                                </div>
                                              </li>";
                                        $first = false;
                                    }
                                    ?>
                                </ul>
                            </div>
                        </li>
                        <!-- Vehicle -->
                        <li class="hwb-main-list-item hwb-main-list-item-vehicle-list hwb-clear-fix">
                            <div class="hwb-main-list-item-section-header hwb-clear-fix">
                                    <span class="hwb-main-list-item-section-header-step">
    				                <span>2</span>
                                    <span>/6</span>
                                    </span>
                                <h4 class="hwb-main-list-item-section-header-header">
                                    <span>Vehicle type</span></h4>
                                <h5 class="hwb-main-list-item-section-header-subheader">
                                    <span>Select vehicle type below.</span>
                                </h5>
                            </div>
                            <div class="hwb-main-list-item-section-content hwb-clear-fix">
                                <ul id="hwb-vehicle-list" class="hwb-vehicle-list hwb-list-reset hwb-clear-fix">
                                    <!-- Vehicles will be dynamically populated here -->
                                </ul>
                            </div>
                        </li>
                        <!-- Package -->
                        <li class="hwb-main-list-item hwb-main-list-item-package-list hwb-clear-fix">
                            <div class="hwb-main-list-item-section-header hwb-clear-fix">
                                <span class="hwb-main-list-item-section-header-step">
                                    <span>3</span>
                                    <span>/6</span>
                                </span>
                                <h4 class="hwb-main-list-item-section-header-header">
                                    <span>Wash packages</span>
                                </h4>
                                <h5 class="hwb-main-list-item-section-header-subheader">
                                    <span>Which wash is best for your vehicle?</span>
                                </h5>
                            </div>
                            <div class="hwb-main-list-item-section-content hwb-clear-fix">
                                <!-- Placeholder for dynamic packages -->
                                <ul id="hwb-package-list" class="hwb-package-list hwb-list-reset hwb-clear-fix">
                                    <!-- Packages will be dynamically loaded here -->
                                </ul>
                            </div>
                        </li>
                        <!-- Service -->
                        <li class="hwb-main-list-item hwb-main-list-item-service-list hwb-clear-fix hwb-state-disable">
                            <div class="hwb-main-list-item-section-header hwb-clear-fix">
                                    <span class="hwb-main-list-item-section-header-step">
                                <span>4</span>
                                    <span>/6</span>
                                    </span>
                                <h4 class="hwb-main-list-item-section-header-header">
                                    <span>Add-on options</span></h4>
                                <h5 class="hwb-main-list-item-section-header-subheader">
                                    <span>Add services to your package.</span>
                                </h5>
                            </div>
                            <div class="hwb-main-list-item-section-content hwb-clear-fix">
                                <ul id="hwb-addon-list" class="hwb-service-list hwb-list-reset hwb-clear-fix">
                                    <!-- Add-on services will be dynamically loaded here -->
                                </ul>
                            </div>
                        </li>
                        <!-- Date and Time -->
                        <li class="hwb-main-list-item hwb-main-list-item-calendar hwb-clear-fix">
                            <div class="hwb-main-list-item-section-header hwb-clear-fix">
                                    <span class="hwb-main-list-item-section-header-step">
    				                <span>5</span>
                                    <span>/6</span>
                                    </span>
                                <h4 class="hwb-main-list-item-section-header-header">
                                    <span>Select date and time</span></h4>
                                <h5 class="hwb-main-list-item-section-header-subheader">
                                    <span>Click on any time to make a booking.</span>
                                </h5>
                            </div>
                            <div class="hwb-main-list-item-section-content hwb-clear-fix">
                                <div class="hwb-main-list-item-section-content hwb-clear-fix">
                                     <div class="hwb-calendar-header">
                                         <a href="#" class="hwb-calendar-header-arrow-left hwb-meta-icon hwb-meta-icon-arrow-horizontal">
                                             <i class="fa-solid fa-arrow-left"></i>
                                         </a>
                                         <span class="hwb-calendar-header-caption">
                                             <!-- Header will update dynamically -->
                                         </span>
                                         <a href="#" class="hwb-calendar-header-arrow-right hwb-meta-icon hwb-meta-icon-arrow-horizontal">
                                             <i class="fa-solid fa-arrow-right"></i>
                                         </a>
                                     </div>
                                    <div class="hwb-calendar-table-wrapper">
                                        <table class="hwb-calendar">
                                            <tbody>
                                                <tr class="hwb-calendar-subheader">
                                                    <!-- Days and dates will populate here -->
                                                </tr>
                                                <tr class="hwb-calendar-data">
                                                    <!-- Availability data will populate here -->
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <!-- Booking summary -->
                        <li class="hwb-main-list-item hwb-clear-fix hwb-main-list-item-booking">
                            <div class="hwb-main-list-item-section-header hwb-clear-fix">
                                    <span class="hwb-main-list-item-section-header-step">
    				                <span>6</span>
                                    <span>/6</span>
                                    </span>
                                <h4 class="hwb-main-list-item-section-header-header">
                                    <span>Booking summary</span></h4>
                                <h5 class="hwb-main-list-item-section-header-subheader">
                                    <span>Please provide us with your contact information.</span>
                                </h5>
                            </div>
                            <div class="hwb-main-list-item-section-content hwb-clear-fix">
                                <div class="hwb-main-list-item-section-content hwb-clear-fix">
                                    <ul class="hwb-booking-summary hwb-list-reset hwb-clear-fix">
                                        <li class="hwb-booking-summary-date">
                                            <div class="hwb-meta-icon hwb-meta-icon-date">
                                                <i class="fa-regular fa-calendar-days"></i>
                                            </div>
                                            <h5>
                                                <span></span>
                                                <span>?</span>
                                            </h5>
                                            <span>Your Appointment Date</span>
                                        </li>
                                        <li class="hwb-booking-summary-time">
                                            <div class="hwb-meta-icon hwb-meta-icon-time">
                                                <i class="fa-regular fa-clock"></i>
                                            </div>
                                            <h5>
                                                <span></span>
                                                <span>?</span>
                                            </h5>
                                            <span>Your Appointment Time</span>
                                        </li>
                                        <li class="hwb-booking-summary-duration">
                                            <div class="hwb-meta-icon hwb-meta-icon-total-duration">
                                                <i class="fa-regular fa-bell"></i>
                                            </div>
                                            <h5>
                                                <span>0</span>
                                                <span>h</span>
                                                 
                                                <span>0</span>
                                                <span>min</span>
                                            </h5>
                                            <span>Duration</span>
                                        </li>
                                        <li class="hwb-booking-summary-price">
                                            <div class="hwb-meta-icon hwb-meta-icon-total-price">
                                                <i class="fa-solid fa-cart-shopping"></i>
                                            </div>
                                            <h5>
                                                <span>$</span>
                                                <span>0.00</span>
                                            </h5>
                                            <span>Total Price</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="hwb-contact-details-options hwb-state-hidden">
                                    <a class="hwb-button" href="#">Log in</a> or <a class="hwb-button" href="#">Place order</a>
                                </div>
                                <div class="hwb-notice hwb-notice-contact-details">
                                    <div class="hwb-meta-icon"></div>
                                    <div class="hwb-notice-content">
                                        <div class="hwb-notice-header"></div>
                                        <div class="hwb-notice-text"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="hwb-to-tab ui-tabs">
                                        <ul role="tablist" class="ui-tabs-nav ui-helper-clearfix">
                                            <li role="tab" tabindex="0" class="ui-tabs-tab ui-tab ui-tabs-active ui-state-active" aria-selected="true" aria-expanded="true"><a href="#hwb-current-order" tabindex="-1" class="ui-tabs-anchor">Current
                                                Order</a></li>
                                            <li role="tab" tabindex="-1" class="ui-tabs-tab ui-tab" aria-controls="hwb-user-details" aria-labelledby="ui-id-2" aria-selected="false" aria-expanded="false"><a href="#hwb-user-details" tabindex="-1" class="ui-tabs-anchor">Your
                                                Details</a></li>
                                            <li role="tab" tabindex="-1" class="ui-tabs-tab ui-tab" aria-selected="false" aria-expanded="false"><a href="#hwb-user-log-out" tabindex="-1" class="ui-tabs-anchor">Log Out</a>
                                            </li>
                                        </ul>
                                        <div aria-labelledby="ui-id-1" role="tabpanel" class="ui-tabs-panel" aria-hidden="false">
                                            <div class="hwb-main-list-item-section-content hwb-clear-fix" id="hwb-booking-form-fields"> <!-- Added ID here -->
                                                <div class="hwb-clear-fix">
                                                    <div class="hwb-form-field hwb-form-width-50">
                                                        <label>First name *</label>
                                                        <input type="text" name="client_first_name" autocomplete="off" value="" required>
                                                    </div>
                                                    <div class="hwb-form-field hwb-form-width-50">
                                                        <label>Last Name *</label>
                                                        <input type="text" name="client_second_name" autocomplete="off" value="" required>
                                                    </div>
                                                </div>
                                                <!-- Street Field (Conditional) -->
                                                <div class="hwb-location-field hwb-location-field-street">
                                                    <div class="hwb-form-field hwb-form-width-50 hwb-location-field hwb-location-field-street">
                                                        <label>Street</label>
                                                        <input type="text" name="client_address_street" autocomplete="off" value="">
                                                    </div>
                                                </div>
                                                <!-- Zip Code Field (Conditional) -->
                                                <div class="hwb-location-field hwb-location-field-zip_code">
                                                    <div class="hwb-form-field hwb-form-width-50">
                                                        <label>ZIP Code</label>
                                                        <input type="text" name="client_address_post_code" autocomplete="off" value="">
                                                    </div>
                                                </div>
                                                <!-- City Field (Conditional) -->
                                                <div class="hwb-location-field hwb-location-field-city">
                                                    <div class="hwb-form-field hwb-form-width-33">
                                                        <label>City</label>
                                                        <input type="text" name="client_address_city" autocomplete="off" value="">
                                                    </div>
                                                </div>
                                                <!-- State Field (Conditional) -->
                                                <div class="hwb-location-field hwb-location-field-state">
                                                    <div class="hwb-form-field hwb-form-width-33">
                                                        <label>State</label>
                                                        <input type="text" name="client_address_state" autocomplete="off" value="">
                                                    </div>
                                                </div>
                                                <!-- Country Field (Conditional) -->
                                                <div class="hwb-location-field hwb-location-field-country">
                                                    <div class="hwb-form-field hwb-form-width-33">
                                                        <label>Country</label>
                                                        <input type="text" name="client_address_country" autocomplete="off" value="">
                                                    </div>
                                                </div>
                                                <div class="hwb-clear-fix">
                                                    <div class="hwb-form-field hwb-form-width-50">
                                                        <label>Your E-mail *</label>
                                                        <input type="text" name="client_email_address" autocomplete="off" value="" required>
                                                    </div>
                                                    <div class="hwb-form-field hwb-form-width-50">
                                                        <label>Phone Number *</label>
                                                        <input type="text" name="client_phone_number" autocomplete="off" value="" required>
                                                    </div>
                                                </div>
                                                <div class="hwb-clear-fix">
                                                    <div class="hwb-form-field hwb-form-width-100">
                                                        <label>Vehicle Make and Model *</label>
                                                        <input type="text" name="client_vehicle" autocomplete="off" value="" required>
                                                    </div>
                                                </div>
                                                <!-- Message Field (Conditional) -->
                                                <div class="hwb-clear-fix hwb-location-field hwb-location-field-message">
                                                    <div class="hwb-form-field hwb-form-width-100">
                                                        <label>Message</label>
                                                        <textarea rows="1" cols="1" name="client_message"></textarea>
                                                    </div>
                                                </div>
                                                <!-- Gratuity Field (Conditional) -->
                                                <div class="hwb-clear-fix hwb-location-field hwb-location-field-gratuity">
                                                    <div class="hwb-form-field hwb-form-width-100">
                                                        <label>Gratuity</label>
                                                        <input type="text" name="gratuity" autocomplete="off" value="0.00">
                                                    </div>
                                                </div>
                                                <!-- Service Location Field (Conditional) -->
                                                <div class="hwb-clear-fix hwb-location-field hwb-location-field-service_location">
                                                    <div class="hwb-form-field hwb-form-width-100">
                                                        <label>Service Location</label>
                                                        <select name="service_location" autocomplete="off" tabindex="-1" class="select2-hidden-accessible" aria-hidden="true">
                                                            <option value="" selected="">Select a location</option>
                                                            <option value="Christiansted">Christiansted</option>
                                                            <option value="Frederiksted">Frederiksted</option>
                                                        </select>
                                                        <span class="select2 select2-container select2-container--default" dir="ltr" style="width: 100%;">
                                                            <span class="selection">
                                                                <span class="select2-selection select2-selection--single" role="combobox" aria-haspopup="true" aria-expanded="false" tabindex="0" aria-labelledby="select2-service_location-qp-container">
                                                                    <span class="select2-selection__rendered" id="select2-service_location-qp-container" title="Select a location">Select a location</span>
                                                                    <span class="select2-selection__arrow" role="presentation">
                                                                        <b role="presentation"></b>
                                                                    </span>
                                                                </span>
                                                            </span>
                                                            <span class="dropdown-wrapper" aria-hidden="true"></span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="hwb-clear-fix">
                                                    <div class="hwb-form-field hwb-form-width-100">
                                                        <label>Payment type</label>
                                                        <select name="payment_type" autocomplete="off" tabindex="-1" class="select2-hidden-accessible" aria-hidden="true">
                                                            <option value="" selected="">Choose a payment</option>
                                                            <option value="stripe_cc">Credit Cards (Stripe) by Payment Plugins
                                                            </option>
                                                            <option value="stripe_applepay">Apple Pay (Stripe) by Payment Plugins
                                                            </option>
                                                            <option value="eh_paypal_express">PayPal Express</option>
                                                        </select>
                                                        <span class="select2 select2-container select2-container--default" dir="ltr" style="width: 100%;">
                                                    <span class="selection">
                                                        <span
                                                                class="select2-selection select2-selection--single"
                                                                role="combobox"
                                                                aria-haspopup="true" aria-expanded="false" tabindex="0"
                                                                aria-labelledby="select2-payment_type-qp-container">
                                                <span
                                                        class="select2-selection__rendered"
                                                        id="select2-payment_type-qp-container" title="Choose a payment">Choose a payment
                                                </span>
                                                            <span class="select2-selection__arrow" role="presentation">
                                                    <b role="presentation"></b>
                                                </span>
                                                            </span>
                                                            </span>
                                                            <span class="dropdown-wrapper" aria-hidden="true">
                                                </span>
                                                            </span>
                                                    </div>
                                                </div>
                                                <div class="hwb-form-summary hwb-clear-fix">
                                                    <div class="hwb-agreement">
                                                        <div class="hwb-clear-fix">
                                                                <span class="hwb-form-checkbox">
    							                                <span class="hwb-meta-icon hwb-meta-icon-check"></span>
                                                                </span>
                                                            <input type="hidden" value="0" autocomplete="off">
                                                            <div>Read Privacy Policy</div>
                                                        </div>
                                                        <div class="hwb-clear-fix">
                                                                <span class="hwb-form-checkbox">
                                                                <span class="hwb-meta-icon hwb-meta-icon-check"></span>
                                                                </span>
                                                            <input type="hidden" value="0" autocomplete="off">
                                                            <div>Read Terms & Conditions</div>
                                                        </div>
                                                    </div>
                                                    <div class="hwb-form-info">We will confirm your appointment with you by phone or e-mail within 24 hours of receiving your request.
                                                    </div>
                                                    <input type="submit" class="hwb-button" value="Confirm Booking">
                                                </div>
                                            </div>
                                        </div>
                                        <div aria-labelledby="ui-id-2" role="tabpanel" class="ui-tabs-panel" aria-hidden="true" style="display: none;">
                                            <div class="hwb-main-list-item-section-content hwb-clear-fix ">
                                                <div class="hwb-clear-fix">
                                                    <div class="hwb-form-field hwb-form-width-50">
                                                        <label>First name *</label>
                                                        <input type="text" name="update_client_first_name" autocomplete="off" value="" required>
                                                    </div>
                                                    <div class="hwb-form-field hwb-form-width-50">
                                                        <label>Last Name *</label>
                                                        <input type="text" name="update_client_second_name" autocomplete="off" value="" required>
                                                    </div>
                                                </div>
                                                <div class="hwb-clear-fix">
                                                    <div class="hwb-form-field hwb-form-width-50">
                                                        <label>Street</label>
                                                        <input type="text" name="update_client_address_street" autocomplete="off" value="">
                                                    </div>

                                                    <div class="hwb-form-field hwb-form-width-50">
                                                        <label>ZIP Code</label>
                                                        <input type="text" name="update_client_address_post_code" autocomplete="off" value="">
                                                    </div>
                                                </div>
                                                <div class="hwb-clear-fix">
                                                    <div class="hwb-form-field hwb-form-width-33">
                                                        <label>City</label>
                                                        <input type="text" name="update_client_address_city" autocomplete="off" value="">
                                                    </div>
                                                    <div class="hwb-form-field hwb-form-width-33">
                                                        <label>State</label>
                                                        <input type="text" name="update_client_address_state" autocomplete="off" value="">
                                                    </div>

                                                    <div class="hwb-form-field hwb-form-width-33">
                                                        <label>Country</label>
                                                        <input type="text" name="update_client_address_country" autocomplete="off" value="">
                                                    </div>
                                                </div>
                                                <div class="hwb-clear-fix">
                                                    <div class="hwb-form-field hwb-form-width-50">
                                                        <label>Your E-mail *</label>
                                                        <input type="text" name="update_client_email_address" autocomplete="off" value="amalikrigger93@gmail.com" required>
                                                    </div>
                                                    <div class="hwb-form-field hwb-form-width-50">
                                                        <label>Phone Number *</label>
                                                        <input type="text" name="update_client_phone_number" autocomplete="off" value="" required>
                                                    </div>
                                                </div>
                                                <div class="hwb-clear-fix">
                                                    <div class="hwb-form-field hwb-form-width-100">
                                                        <label>Vehicle Make and Model *</label>
                                                        <input type="text" name="update_client_vehicle" autocomplete="off" value="" required>
                                                    </div>
                                                </div>
                                                <div class="hwb-form-summary hwb-clear-fix">
                                                    <a class="hwb-button" href="#">Save</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div aria-labelledby="ui-id-3" role="tabpanel" class="ui-tabs-panel  " aria-hidden="true" style="display: none;"></div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div id="hwb-preloader" class="" data-counter="0"></div>
                </form>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('hwb_booking_form', 'hwb_render_booking_form');
?>

<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Shortcode to display the car wash booking form.
 */
function cwb_render_booking_form() {
    // Enqueue the stylesheet for the UI
    wp_enqueue_style('cwb-styles-css', plugin_dir_url(__FILE__) . '../../public/assets/css/styles.css');
    wp_enqueue_style('cwb-colors-css', plugin_dir_url(__FILE__) . '../../public/assets/css/colors.css');
    wp_enqueue_script(
        'font-awesome-kit', // Handle name
        'https://kit.fontawesome.com/c95283ecc5.js', // URL of the Font Awesome script
        [], // Dependencies (none)
        null, // Version (null means no versioning)
        true // Load in the footer
    );
    wp_enqueue_script('cwb-booking-js', plugin_dir_url(__FILE__) . '../../public/assets/js/cwb-booking.js', array('jquery'), null, true);

    // Fetch location fields configurations for all locations
    $location_fields_configs = [];
    $locations = cwb_get_locations();
    foreach ($locations as $location) {
        $location_fields_configs[$location['id']] = cwb_get_location_fields_config($location['id']);
    }

    // Pass AJAX URL, nonce, and location fields config to the script.
    wp_localize_script('cwb-booking-js', 'cwb_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('cwb_nonce'),
        'location_fields_configs' => $location_fields_configs, // Pass configs to JS
    ));

    ob_start();
    ?>
    <div class="cwb-container">
        <div class="cwb-wrapper">
            <div class="cwb-main cwb-clear-fix">
                <form class="cwb-form">
                    <ul class="cwb-main-list cwb-clear-fix cwb-list-reset">
                        <!-- Location -->
                        <li class="cwb-main-list-item cwb-main-list-item-location-list cwb-clear-fix">
                            <div class="cwb-main-list-item-section-header cwb-clear-fix">
                                    <span class="cwb-main-list-item-section-header-step">
                                    <span>1</span>
                                    <span>/6</span>
                                    </span>
                                <h4 class="cwb-main-list-item-section-header-header">
                                    <span>Select location</span></h4>
                                <h5 class="cwb-main-list-item-section-header-subheader">
                                    <span>Select location below.</span>
                                </h5>
                            </div>
                            <div class="cwb-main-list-item-section-content cwb-clear-fix">
                                <ul class="cwb-location-list cwb-list-reset cwb-clear-fix">
                                    <?php
                                    $locations = cwb_get_locations();
                                    $first = true; // Flag to track the first location
                                    foreach ($locations as $location) {
                                        $selected = $first ? 'cwb-state-selected' : '';
                                        $default = $first ? 'data-default="true"' : ''; // Add data-default attribute for the first location
                                        echo "<li class='cwb-location {$selected}' data-id='" . esc_attr($location['id']) . "' {$default}>
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
                        <li class="cwb-main-list-item cwb-main-list-item-vehicle-list cwb-clear-fix">
                            <div class="cwb-main-list-item-section-header cwb-clear-fix">
                                    <span class="cwb-main-list-item-section-header-step">
    				                <span>2</span>
                                    <span>/6</span>
                                    </span>
                                <h4 class="cwb-main-list-item-section-header-header">
                                    <span>Vehicle type</span></h4>
                                <h5 class="cwb-main-list-item-section-header-subheader">
                                    <span>Select vehicle type below.</span>
                                </h5>
                            </div>
                            <div class="cwb-main-list-item-section-content cwb-clear-fix">
                                <ul id="cwb-vehicle-list" class="cwb-vehicle-list cwb-list-reset cwb-clear-fix">
                                    <!-- Vehicles will be dynamically populated here -->
                                </ul>
                            </div>
                        </li>
                        <!-- Package -->
                        <li class="cwb-main-list-item cwb-main-list-item-package-list cwb-clear-fix">
                            <div class="cwb-main-list-item-section-header cwb-clear-fix">
                                <span class="cwb-main-list-item-section-header-step">
                                    <span>3</span>
                                    <span>/6</span>
                                </span>
                                <h4 class="cwb-main-list-item-section-header-header">
                                    <span>Wash packages</span>
                                </h4>
                                <h5 class="cwb-main-list-item-section-header-subheader">
                                    <span>Which wash is best for your vehicle?</span>
                                </h5>
                            </div>
                            <div class="cwb-main-list-item-section-content cwb-clear-fix">
                                <!-- Placeholder for dynamic packages -->
                                <ul id="cwb-package-list" class="cwb-package-list cwb-list-reset cwb-clear-fix">
                                    <!-- Packages will be dynamically loaded here -->
                                </ul>
                            </div>
                        </li>
                        <!-- Service -->
                        <li class="cwb-main-list-item cwb-main-list-item-service-list cwb-clear-fix cwb-state-disable">
                            <div class="cwb-main-list-item-section-header cwb-clear-fix">
                                    <span class="cwb-main-list-item-section-header-step">
                                <span>4</span>
                                    <span>/6</span>
                                    </span>
                                <h4 class="cwb-main-list-item-section-header-header">
                                    <span>Add-on options</span></h4>
                                <h5 class="cwb-main-list-item-section-header-subheader">
                                    <span>Add services to your package.</span>
                                </h5>
                            </div>
                            <div class="cwb-main-list-item-section-content cwb-clear-fix">
                                <ul id="cwb-addon-list" class="cwb-service-list cwb-list-reset cwb-clear-fix">
                                    <!-- Add-on services will be dynamically loaded here -->
                                </ul>
                            </div>
                        </li>
                        <!-- Date and Time -->
                        <li class="cwb-main-list-item cwb-main-list-item-calendar cwb-clear-fix">
                            <div class="cwb-main-list-item-section-header cwb-clear-fix">
                                    <span class="cwb-main-list-item-section-header-step">
    				                <span>5</span>
                                    <span>/6</span>
                                    </span>
                                <h4 class="cwb-main-list-item-section-header-header">
                                    <span>Select date and time</span></h4>
                                <h5 class="cwb-main-list-item-section-header-subheader">
                                    <span>Click on any time to make a booking.</span>
                                </h5>
                            </div>
                            <div class="cwb-main-list-item-section-content cwb-clear-fix">
                                <div class="cwb-main-list-item-section-content cwb-clear-fix">
                                     <div class="cwb-calendar-header">
                                         <a href="#" class="cwb-calendar-header-arrow-left cwb-meta-icon cwb-meta-icon-arrow-horizontal">
                                             <i class="fa-solid fa-arrow-left"></i>
                                         </a>
                                         <span class="cwb-calendar-header-caption">
                                             <!-- Header will update dynamically -->
                                         </span>
                                         <a href="#" class="cwb-calendar-header-arrow-right cwb-meta-icon cwb-meta-icon-arrow-horizontal">
                                             <i class="fa-solid fa-arrow-right"></i>
                                         </a>
                                     </div>
                                    <div class="cwb-calendar-table-wrapper">
                                        <table class="cwb-calendar">
                                            <tbody>
                                                <tr class="cwb-calendar-subheader">
                                                    <!-- Days and dates will populate here -->
                                                </tr>
                                                <tr class="cwb-calendar-data">
                                                    <!-- Availability data will populate here -->
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <!-- Booking summary -->
                        <li class="cwb-main-list-item cwb-clear-fix cwb-main-list-item-booking">
                            <div class="cwb-main-list-item-section-header cwb-clear-fix">
                                    <span class="cwb-main-list-item-section-header-step">
    				                <span>6</span>
                                    <span>/6</span>
                                    </span>
                                <h4 class="cwb-main-list-item-section-header-header">
                                    <span>Booking summary</span></h4>
                                <h5 class="cwb-main-list-item-section-header-subheader">
                                    <span>Please provide us with your contact information.</span>
                                </h5>
                            </div>
                            <div class="cwb-main-list-item-section-content cwb-clear-fix">
                                <div class="cwb-main-list-item-section-content cwb-clear-fix">
                                    <ul class="cwb-booking-summary cwb-list-reset cwb-clear-fix">
                                        <li class="cwb-booking-summary-date">
                                            <div class="cwb-meta-icon cwb-meta-icon-date">
                                                <i class="fa-regular fa-calendar-days"></i>
                                            </div>
                                            <h5>
                                                <span></span>
                                                <span>?</span>
                                            </h5>
                                            <span>Your Appointment Date</span>
                                        </li>
                                        <li class="cwb-booking-summary-time">
                                            <div class="cwb-meta-icon cwb-meta-icon-time">
                                                <i class="fa-regular fa-clock"></i>
                                            </div>
                                            <h5>
                                                <span></span>
                                                <span>?</span>
                                            </h5>
                                            <span>Your Appointment Time</span>
                                        </li>
                                        <li class="cwb-booking-summary-duration">
                                            <div class="cwb-meta-icon cwb-meta-icon-total-duration">
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
                                        <li class="cwb-booking-summary-price">
                                            <div class="cwb-meta-icon cwb-meta-icon-total-price">
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
                                <div class="cwb-contact-details-options cwb-state-hidden">
                                    <a class="cwb-button" href="#">Log in</a> or <a class="cwb-button" href="#">Place order</a>
                                </div>
                                <div class="cwb-notice cwb-notice-contact-details">
                                    <div class="cwb-meta-icon"></div>
                                    <div class="cwb-notice-content">
                                        <div class="cwb-notice-header"></div>
                                        <div class="cwb-notice-text"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="cwb-to-tab ui-tabs">
                                        <ul role="tablist" class="ui-tabs-nav ui-helper-clearfix">
                                            <li role="tab" tabindex="0" class="ui-tabs-tab ui-tab ui-tabs-active ui-state-active" aria-selected="true" aria-expanded="true"><a href="#cwb-current-order" tabindex="-1" class="ui-tabs-anchor">Current
                                                Order</a></li>
                                            <li role="tab" tabindex="-1" class="ui-tabs-tab ui-tab" aria-controls="cwb-user-details" aria-labelledby="ui-id-2" aria-selected="false" aria-expanded="false"><a href="#cwb-user-details" tabindex="-1" class="ui-tabs-anchor">Your
                                                Details</a></li>
                                            <li role="tab" tabindex="-1" class="ui-tabs-tab ui-tab" aria-selected="false" aria-expanded="false"><a href="#cwb-user-log-out" tabindex="-1" class="ui-tabs-anchor">Log Out</a>
                                            </li>
                                        </ul>
                                        <div aria-labelledby="ui-id-1" role="tabpanel" class="ui-tabs-panel" aria-hidden="false">
                                            <div class="cwb-main-list-item-section-content cwb-clear-fix" id="cwb-booking-form-fields"> <!-- Added ID here -->
                                                <div class="cwb-clear-fix">
                                                    <div class="cwb-form-field cwb-form-width-50">
                                                        <label>First name *</label>
                                                        <input type="text" name="client_first_name" autocomplete="off" value="" required pattern="[A-Za-z\s'-]+" title="First name should only contain letters, spaces, apostrophes, and hyphens.">
                                                    </div>
                                                    <div class="cwb-form-field cwb-form-width-50">
                                                        <label>Last Name *</label>
                                                        <input type="text" name="client_second_name" autocomplete="off" value="" required pattern="[A-Za-z\s'-]+" title="Last name should only contain letters, spaces, apostrophes, and hyphens.">
                                                    </div>
                                                </div>
                                                <!-- Latitude and Longitude Fields -->
                                                <div class="cwb-clear-fix">
                                                    <div class="cwb-form-field cwb-form-width-50">
                                                        <label>Latitude *</label>
                                                        <input type="text" name="client_latitude" autocomplete="off" value="" required pattern="-?\d+(\.\d+)?" title="Please enter a valid latitude (e.g., 37.7749).">
                                                    </div>
                                                    <div class="cwb-form-field cwb-form-width-50">
                                                        <label>Longitude *</label>
                                                        <input type="text" name="client_longitude" autocomplete="off" value="" required pattern="-?\d+(\.\d+)?" title="Please enter a valid longitude (e.g., -122.4194).">
                                                    </div>
                                                </div>
                                                <!-- Street Field (Conditional) -->
                                                <div class="cwb-location-field cwb-location-field-street">
                                                    <div class="cwb-form-field cwb-form-width-50 cwb-location-field cwb-location-field-street">
                                                        <label>Street</label>
                                                        <input type="text" name="client_address_street" autocomplete="off" value="">
                                                    </div>
                                                </div>
                                                <!-- Zip Code Field (Conditional) -->
                                                <div class="cwb-location-field cwb-location-field-zip_code">
                                                    <div class="cwb-form-field cwb-form-width-50">
                                                        <label>ZIP Code</label>
                                                        <input type="text" name="client_address_post_code" autocomplete="off" value="" pattern="[0-9]{5}(-[0-9]{4})?" title="Please enter a valid ZIP code (e.g., 12345 or 12345-6789).">
                                                    </div>
                                                </div>
                                                <!-- City Field (Conditional) -->
                                                <div class="cwb-location-field cwb-location-field-city">
                                                    <div class="cwb-form-field cwb-form-width-33">
                                                        <label>City</label>
                                                        <input type="text" name="client_address_city" autocomplete="off" value="" pattern="[A-Za-z\s]+" title="City should only contain letters and spaces.">
                                                    </div>
                                                </div>
                                                <!-- State Field (Conditional) -->
                                                <div class="cwb-location-field cwb-location-field-state">
                                                    <div class="cwb-form-field cwb-form-width-33">
                                                        <label>State</label>
                                                        <input type="text" name="client_address_state" autocomplete="off" value="" pattern="[A-Za-z\s]+" title="State should only contain letters and spaces.">
                                                    </div>
                                                </div>
                                                <!-- Country Field (Conditional) -->
                                                <div class="cwb-location-field cwb-location-field-country">
                                                    <div class="cwb-form-field cwb-form-width-33">
                                                        <label>Country</label>
                                                        <input type="text" name="client_address_country" autocomplete="off" value="" pattern="[A-Za-z\s]+" title="Country should only contain letters and spaces.">
                                                    </div>
                                                </div>
                                                <div class="cwb-clear-fix">
                                                    <div class="cwb-form-field cwb-form-width-50">
                                                        <label>E-mail *</label>
                                                        <input type="email" name="client_email_address" autocomplete="off" value="" required title="Please enter a valid email address.">
                                                    </div>
                                                    <div class="cwb-form-field cwb-form-width-50">
                                                        <label>Phone Number *</label>
                                                        <input type="tel" name="client_phone_number" autocomplete="off" value="" required pattern="\+?[0-9\s\-]+" title="Phone number should only contain numbers, spaces, dashes, and an optional leading '+'.">
                                                    </div>
                                                </div>
                                                <div class="cwb-clear-fix">
                                                    <div class="cwb-form-field cwb-form-width-100">
                                                        <label>Vehicle Make and Model *</label>
                                                        <input type="text" name="client_vehicle" autocomplete="off" value="" required pattern="[A-Za-z0-9\s\-]+" title="Vehicle make and model should only contain letters, numbers, spaces, and dashes.">
                                                    </div>
                                                </div>
                                                <!-- Message Field (Conditional) -->
                                                <div class="cwb-clear-fix cwb-location-field cwb-location-field-message">
                                                    <div class="cwb-form-field cwb-form-width-100">
                                                        <label>Message</label>
                                                        <textarea rows="1" cols="1" name="client_message"></textarea>
                                                    </div>
                                                </div>
                                                <!-- Gratuity Field (Conditional) -->
                                                <div class="cwb-clear-fix cwb-location-field cwb-location-field-gratuity">
                                                    <div class="cwb-form-field cwb-form-width-100">
                                                        <label>Gratuity</label>
                                                        <input type="text" name="gratuity" autocomplete="off" value="0.00">
                                                    </div>
                                                </div>
                                                <!-- Service Location Field (Conditional) -->
                                                <!--
                                                <div class="cwb-clear-fix cwb-location-field cwb-location-field-service_location">
                                                    <div class="cwb-form-field cwb-form-width-100">
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
                                                -->
                                                <div class="cwb-clear-fix">
                                                    <div class="cwb-form-field cwb-form-width-100">
                                                        <label>Payment type</label>
                                                        <select name="payment_type" autocomplete="off">
                                                            <option value="" selected="">Choose a payment</option>
                                                            <option value="stripe_cc">Credit Cards (Stripe) by Payment Plugins</option>
                                                            <option value="stripe_applepay">Apple Pay (Stripe) by Payment Plugins</option>
                                                            <option value="eh_paypal_express">PayPal Express</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="cwb-form-summary cwb-clear-fix">
                                                     <div class="cwb-agreement">
                                                        <div class="cwb-clear-fix">
                                                            <span class="cwb-form-checkbox">
                                                                <span class="cwb-meta-icon cwb-meta-icon-check"></span>
                                                            </span>
                                                            <input type="checkbox" name="privacy_policy_accepted" value="0" required>
                                                            <div>
                                                                I have read and agree to the
                                                                <a href="/privacy-policy" target="_blank">Privacy Policy</a>.
                                                            </div>
                                                        </div>
                                                        <div class="cwb-clear-fix">
                                                            <span class="cwb-form-checkbox">
                                                                <span class="cwb-meta-icon cwb-meta-icon-check"></span>
                                                            </span>
                                                            <input type="checkbox" name="terms_accepted" value="0" required>
                                                            <div>
                                                                I have read and agree to the
                                                                <a href="/terms-and-conditions" target="_blank">Terms & Conditions</a>.
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="cwb-form-info">We will confirm your appointment with you by phone or e-mail within 24 hours of receiving your request.
                                                    </div>
                                                    <input type="submit" class="cwb-button" value="Confirm Booking">
                                                </div>
                                            </div>
                                        </div>
                                        <div aria-labelledby="ui-id-2" role="tabpanel" class="ui-tabs-panel" aria-hidden="true" style="display: none;">
                                            <div class="cwb-main-list-item-section-content cwb-clear-fix ">
                                                <div class="cwb-clear-fix">
                                                    <div class="cwb-form-field cwb-form-width-50">
                                                        <label>First name *</label>
                                                        <input type="text" name="update_client_first_name" autocomplete="off" value="" required pattern="[A-Za-z\s'-]+" title="First name should only contain letters, spaces, apostrophes, and hyphens.">
                                                    </div>
                                                    <div class="cwb-form-field cwb-form-width-50">
                                                        <label>Last Name *</label>
                                                        <input type="text" name="update_client_second_name" autocomplete="off" value="" required pattern="[A-Za-z\s'-]+" title="Last name should only contain letters, spaces, apostrophes, and hyphens.">
                                                    </div>
                                                </div>
                                                <div class="cwb-clear-fix">
                                                    <div class="cwb-form-field cwb-form-width-50">
                                                        <label>Street</label>
                                                        <input type="text" name="update_client_address_street" autocomplete="off" value="">
                                                    </div>

                                                    <div class="cwb-form-field cwb-form-width-50">
                                                        <label>ZIP Code</label>
                                                        <input type="text" name="update_client_address_post_code" autocomplete="off" value="" pattern="[0-9]{5}(-[0-9]{4})?" title="Please enter a valid ZIP code (e.g., 12345 or 12345-6789).">
                                                    </div>
                                                </div>
                                                <div class="cwb-clear-fix">
                                                    <div class="cwb-form-field cwb-form-width-33">
                                                        <label>City</label>
                                                        <input type="text" name="update_client_address_city" autocomplete="off" value="" pattern="[A-Za-z\s]+" title="City should only contain letters and spaces.">
                                                    </div>
                                                    <div class="cwb-form-field cwb-form-width-33">
                                                        <label>State</label>
                                                        <input type="text" name="update_client_address_state" autocomplete="off" value="" pattern="[A-Za-z\s]+" title="State should only contain letters and spaces.">
                                                    </div>

                                                    <div class="cwb-form-field cwb-form-width-33">
                                                        <label>Country</label>
                                                        <input type="text" name="update_client_address_country" autocomplete="off" value="" pattern="[A-Za-z\s]+" title="Country should only contain letters and spaces.">
                                                    </div>
                                                </div>
                                                <div class="cwb-clear-fix">
                                                    <div class="cwb-form-field cwb-form-width-50">
                                                        <label>E-mail *</label>
                                                        <input type="email" name="update_client_email_address" autocomplete="off" value="amalikrigger93@gmail.com" required title="Please enter a valid email address.">
                                                    </div>
                                                    <div class="cwb-form-field cwb-form-width-50">
                                                        <label>Phone Number *</label>
                                                        <input type="tel" name="update_client_phone_number" autocomplete="off" value="" required pattern="\+?[0-9\s\-]+" title="Phone number should only contain numbers, spaces, dashes, and an optional leading '+'.">
                                                    </div>
                                                </div>
                                                <div class="cwb-clear-fix">
                                                    <div class="cwb-form-field cwb-form-width-100">
                                                        <label>Vehicle Make and Model *</label>
                                                        <input type="text" name="update_client_vehicle" autocomplete="off" value="" required pattern="[A-Za-z0-9\s\-]+" title="Vehicle make and model should only contain letters, numbers, spaces, and dashes.">
                                                    </div>
                                                </div>
                                                <div class="cwb-form-summary cwb-clear-fix">
                                                    <a class="cwb-button" href="#">Save</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div aria-labelledby="ui-id-3" role="tabpanel" class="ui-tabs-panel  " aria-hidden="true" style="display: none;"></div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div id="cwb-preloader" class="" data-counter="0"></div>
                </form>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('cwb_booking_form', 'cwb_render_booking_form');
?>

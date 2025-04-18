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
            <div class="cwb-form-field cwb-form-width-50 cwb-location-field-latitude">
                <label>Latitude *</label>
                <input type="text" name="client_latitude" autocomplete="off" value="" required pattern="-?\d+(\.\d+)?" title="Please enter a valid latitude (e.g., 37.7749).">
            </div>
            <div class="cwb-form-field cwb-form-width-50 cwb-location-field-longitude">
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
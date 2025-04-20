<li class="cwb-main-list-item cwb-clear-fix cwb-main-list-item-booking">
    <div class="cwb-main-list-item-section-header cwb-clear-fix">
        <span class="cwb-main-list-item-section-header-step" aria-hidden="true">
            <span>6</span>
            <span>/6</span>
        </span>
        <h4 class="cwb-main-list-item-section-header-header" id="booking-summary-title">
            <span>Booking summary</span>
        </h4>
        <h5 class="cwb-main-list-item-section-header-subheader">
            <span>Please provide us with your contact information.</span>
        </h5>
    </div>
    <div class="cwb-main-list-item-section-content cwb-clear-fix">
        <div class="cwb-main-list-item-section-content cwb-clear-fix">
            <!-- Booking details summary section -->
            <ul class="cwb-booking-summary cwb-list-reset cwb-clear-fix" aria-labelledby="booking-summary-title">
                <li class="cwb-booking-summary-date">
                    <div class="cwb-meta-icon cwb-meta-icon-date" aria-hidden="true">
                        <i class="fa-regular fa-calendar-days"></i>
                    </div>
                    <h5>
                        <span class="cwb-visuallyhidden">Date: </span>
                        <span>?</span>
                    </h5>
                    <span>Your Appointment Date</span>
                </li>
                <li class="cwb-booking-summary-time">
                    <div class="cwb-meta-icon cwb-meta-icon-time" aria-hidden="true">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                    <h5>
                        <span class="cwb-visuallyhidden">Time: </span>
                        <span>?</span>
                    </h5>
                    <span>Your Appointment Time</span>
                </li>
                <li class="cwb-booking-summary-duration">
                    <div class="cwb-meta-icon cwb-meta-icon-total-duration" aria-hidden="true">
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
                    <div class="cwb-meta-icon cwb-meta-icon-total-price" aria-hidden="true">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>
                    <h5>
                        <span>$</span>
                        <span>0.00</span>
                    </h5>
                    <span>Total Price</span>
                </li>
            </ul>

            <!-- Selected services summary section -->
            <div class="cwb-selected-services-container cwb-state-hidden">
                <h5>Your Selected Services</h5>
                <div class="cwb-selected-services">
                    <div class="cwb-selected-location">
                        <strong>Location:</strong> <span class="cwb-summary-location-name">Not selected</span>
                    </div>
                    <div class="cwb-selected-vehicle">
                        <strong>Vehicle Type:</strong> <span class="cwb-summary-vehicle-name">Not selected</span>
                    </div>
                    <div class="cwb-selected-package">
                        <strong>Package:</strong> <span class="cwb-summary-package-name">Not selected</span>
                    </div>
                    <div class="cwb-selected-addons cwb-state-hidden">
                        <strong>Add-ons:</strong>
                        <ul class="cwb-summary-addon-list cwb-list-reset">
                            <!-- Add-ons will be populated dynamically -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="cwb-contact-details-options cwb-state-hidden">
            <a class="cwb-button" href="#">Log in</a> or <a class="cwb-button" href="#">Place order</a>
        </div>
        
        <!-- Status notifications -->
        <div class="cwb-notice cwb-notice-contact-details">
            <div class="cwb-meta-icon"></div>
            <div class="cwb-notice-content">
                <div class="cwb-notice-header"></div>
                <div class="cwb-notice-text"></div>
            </div>
        </div>
        
        <!-- Form tabs -->
        <div class="cwb-to-tab ui-tabs">
            <ul role="tablist" class="ui-tabs-nav ui-helper-clearfix">
                <li role="tab" tabindex="0" class="ui-tabs-tab ui-tab ui-tabs-active ui-state-active" 
                    aria-controls="cwb-current-order" aria-selected="true" aria-expanded="true">
                    <a href="#cwb-current-order" tabindex="-1" class="ui-tabs-anchor">
                        <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i> Current Order
                    </a>
                </li>
                <li role="tab" tabindex="-1" class="ui-tabs-tab ui-tab" 
                    aria-controls="cwb-user-details" aria-selected="false" aria-expanded="false">
                    <a href="#cwb-user-details" tabindex="-1" class="ui-tabs-anchor">
                        <i class="fa-solid fa-user" aria-hidden="true"></i> Your Details
                    </a>
                </li>
                <li role="tab" tabindex="-1" class="ui-tabs-tab ui-tab" 
                    aria-controls="cwb-user-log-out" aria-selected="false" aria-expanded="false">
                    <a href="#cwb-user-log-out" tabindex="-1" class="ui-tabs-anchor">
                        <i class="fa-solid fa-sign-out-alt" aria-hidden="true"></i> Log Out
                    </a>
                </li>
            </ul>
            
            <!-- Form fields are included here -->
            <div id="cwb-current-order" role="tabpanel" aria-labelledby="ui-id-1" class="ui-tabs-panel">
                <?php include plugin_dir_path(__FILE__) . 'booking-widget-fields.php'; ?>
            </div>
            
            <!-- Other tab panels would be defined here -->
            <div id="cwb-user-details" role="tabpanel" aria-labelledby="ui-id-2" class="ui-tabs-panel cwb-state-hidden">
                <!-- User details content -->
            </div>
            
            <div id="cwb-user-log-out" role="tabpanel" aria-labelledby="ui-id-3" class="ui-tabs-panel cwb-state-hidden">
                <!-- Logout confirmation -->
            </div>
        </div>
    </div>
</li>

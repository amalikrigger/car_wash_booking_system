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
                    <?php
                    include plugin_dir_path(__FILE__) . 'booking-widget-fields.php';
                    ?>
            </div>
        </div>
    </div>
</li>
<li class="cwb-main-list-item cwb-main-list-item-calendar cwb-clear-fix">
    <div class="cwb-main-list-item-section-header cwb-clear-fix">
            <span class="cwb-main-list-item-section-header-step" aria-hidden="true">
            <span>5</span>
            <span>/6</span>
            </span>
        <h4 class="cwb-main-list-item-section-header-header" id="calendar-section-title">
            <span>Select date and time</span></h4>
        <h5 class="cwb-main-list-item-section-header-subheader">
            <span>Click on any time to make a booking.</span>
        </h5>
    </div>
    <div class="cwb-main-list-item-section-content cwb-clear-fix">
        <div class="cwb-calendar-container" aria-labelledby="calendar-section-title">
            <!-- Calendar header with navigation -->
            <div class="cwb-calendar-header">
                <button type="button" class="cwb-calendar-header-arrow-left cwb-meta-icon cwb-meta-icon-arrow-horizontal"
                        aria-label="Previous week">
                    <i class="fa-solid fa-arrow-left"></i>
                </button>
                <span class="cwb-calendar-header-caption" role="heading" aria-level="5">
                    <!-- Header will update dynamically with month/year -->
                </span>
                <button type="button" class="cwb-calendar-header-arrow-right cwb-meta-icon cwb-meta-icon-arrow-horizontal"
                        aria-label="Next week">
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>

            <!-- Calendar table -->
            <div class="cwb-calendar-table-wrapper">
                <div class="cwb-loading-overlay" style="display: none;">
                    <div class="cwb-loading-spinner">
                        <i class="fa-solid fa-spinner fa-spin"></i> Loading availability...
                    </div>
                </div>

                <table class="cwb-calendar" role="grid" aria-label="Booking calendar">
                    <tbody>
                        <tr class="cwb-calendar-subheader" role="row">
                            <!-- Days and dates will populate here dynamically -->
                        </tr>
                        <tr class="cwb-calendar-data" role="row">
                            <!-- Availability data will populate here dynamically -->
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</li>
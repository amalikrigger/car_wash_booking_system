/**
 * Car Wash Booking System Frontend JavaScript
 *
 * Handles all booking-related functionality including:
 * - Location selection
 * - Vehicle selection
 * - Package selection
 * - Add-on selection
 * - Date/time selection
 * - Form fields management
 */

(function($) {
    'use strict';

    // Main booking module
    const CWB_Booking = {
        // State variables
        state: {
            selectedDate: null,
            selectedTime: null,
            selectedDuration: 0,
            selectedPrice: 0.00,
            selectedAddons: [],
            selectedPackageDuration: null,
            currentDate: new Date(),
            currentSelectedLocationId: null,
            selectedVehicleId: null,
            loadingStates: {
                calendar: false,
                vehicles: false,
                packages: false,
                addons: false
            }
        },

        // Cache DOM elements for better performance
        elements: {
            vehicleList: $("#cwb-vehicle-list"),
            packageList: $("#cwb-package-list"),
            addonList: $("#cwb-addon-list"),
            calendarHeader: $(".cwb-calendar-header-caption"),
            calendarSubheader: $(".cwb-calendar-subheader"),
            calendarData: $(".cwb-calendar-data"),
            calendarContainer: $(".cwb-calendar-container"),
            calendarLoadingOverlay: $(".cwb-loading-overlay"),
            summaryDate: $(".cwb-booking-summary-date h5 span:nth-child(2)"),
            summaryTime: $(".cwb-booking-summary-time h5 span:nth-child(2)"),
            summaryDurationHours: $(".cwb-booking-summary-duration h5 span:nth-child(1)"),
            summaryDurationMinutes: $(".cwb-booking-summary-duration h5 span:nth-child(3)"),
            summaryPrice: $(".cwb-booking-summary-price h5 span:nth-child(2)"),
            serviceListContainer: $(".cwb-main-list-item-service-list"),
            vehicleLoadingIndicator: $(".cwb-vehicle-container .cwb-loading-indicator"),
            vehicleEmptyState: $(".cwb-vehicle-container .cwb-empty-state"),
            vehicleSelectionHelp: $(".cwb-vehicle-container .cwb-selection-help")
        },

        // Configuration
        config: {
            locationFieldsConfigs: cwb_ajax.location_fields_configs || {},
            ajaxUrl: cwb_ajax.ajax_url,
            nonce: cwb_ajax.nonce,
            debug: cwb_ajax.debug || false
        },

        /**
         * Initialize the booking system
         */
        init: function() {
            this.resetSelections();
            this.bindEvents();
            this.initDefaultLocation();
            this.renderCalendar([], this.state.currentDate);
        },

        /**
         * Reset all selections to default values
         */
        resetSelections: function() {
            this.state.selectedDate = null;
            this.state.selectedTime = null;
            this.state.selectedDuration = 0;
            this.state.selectedPrice = 0.00;
            this.state.selectedAddons = [];
            this.state.selectedPackageDuration = null;

            $(".cwb-package").removeClass("cwb-state-selected");
            this.elements.addonList.empty();
            this.elements.serviceListContainer.addClass("cwb-state-disable");

            this.updateBookingSummary();
        },

        /**
         * Bind all event handlers
         */
        bindEvents: function() {
            // Navigation arrows for calendar
            $(".cwb-calendar-header-arrow-left").on("click", () => {
                this.updateCalendar(-7); // Move back a week
            });

            $(".cwb-calendar-header-arrow-right").on("click", () => {
                this.updateCalendar(7); // Move forward a week
            });

            // Location selection
            $(document).on("click", ".cwb-location", (e) => {
                const locationId = $(e.currentTarget).data("id");
                const isAlreadySelected = $(e.currentTarget).hasClass("cwb-state-selected");

                if (locationId !== this.state.currentSelectedLocationId) {
                    $(".cwb-location").removeClass("cwb-state-selected");
                    $(e.currentTarget).addClass("cwb-state-selected");
                    this.resetSelections();
                    this.renderCalendar([], this.state.currentDate);
                    this.loadVehicles(locationId);
                    this.state.currentSelectedLocationId = locationId;
                    this.updateBookingFormFields(locationId);
                } else if (!isAlreadySelected) {
                    $(".cwb-location").removeClass("cwb-state-selected");
                    $(e.currentTarget).addClass("cwb-state-selected");
                }
            });

            // Vehicle selection
            this.elements.vehicleList.on("click", ".cwb-vehicle", (e) => {
                const vehicleTypeId = $(e.currentTarget).data("id");
                this.state.selectedVehicleId = vehicleTypeId;
                $(".cwb-vehicle").removeClass("cwb-state-selected");
                $(e.currentTarget).addClass("cwb-state-selected");
                this.loadPackages(vehicleTypeId);
            });

            // Package selection
            this.elements.packageList.on("click", ".cwb-package", (e) => {
                const packageItem = $(e.currentTarget);
                const packageId = packageItem.data("id");
                const packageDuration = parseInt(packageItem.data("duration"), 10);
                const packagePrice = parseFloat(packageItem.data("price"));
                const isSelected = packageItem.hasClass("cwb-state-selected");

                if (isNaN(packagePrice)) {
                    this.showError("Invalid package price. Please contact support.");
                    return;
                }

                if (isSelected) {
                    packageItem.removeClass("cwb-state-selected");
                    this.resetSelections();
                    this.elements.addonList.empty();
                    this.elements.serviceListContainer.addClass("cwb-state-disable");
                } else {
                    $(".cwb-package").removeClass("cwb-state-selected");
                    packageItem.addClass("cwb-state-selected");

                    this.state.selectedDate = null;
                    this.state.selectedTime = null;
                    this.state.selectedAddons = [];
                    this.state.selectedDuration = packageDuration;
                    this.state.selectedPrice = packagePrice;
                    this.state.selectedPackageDuration = packageDuration;

                    this.loadAddons(packageId);
                    this.fetchAvailableSlots(this.state.currentDate);
                    this.updateBookingSummary();
                }
            });

            // Addon selection
            this.elements.addonList.on("click", ".cwb-button", (e) => {
                const addonItem = $(e.currentTarget).closest("li");
                const addonId = addonItem.data("id");
                const addonDuration = parseInt(addonItem.data("duration"), 10);
                const addonPrice = parseFloat(addonItem.data("price"));

                if (isNaN(addonPrice)) {
                    this.showError("Invalid addon price. Please contact support.");
                    return;
                }

                this.state.selectedDate = null;
                this.state.selectedTime = null;

                if (addonItem.hasClass("cwb-state-selected")) {
                    addonItem.removeClass("cwb-state-selected");
                    this.state.selectedAddons = this.state.selectedAddons.filter(id => id !== addonId);
                    this.state.selectedDuration -= addonDuration;
                    this.state.selectedPrice -= addonPrice;
                } else {
                    addonItem.addClass("cwb-state-selected");
                    this.state.selectedAddons.push(addonId);
                    this.state.selectedDuration += addonDuration;
                    this.state.selectedPrice += addonPrice;
                }

                this.updateBookingSummary();
                this.fetchAvailableSlots(this.state.currentDate);
            });

            // Calendar slot selection
            $(document).on("click", ".cwb-calendar-data a", (e) => {
                $(".cwb-calendar-data .cwb-state-selected").removeClass("cwb-state-selected");

                const listItem = $(e.currentTarget).parent();
                listItem.addClass("cwb-state-selected");

                this.state.selectedTime = $(e.currentTarget).data("time");

                const cell = listItem.closest("td");
                const selectedIndex = cell.index();
                const selectedDateStr = $(".cwb-calendar-subheader th").eq(selectedIndex).attr("data-full-date");

                if (selectedDateStr) {
                    const parts = selectedDateStr.split("-");
                    const dateObj = new Date(parts[0], parts[1] - 1, parts[2]);
                    this.state.selectedDate = dateObj.toLocaleDateString("en-US", {
                        weekday: "long",
                        day: "numeric",
                        month: "long",
                        year: "numeric"
                    });
                }

                this.updateBookingSummary();
            });

            // Checkbox toggle
            $(".cwb-form-checkbox").on("click", function() {
                const checkbox = $(this).next("input[type='checkbox']");
                checkbox.prop("checked", !checkbox.prop("checked"));
                $(this).toggleClass("cwb-state-selected", checkbox.prop("checked"));
            });

            // Prevent form submission on agreement click
            $(".cwb-agreement div").on("click", function(e) {
                e.stopPropagation();
            });
        },

        /**
         * Initialize with default location if available
         */
        initDefaultLocation: function() {
            const defaultLocation = $(".cwb-location[data-default='true']");
            if (defaultLocation.length) {
                const defaultLocationId = defaultLocation.data("id");
                this.loadVehicles(defaultLocationId);
                this.state.currentSelectedLocationId = defaultLocationId;
                this.updateBookingFormFields(defaultLocationId);
            }
        },

        /**
         * Set loading state for a specific component
         * @param {string} component - Component name ('calendar', 'vehicles', 'packages', 'addons')
         * @param {boolean} isLoading - Whether the component is loading
         */
        setLoadingState: function(component, isLoading) {
            if (this.state.loadingStates.hasOwnProperty(component)) {
                this.state.loadingStates[component] = isLoading;
                this.logDebug(`Loading state for ${component}: ${isLoading}`);
            }
        },

        /**
         * Show calendar loading state
         */
        showCalendarLoading: function() {
            this.setLoadingState('calendar', true);
            this.elements.calendarLoadingOverlay.fadeIn(200);
        },

        /**
         * Hide calendar loading state
         */
        hideCalendarLoading: function() {
            this.setLoadingState('calendar', false);
            this.elements.calendarLoadingOverlay.fadeOut(200);
        },

        /**
         * Show vehicle loading state
         */
        showVehicleLoading: function() {
            this.setLoadingState('vehicles', true);
            this.elements.vehicleEmptyState.addClass("cwb-state-hidden");
            this.elements.vehicleSelectionHelp.addClass("cwb-state-hidden");
            this.elements.vehicleLoadingIndicator.removeClass("cwb-state-hidden");
            this.elements.vehicleList.addClass("cwb-state-hidden");
            this.elements.vehicleList.empty(); // Clear any existing vehicles
        },

        /**
         * Hide vehicle loading state and update UI based on vehicle availability
         * @param {boolean} hasVehicles - Whether vehicles were returned
         */
        hideVehicleLoading: function(hasVehicles) {
            this.setLoadingState('vehicles', false);
            this.elements.vehicleLoadingIndicator.addClass("cwb-state-hidden");

            if (hasVehicles) {
                this.logDebug("Vehicles found, showing vehicle list");
                this.elements.vehicleList.removeClass("cwb-state-hidden");
                this.elements.vehicleSelectionHelp.removeClass("cwb-state-hidden");
                this.elements.vehicleEmptyState.addClass("cwb-state-hidden");
            } else {
                this.logDebug("No vehicles found, showing empty state");
                this.elements.vehicleEmptyState.removeClass("cwb-state-hidden");
                this.elements.vehicleList.addClass("cwb-state-hidden");
                this.elements.vehicleSelectionHelp.addClass("cwb-state-hidden");
            }
        },

        /**
         * Show package loading state
         */
        showPackageLoading: function() {
            this.setLoadingState('packages', true);
            // Visual loading indicator for packages could be added here
            this.showCalendarLoading(); // Use calendar overlay for now
        },

        /**
         * Hide package loading state
         */
        hidePackageLoading: function() {
            this.setLoadingState('packages', false);
            this.hideCalendarLoading(); // Use calendar overlay for now
        },

        /**
         * Show addon loading state
         */
        showAddonLoading: function() {
            this.setLoadingState('addons', true);
            // Visual loading indicator for addons could be added here
            this.showCalendarLoading(); // Use calendar overlay for now
        },

        /**
         * Hide addon loading state
         */
        hideAddonLoading: function() {
            this.setLoadingState('addons', false);
            this.hideCalendarLoading(); // Use calendar overlay for now
        },

        /**
         * Check if any component is in loading state
         * @returns {boolean} - True if any component is loading
         */
        isAnyLoading: function() {
            return Object.values(this.state.loadingStates).some(state => state === true);
        },

        /**
         * Show error message
         * @param {string} message - Error message to display
         */
        showError: function(message) {
            let errorContainer = $(".cwb-error-message");
            if (!errorContainer.length) {
                errorContainer = $('<div class="cwb-error-message"></div>');
                this.elements.calendarContainer.before(errorContainer);
            }

            errorContainer.text(message).fadeIn(300);
            setTimeout(() => errorContainer.fadeOut(300), 4000);

            if (this.config.debug) {
                console.error(message);
            }
        },

        /**
         * Log debug information if debug mode is enabled
         * @param {string} message - Debug message
         * @param {*} data - Additional data to log
         */
        logDebug: function(message, data) {
            if (this.config.debug) {
                console.log(message, data);
            }
        },

        /**
         * Update the booking form fields based on location configuration
         * @param {number|string} locationId - Location ID
         */
        updateBookingFormFields: function(locationId) {
            const configArray = this.config.locationFieldsConfigs[locationId] || [];
            const fields = [
                'street',
                'zip-code',
                'city',
                'state',
                'country',
                'message',
                'gratuity',
                'service-location',
                'lat-long'
            ];

            fields.forEach(field => {
                const fieldConfig = configArray.find(item => item.field_name === field);
                const enabled = fieldConfig && (fieldConfig.is_visible === '1' || fieldConfig.is_visible === 1);
                const fieldElement = $(`.cwb-${field}-field`);

                if (fieldElement.length) {
                    this.logDebug(`Field visibility: ${field}`, enabled);
                    fieldElement.toggleClass('cwb-state-hidden', !enabled);
                }
            });
        },

        /**
         * Update the booking summary with current selections
         */
        updateBookingSummary: function() {
            this.elements.summaryDate.text(this.state.selectedDate || "?");
            this.elements.summaryTime.text(this.state.selectedTime || "?");

            const hours = Math.floor(this.state.selectedDuration / 60);
            const minutes = this.state.selectedDuration % 60;

            this.elements.summaryDurationHours.text(hours);
            this.elements.summaryDurationMinutes.text(minutes);

            this.elements.summaryPrice.text(this.state.selectedPrice.toFixed(2));
        },

        /**
         * Load vehicles for a specific location
         * @param {number|string} locationId - Location ID
         */
        loadVehicles: function(locationId) {
            this.showVehicleLoading();

            $.ajax({
                url: this.config.ajaxUrl,
                type: "POST",
                data: {
                    action: "cwb_get_vehicles",
                    nonce: this.config.nonce,
                    location_id: locationId,
                },
                success: (response) => {
                    this.logDebug("Vehicles response received", response);

                    // Add the HTML to the DOM
                    this.elements.vehicleList.html(response);

                    // Check for vehicles using direct DOM inspection after insertion
                    const vehicleCount = this.elements.vehicleList.children('.cwb-vehicle').length;
                    const hasVehicles = vehicleCount > 0;

                    this.logDebug(`Vehicle count: ${vehicleCount}`, hasVehicles);

                    // Update UI based on vehicle availability
                    this.hideVehicleLoading(hasVehicles);

                    if (hasVehicles) {
                        // Force a repaint to ensure the DOM is updated
                        this.elements.vehicleList[0].offsetHeight;

                        // Get the first vehicle and select it
                        const firstVehicle = this.elements.vehicleList.children('.cwb-vehicle').first();

                        if (firstVehicle.length) {
                            this.logDebug("Selecting first vehicle", firstVehicle);

                            // Remove any existing selections
                            this.elements.vehicleList.find(".cwb-vehicle").removeClass("cwb-state-selected");

                            // Select the first vehicle
                            firstVehicle.addClass("cwb-state-selected");

                            // Get and store the vehicle ID
                            const firstVehicleId = firstVehicle.data("id");
                            this.state.selectedVehicleId = firstVehicleId;

                            // Load packages for this vehicle with a slight delay
                            setTimeout(() => {
                                this.loadPackages(firstVehicleId);
                            }, 100);
                        } else {
                            this.logDebug("First vehicle not found even though vehicles exist");
                        }
                    } else {
                        this.elements.packageList.empty();
                        this.elements.addonList.empty();
                        this.elements.serviceListContainer.addClass("cwb-state-disable");
                    }
                },
                error: (xhr, status, error) => {
                    this.showError("Failed to load vehicles. Please try again.");
                    this.logDebug("Vehicle loading error", error);
                    this.hideVehicleLoading(false);
                }
            });
        },

        /**
         * Load packages for a specific vehicle type
         * @param {number|string} vehicleTypeId - Vehicle type ID
         */
        loadPackages: function(vehicleTypeId) {
            this.showPackageLoading();

            $.ajax({
                url: this.config.ajaxUrl,
                type: "POST",
                data: {
                    action: "cwb_get_packages",
                    nonce: this.config.nonce,
                    vehicle_type_id: vehicleTypeId,
                },
                success: (response) => {
                    this.logDebug("Packages loaded", response);
                    this.elements.packageList.html(response);
                    this.hidePackageLoading();
                },
                error: (xhr, status, error) => {
                    this.showError("Failed to load packages. Please try again.");
                    this.logDebug("Package loading error", error);
                    this.hidePackageLoading();
                }
            });
        },

        /**
         * Load add-ons for a specific package
         * @param {number|string} packageId - Package ID
         */
        loadAddons: function(packageId) {
            this.showAddonLoading();

            $.ajax({
                url: this.config.ajaxUrl,
                type: "POST",
                data: {
                    action: "cwb_get_addons",
                    nonce: this.config.nonce,
                    package_id: packageId,
                },
                success: (response) => {
                    this.logDebug("Add-ons loaded", response);
                    this.elements.addonList.html(response);

                    const hasAddons = response.trim() !== "" && $(response).find("li").length > 0;
                    this.elements.serviceListContainer.toggleClass("cwb-state-disable", !hasAddons);

                    this.hideAddonLoading();
                },
                error: (xhr, status, error) => {
                    this.showError("Failed to load add-on services. Please try again.");
                    this.logDebug("Add-on loading error", error);
                    this.hideAddonLoading();
                }
            });
        },

        /**
         * Update calendar by moving dates
         * @param {number} days - Number of days to move (positive or negative)
         */
        updateCalendar: function(days) {
            const newDate = new Date(this.state.currentDate);
            newDate.setDate(newDate.getDate() + days);
            this.state.currentDate = newDate;
            this.fetchAvailableSlots(newDate);
        },

        /**
         * Fetch available time slots for a specific date
         * @param {Date} date - Date to check for availability
         */
        fetchAvailableSlots: function(date) {
            if (!this.state.selectedPackageDuration) {
                this.logDebug("No package selected");
                this.renderCalendar({}, date);
                return;
            }

            this.showCalendarLoading();

            let totalDuration = this.state.selectedPackageDuration;

            // Add duration from selected add-ons
            this.state.selectedAddons.forEach(addonId => {
                const addonElement = $(`#cwb-addon-list .cwb-service-id-${addonId}.cwb-state-selected`);
                if (addonElement.length) {
                    totalDuration += parseInt(addonElement.data("duration"), 10);
                }
            });

            const formattedDate = date.toISOString().split("T")[0];

            $.ajax({
                url: this.config.ajaxUrl,
                type: "POST",
                data: {
                    action: "cwb_get_available_slots",
                    nonce: this.config.nonce,
                    date: formattedDate,
                    duration: totalDuration
                },
                success: (response) => {
                    if (response.success && response.data?.slots) {
                        this.logDebug("Available slots", response.data.slots);
                        this.renderCalendar(response.data.slots, date);
                    } else {
                        this.logDebug("No available slots", response);
                        this.renderCalendar({}, date);
                    }
                    this.hideCalendarLoading();
                },
                error: (xhr, status, error) => {
                    this.showError("Failed to fetch available time slots. Please try again.");
                    this.logDebug("Slot fetching error", error);
                    this.renderCalendar({}, date);
                    this.hideCalendarLoading();
                }
            });
        },

        /**
         * Render the calendar with available slots
         * @param {Object} slots - Available time slots by date
         * @param {Date} startDate - Starting date for the calendar
         */
        renderCalendar: function(slots, startDate) {
            this.elements.calendarSubheader.empty();
            this.elements.calendarData.empty();

            for (let i = 0; i < 7; i++) {
                const dayDate = new Date(startDate.getTime());
                dayDate.setDate(startDate.getDate() + i);

                const dayKey = dayDate.toISOString().split('T')[0];
                const dayName = dayDate.toLocaleDateString("en-US", {weekday: "short"});
                const dayNumber = dayDate.getDate();
                const formattedDate = dayDate.toLocaleDateString("en-CA");

                const slotData = slots[dayKey] || [];

                // Add header
                this.elements.calendarSubheader.append(`
                    <th data-full-date="${formattedDate}" role="columnheader">
                        <div class="cwb-clear-fix">
                            <span class="cwb-calendar-subheader-day-number">${dayNumber}</span>
                            <span class="cwb-calendar-subheader-day-name">${dayName}</span>
                        </div>
                    </th>
                `);

                // Add time slots
                let dataCell = `<td role="gridcell" data-date="${formattedDate}"><div>`;

                if (slotData.length > 0) {
                    dataCell += `<ul class="cwb-list-reset">`;
                    slotData.forEach((slot) => {
                        dataCell += `<li><a href="#" data-time="${slot}" tabindex="0">${slot}</a></li>`;
                    });
                    dataCell += `</ul>`;
                } else {
                    dataCell += `<div class="cwb-no-slots-message">Not available</div>`;
                }

                dataCell += `</div></td>`;
                this.elements.calendarData.append(dataCell);
            }

            const startMonth = startDate.toLocaleDateString("en-US", {month: "long", year: "numeric"});
            const endDate = new Date(startDate.getTime());
            endDate.setDate(startDate.getDate() + 6);
            const endMonth = endDate.toLocaleDateString("en-US", {month: "long", year: "numeric"});

            this.elements.calendarHeader.html(
                startMonth === endMonth
                    ? `<span>${startMonth}</span>`
                    : `<span>${startMonth}</span> / <span>${endMonth}</span>`
            );
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        CWB_Booking.init();
    });

})(jQuery);


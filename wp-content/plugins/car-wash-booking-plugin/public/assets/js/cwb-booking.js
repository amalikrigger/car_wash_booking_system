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
        // Constants for class names and actions
        constants: {
            // CSS class names
            STATE_SELECTED: 'cwb-state-selected',
            STATE_HIDDEN: 'cwb-state-hidden',
            STATE_DISABLE: 'cwb-state-disable',
            
            // AJAX action names
            ACTION_GET_LOCATIONS: 'cwb_get_locations',
            ACTION_GET_VEHICLES: 'cwb_get_vehicles',
            ACTION_GET_PACKAGES: 'cwb_get_packages',
            ACTION_GET_ADDONS: 'cwb_get_addons',
            ACTION_GET_AVAILABLE_SLOTS: 'cwb_get_available_slots',
            
            // Selectors
            SELECTOR_VEHICLE: '.cwb-vehicle',
            SELECTOR_PACKAGE: '.cwb-package',
            SELECTOR_LOCATION: '.cwb-location',
            SELECTOR_ADDON: '.cwb-service-id-'
        },
        
        // State variables
        state: {
            selectedDate: null,
            selectedTime: null,
            selectedDuration: 0,
            selectedPrice: 0.00,
            selectedAddons: [],
            selectedPackageDuration: null,
            selectedPackageId: null,
            currentDate: new Date(),
            currentSelectedLocationId: null,
            selectedVehicleId: null,
            loadingStates: {
                locations: false,
                calendar: false,
                vehicles: false,
                packages: false,
                addons: false
            }
        },

        // Cache DOM elements for better performance
        elements: {
            locationList: $(".cwb-location-list"),
            locationLoadingIndicator: $(".cwb-location-container .cwb-loading-indicator"),
            locationEmptyState: $(".cwb-location-container .cwb-empty-state"),
            locationSelectionHelp: $(".cwb-location-container .cwb-selection-help"),
            vehicleList: $("#cwb-vehicle-list"),
            packageList: $("#cwb-package-list"),
            packageLoadingIndicator: $(".cwb-package-container .cwb-loading-indicator"),
            packageEmptyState: $(".cwb-package-container .cwb-empty-state"),
            packageSelectionHelp: $(".cwb-package-container .cwb-selection-help"),
            addonList: $("#cwb-addon-list"),
            addonLoadingIndicator: $(".cwb-addon-container .cwb-loading-indicator"),
            addonEmptyState: $(".cwb-addon-container .cwb-empty-state"),
            addonSelectionHelp: $(".cwb-addon-container .cwb-selection-help"),
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
            this.checkLocationAvailability();
            this.renderCalendar([], this.state.currentDate);
        },
        
        /**
         * Make an AJAX request with standardized error handling
         * @param {string} action - The AJAX action to perform
         * @param {object} data - Additional data for the request
         * @returns {Promise} - Promise that resolves with the response
         */
        async makeRequest(action, data = {}) {
            const requestData = {
                ...data,
                action,
                nonce: this.config.nonce
            };

            try {
                return await $.ajax({
                    url: this.config.ajaxUrl,
                    type: "POST",
                    data: requestData
                });
            } catch (error) {
                this.logDebug(`${action} error`, error);
                throw error; // Re-throw to let the caller handle it
            }
        },

        /**
         * Check if locations are available and handle the initial display
         */
        async checkLocationAvailability() {
            const locationsExist = this.elements.locationList.find(`li.${this.constants.SELECTOR_LOCATION.substring(1)}`).length > 0;
            this.logDebug(`Locations found: ${locationsExist}`);
            
            if (locationsExist) {
                this.hideLocationLoading(true);
                await this.initDefaultLocation();
            } else {
                this.showLocationLoading();
                await this.loadLocations();
            }
        },

        /**
         * Load locations via AJAX if they're not pre-rendered
         */
        async loadLocations() {
            this.showLocationLoading();
            
            try {
                const response = await this.makeRequest(this.constants.ACTION_GET_LOCATIONS);
                
                this.logDebug("Locations loaded", response);
                
                // Add the HTML to the DOM
                this.elements.locationList.html(response);
                
                // Check for locations
                const locationCount = this.elements.locationList.children(this.constants.SELECTOR_LOCATION).length;
                const hasLocations = locationCount > 0;
                
                this.logDebug(`Location count: ${locationCount}`, hasLocations);
                
                // Update UI based on location availability
                this.hideLocationLoading(hasLocations);
                
                if (hasLocations) {
                    await this.initDefaultLocation();
                }
            } catch (error) {
                this.showError("Failed to load locations. Please try again.");
                this.logDebug("Location loading error", error);
                this.hideLocationLoading(false);
            }
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
            this.state.selectedPackageId = null;

            $(".cwb-package").removeClass(this.constants.STATE_SELECTED);
            this.elements.addonList.empty();
            this.elements.serviceListContainer.addClass(this.constants.STATE_DISABLE);

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
            $(document).on("click", this.constants.SELECTOR_LOCATION, async (e) => {
                const locationId = $(e.currentTarget).data("id");
                const isAlreadySelected = $(e.currentTarget).hasClass(this.constants.STATE_SELECTED);

                if (locationId !== this.state.currentSelectedLocationId) {
                    $(this.constants.SELECTOR_LOCATION).removeClass(this.constants.STATE_SELECTED);
                    $(e.currentTarget).addClass(this.constants.STATE_SELECTED);
                    this.resetSelections();
                    this.renderCalendar([], this.state.currentDate);
                    await this.loadVehicles(locationId);
                    this.state.currentSelectedLocationId = locationId;
                    this.updateBookingFormFields(locationId);
                } else if (!isAlreadySelected) {
                    $(this.constants.SELECTOR_LOCATION).removeClass(this.constants.STATE_SELECTED);
                    $(e.currentTarget).addClass(this.constants.STATE_SELECTED);
                }
            });

            // Vehicle selection
            this.elements.vehicleList.on("click", this.constants.SELECTOR_VEHICLE, async (e) => {
                const vehicleTypeId = $(e.currentTarget).data("id");
                this.state.selectedVehicleId = vehicleTypeId;
                $(this.constants.SELECTOR_VEHICLE).removeClass(this.constants.STATE_SELECTED);
                $(e.currentTarget).addClass(this.constants.STATE_SELECTED);
                await this.loadPackages(vehicleTypeId);
            });

            // Package selection
            this.elements.packageList.on("click", this.constants.SELECTOR_PACKAGE, async (e) => {
                const packageItem = $(e.currentTarget);
                const packageId = packageItem.data("id");
                const packageDuration = parseInt(packageItem.data("duration"), 10);
                const packagePrice = parseFloat(packageItem.data("price"));
                const isSelected = packageItem.hasClass(this.constants.STATE_SELECTED);

                if (isNaN(packagePrice)) {
                    this.showError("Invalid package price. Please contact support.");
                    return;
                }

                if (isSelected) {
                    packageItem.removeClass(this.constants.STATE_SELECTED);
                    this.state.selectedPackageId = null;
                    this.resetSelections();
                    this.elements.addonList.empty();
                    this.elements.serviceListContainer.addClass(this.constants.STATE_DISABLE);
                } else {
                    $(this.constants.SELECTOR_PACKAGE).removeClass(this.constants.STATE_SELECTED);
                    packageItem.addClass(this.constants.STATE_SELECTED);
                    
                    this.state.selectedPackageId = packageId;
                    this.state.selectedDate = null;
                    this.state.selectedTime = null;
                    this.state.selectedAddons = [];
                    this.state.selectedDuration = packageDuration;
                    this.state.selectedPrice = packagePrice;
                    this.state.selectedPackageDuration = packageDuration;

                    await this.loadAddons(packageId);
                    await this.fetchAvailableSlots(this.state.currentDate);
                    this.updateBookingSummary();
                }
            });

            // Addon selection
            this.elements.addonList.on("click", ".cwb-button", async (e) => {
                e.preventDefault(); // Prevent page scrolling to top
                
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

                if (addonItem.hasClass(this.constants.STATE_SELECTED)) {
                    addonItem.removeClass(this.constants.STATE_SELECTED);
                    this.state.selectedAddons = this.state.selectedAddons.filter(id => id !== addonId);
                    this.state.selectedDuration -= addonDuration;
                    this.state.selectedPrice -= addonPrice;
                } else {
                    addonItem.addClass(this.constants.STATE_SELECTED);
                    this.state.selectedAddons.push(addonId);
                    this.state.selectedDuration += addonDuration;
                    this.state.selectedPrice += addonPrice;
                }

                this.updateBookingSummary();
                await this.fetchAvailableSlots(this.state.currentDate);
            });

            // Calendar slot selection
            $(document).on("click", ".cwb-calendar-data a", (e) => {
                e.preventDefault(); // Prevent page scrolling to top
                
                $(".cwb-calendar-data .cwb-state-selected").removeClass(this.constants.STATE_SELECTED);

                const listItem = $(e.currentTarget).parent();
                listItem.addClass(this.constants.STATE_SELECTED);

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
                $(this).toggleClass(this.constants.STATE_SELECTED, checkbox.prop("checked"));
            });

            // Prevent form submission on agreement click
            $(".cwb-agreement div").on("click", function(e) {
                e.stopPropagation();
            });
        },

        /**
         * Initialize with default location if available
         */
        async initDefaultLocation() {
            const defaultLocation = $(`${this.constants.SELECTOR_LOCATION}[data-default='true']`);
            if (defaultLocation.length) {
                const defaultLocationId = defaultLocation.data("id");
                this.state.currentSelectedLocationId = defaultLocationId;
                this.updateBookingFormFields(defaultLocationId);
                await this.loadVehicles(defaultLocationId);
            }
        },

        /**
         * Set loading state for a specific component
         * @param {string} component - Component name ('locations', 'calendar', 'vehicles', 'packages', 'addons')
         * @param {boolean} isLoading - Whether the component is loading
         */
        setLoadingState: function(component, isLoading) {
            if (this.state.loadingStates.hasOwnProperty(component)) {
                this.state.loadingStates[component] = isLoading;
                this.logDebug(`Loading state for ${component}: ${isLoading}`);
            }
        },

        /**
         * Show location loading state
         */
        showLocationLoading: function() {
            this.setLoadingState('locations', true);
            this.elements.locationEmptyState.addClass(this.constants.STATE_HIDDEN);
            this.elements.locationSelectionHelp.addClass(this.constants.STATE_HIDDEN);
            this.elements.locationLoadingIndicator.removeClass(this.constants.STATE_HIDDEN);
            this.elements.locationList.addClass(this.constants.STATE_HIDDEN);
        },

        /**
         * Hide location loading state and update UI based on location availability
         * @param {boolean} hasLocations - Whether locations were returned
         */
        hideLocationLoading: function(hasLocations) {
            this.setLoadingState('locations', false);
            this.elements.locationLoadingIndicator.addClass(this.constants.STATE_HIDDEN);
            
            if (hasLocations) {
                this.logDebug("Locations found, showing location list");
                this.elements.locationList.removeClass(this.constants.STATE_HIDDEN);
                
                // Only show help text if multiple locations exist
                if (this.elements.locationList.children(this.constants.SELECTOR_LOCATION).length > 1) {
                    this.elements.locationSelectionHelp.removeClass(this.constants.STATE_HIDDEN);
                }
                
                this.elements.locationEmptyState.addClass(this.constants.STATE_HIDDEN);
            } else {
                this.logDebug("No locations found, showing empty state");
                this.elements.locationEmptyState.removeClass(this.constants.STATE_HIDDEN);
                this.elements.locationList.addClass(this.constants.STATE_HIDDEN);
                this.elements.locationSelectionHelp.addClass(this.constants.STATE_HIDDEN);
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
            this.elements.vehicleEmptyState.addClass(this.constants.STATE_HIDDEN);
            this.elements.vehicleSelectionHelp.addClass(this.constants.STATE_HIDDEN);
            this.elements.vehicleLoadingIndicator.removeClass(this.constants.STATE_HIDDEN);
            this.elements.vehicleList.addClass(this.constants.STATE_HIDDEN);
            this.elements.vehicleList.empty(); // Clear any existing vehicles
        },

        /**
         * Hide vehicle loading state and update UI based on vehicle availability
         * @param {boolean} hasVehicles - Whether vehicles were returned
         */
        hideVehicleLoading: function(hasVehicles) {
            this.setLoadingState('vehicles', false);
            this.elements.vehicleLoadingIndicator.addClass(this.constants.STATE_HIDDEN);

            if (hasVehicles) {
                this.logDebug("Vehicles found, showing vehicle list");
                this.elements.vehicleList.removeClass(this.constants.STATE_HIDDEN);
                this.elements.vehicleSelectionHelp.removeClass(this.constants.STATE_HIDDEN);
                this.elements.vehicleEmptyState.addClass(this.constants.STATE_HIDDEN);
            } else {
                this.logDebug("No vehicles found, showing empty state");
                this.elements.vehicleEmptyState.removeClass(this.constants.STATE_HIDDEN);
                this.elements.vehicleList.addClass(this.constants.STATE_HIDDEN);
                this.elements.vehicleSelectionHelp.addClass(this.constants.STATE_HIDDEN);
            }
        },

        /**
         * Show package loading state
         */
        showPackageLoading: function() {
            this.setLoadingState('packages', true);
            this.elements.packageEmptyState.addClass(this.constants.STATE_HIDDEN);
            this.elements.packageSelectionHelp.addClass(this.constants.STATE_HIDDEN);
            this.elements.packageLoadingIndicator.removeClass(this.constants.STATE_HIDDEN);
            this.elements.packageList.addClass(this.constants.STATE_HIDDEN);
            this.elements.packageList.empty(); // Clear any existing packages
        },

        /**
         * Hide package loading state and update UI based on package availability
         * @param {boolean} hasPackages - Whether packages were returned
         */
        hidePackageLoading: function(hasPackages) {
            this.setLoadingState('packages', false);
            this.elements.packageLoadingIndicator.addClass(this.constants.STATE_HIDDEN);
            
            if (hasPackages) {
                this.logDebug("Packages found, showing package list");
                this.elements.packageList.removeClass(this.constants.STATE_HIDDEN);
                this.elements.packageSelectionHelp.removeClass(this.constants.STATE_HIDDEN);
                this.elements.packageEmptyState.addClass(this.constants.STATE_HIDDEN);
            } else {
                this.logDebug("No packages found, showing empty state");
                this.elements.packageEmptyState.removeClass(this.constants.STATE_HIDDEN);
                this.elements.packageList.addClass(this.constants.STATE_HIDDEN);
                this.elements.packageSelectionHelp.addClass(this.constants.STATE_HIDDEN);
            }
        },

        /**
         * Show addon loading state
         */
        showAddonLoading: function() {
            this.setLoadingState('addons', true);
            this.elements.addonEmptyState.addClass(this.constants.STATE_HIDDEN);
            this.elements.addonSelectionHelp.addClass(this.constants.STATE_HIDDEN);
            this.elements.addonLoadingIndicator.removeClass(this.constants.STATE_HIDDEN);
            this.elements.addonList.addClass(this.constants.STATE_HIDDEN);
            this.elements.addonList.empty(); // Clear any existing addons
            this.elements.serviceListContainer.addClass(this.constants.STATE_DISABLE);
        },

        /**
         * Hide addon loading state and update UI based on addon availability
         * @param {boolean} hasAddons - Whether addons were returned
         */
        hideAddonLoading: function(hasAddons) {
            this.setLoadingState('addons', false);
            this.elements.addonLoadingIndicator.addClass(this.constants.STATE_HIDDEN);
            
            if (hasAddons) {
                this.logDebug("Addons found, showing addon list");
                this.elements.addonList.removeClass(this.constants.STATE_HIDDEN);
                this.elements.addonSelectionHelp.removeClass(this.constants.STATE_HIDDEN);
                this.elements.addonEmptyState.addClass(this.constants.STATE_HIDDEN);
                this.elements.serviceListContainer.removeClass(this.constants.STATE_DISABLE);
            } else {
                this.logDebug("No addons found, showing empty state");
                this.elements.addonEmptyState.removeClass(this.constants.STATE_HIDDEN);
                this.elements.addonList.addClass(this.constants.STATE_HIDDEN);
                this.elements.addonSelectionHelp.addClass(this.constants.STATE_HIDDEN);
                this.elements.serviceListContainer.removeClass(this.constants.STATE_DISABLE);
            }
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
                    fieldElement.toggleClass(this.constants.STATE_HIDDEN, !enabled);
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
            
            // Update selected services summary section
            this.updateSelectedServicesSummary();
        },

        /**
         * Update the selected services summary section
         */
        updateSelectedServicesSummary: function() {
            // Get references to elements
            const selectedServicesContainer = $(".cwb-selected-services-container");
            const locationName = $(".cwb-summary-location-name");
            const vehicleName = $(".cwb-summary-vehicle-name");
            const packageName = $(".cwb-summary-package-name");
            const addonContainer = $(".cwb-selected-addons");
            const addonList = $(".cwb-summary-addon-list");
            
            // Check if we have enough information to show the summary
            const hasLocationSelected = this.state.currentSelectedLocationId !== null;
            const hasVehicleSelected = this.state.selectedVehicleId !== null;
            const hasPackageSelected = this.state.selectedPackageId !== null;
            
            if (hasLocationSelected || hasVehicleSelected || hasPackageSelected) {
                selectedServicesContainer.removeClass(this.constants.STATE_HIDDEN);
                
                // Update location name
                if (hasLocationSelected) {
                    const selectedLocation = $(`${this.constants.SELECTOR_LOCATION}[data-id="${this.state.currentSelectedLocationId}"]`);
                    locationName.text(selectedLocation.find("div > div:first-child").text());
                } else {
                    locationName.text("Not selected");
                }
                
                // Update vehicle name with improved selector
                if (hasVehicleSelected) {
                    const selectedVehicle = $(`${this.constants.SELECTOR_VEHICLE}[data-id="${this.state.selectedVehicleId}"]`);
                    
                    // Try multiple selectors to find the vehicle name
                    let vehicleNameText = "";
                    
                    // First try the original selector
                    vehicleNameText = selectedVehicle.find("div > div:first-child").text().trim();
                    
                    // If that's empty, try other common patterns
                    if (!vehicleNameText) {
                        // Try direct text content
                        vehicleNameText = selectedVehicle.text().trim();
                        
                        // Try finding a heading or name element
                        if (!vehicleNameText || vehicleNameText.length > 30) {
                            const nameElement = selectedVehicle.find(".cwb-vehicle-name, h3, h4, .name, .title").first();
                            if (nameElement.length) {
                                vehicleNameText = nameElement.text().trim();
                            }
                        }
                    }
                    
                    // Log for debugging
                    this.logDebug("Selected vehicle element:", selectedVehicle);
                    this.logDebug("Found vehicle name:", vehicleNameText);
                    
                    // Use the found name or fallback
                    vehicleName.text(vehicleNameText || selectedVehicle.attr("title") || "Selected Vehicle");
                } else {
                    vehicleName.text("Not selected");
                }
                
                // Update package name
                if (hasPackageSelected) {
                    const selectedPackage = $(`${this.constants.SELECTOR_PACKAGE}[data-id="${this.state.selectedPackageId}"]`);
                    packageName.text(selectedPackage.find(".cwb-package-name").text());
                } else {
                    packageName.text("Not selected");
                }
                
                // Update add-ons
                if (this.state.selectedAddons && this.state.selectedAddons.length > 0) {
                    addonList.empty();
                    
                    this.state.selectedAddons.forEach(addonId => {
                        const addonElement = $(`#cwb-addon-list ${this.constants.SELECTOR_ADDON}${addonId}`);
                        if (addonElement.length) {
                            const addonName = addonElement.find(".cwb-service-name").text().trim();
                            addonList.append(`<li>${addonName}</li>`);
                        }
                    });
                    
                    addonContainer.removeClass(this.constants.STATE_HIDDEN);
                } else {
                    addonContainer.addClass(this.constants.STATE_HIDDEN);
                }
            } else {
                selectedServicesContainer.addClass(this.constants.STATE_HIDDEN);
            }
        },

        /**
         * Load vehicles for a specific location
         * @param {number|string} locationId - Location ID
         * @returns {Promise} - Promise that resolves when vehicles are loaded and processed
         */
        async loadVehicles(locationId) {
            this.showVehicleLoading();

            try {
                const response = await this.makeRequest(this.constants.ACTION_GET_VEHICLES, {
                    location_id: locationId,
                });
                
                this.logDebug("Vehicles response received", response);

                // Add the HTML to the DOM
                this.elements.vehicleList.html(response);

                // Check for vehicles using direct DOM inspection after insertion
                const vehicleCount = this.elements.vehicleList.children(this.constants.SELECTOR_VEHICLE).length;
                const hasVehicles = vehicleCount > 0;

                this.logDebug(`Vehicle count: ${vehicleCount}`, hasVehicles);

                // Update UI based on vehicle availability
                this.hideVehicleLoading(hasVehicles);

                if (hasVehicles) {
                    // Force a repaint to ensure the DOM is updated
                    this.elements.vehicleList[0].offsetHeight;

                    // Get the first vehicle and select it
                    const firstVehicle = this.elements.vehicleList.children(this.constants.SELECTOR_VEHICLE).first();

                    if (firstVehicle.length) {
                        this.logDebug("Selecting first vehicle", firstVehicle);

                        // Remove any existing selections
                        this.elements.vehicleList.find(this.constants.SELECTOR_VEHICLE).removeClass(this.constants.STATE_SELECTED);

                        // Select the first vehicle
                        firstVehicle.addClass(this.constants.STATE_SELECTED);

                        // Get and store the vehicle ID
                        const firstVehicleId = firstVehicle.data("id");
                        this.state.selectedVehicleId = firstVehicleId;

                        // Load packages for this vehicle
                        await this.loadPackages(firstVehicleId);
                    } else {
                        this.logDebug("First vehicle not found even though vehicles exist");
                    }
                } else {
                    this.elements.packageList.empty();
                    this.elements.addonList.empty();
                    this.elements.serviceListContainer.addClass(this.constants.STATE_DISABLE);
                }
            } catch (error) {
                this.showError("Failed to load vehicles. Please try again.");
                this.logDebug("Vehicle loading error", error);
                this.hideVehicleLoading(false);
            }
        },

        /**
         * Load packages for a specific vehicle type
         * @param {number|string} vehicleTypeId - Vehicle type ID
         * @returns {Promise} - Promise that resolves when packages are loaded and processed
         */
        async loadPackages(vehicleTypeId) {
            this.showPackageLoading();

            try {
                const response = await this.makeRequest(this.constants.ACTION_GET_PACKAGES, {
                    vehicle_type_id: vehicleTypeId,
                });
                
                this.logDebug("Packages loaded", response);
                
                // Add the HTML to the DOM
                this.elements.packageList.html(response);
                
                // Check for packages using direct DOM inspection
                const packageCount = this.elements.packageList.children(this.constants.SELECTOR_PACKAGE).length;
                const hasPackages = packageCount > 0;
                
                this.logDebug(`Package count: ${packageCount}`, hasPackages);
                
                // Update UI based on package availability
                this.hidePackageLoading(hasPackages);
                
                if (hasPackages) {
                    // Force a repaint to ensure the DOM is updated
                    this.elements.packageList[0].offsetHeight;
                    
                    // Get the first package and select it
                    const firstPackage = this.elements.packageList.children(this.constants.SELECTOR_PACKAGE).first();
                    
                    if (firstPackage.length) {
                        this.logDebug("Selecting first package", firstPackage);
                        
                        // Remove any existing selections
                        this.elements.packageList.find(this.constants.SELECTOR_PACKAGE).removeClass(this.constants.STATE_SELECTED);
                        
                        // Select the first package
                        firstPackage.addClass(this.constants.STATE_SELECTED);
                        
                        // Get package details
                        const firstPackageId = firstPackage.data("id");
                        const packageDuration = parseInt(firstPackage.data("duration"), 10);
                        const packagePrice = parseFloat(firstPackage.data("price"));
                        
                        // Store package data in state
                        this.state.selectedPackageId = firstPackageId;
                        this.state.selectedDuration = packageDuration;
                        this.state.selectedPrice = packagePrice;
                        this.state.selectedPackageDuration = packageDuration;
                        
                        // Update booking summary
                        this.updateBookingSummary();
                        
                        // Load addons for this package
                        await this.loadAddons(firstPackageId);
                        await this.fetchAvailableSlots(this.state.currentDate);
                    } else {
                        this.logDebug("First package not found even though packages exist");
                    }
                } else {
                    // If no packages, clear any addon selections too
                    this.elements.addonList.empty();
                    this.elements.serviceListContainer.addClass(this.constants.STATE_DISABLE);
                }
            } catch (error) {
                this.showError("Failed to load packages. Please try again.");
                this.logDebug("Package loading error", error);
                this.hidePackageLoading(false);
            }
        },

        /**
         * Load add-ons for a specific package
         * @param {number|string} packageId - Package ID
         * @returns {Promise} - Promise that resolves when add-ons are loaded
         */
        async loadAddons(packageId) {
            this.showAddonLoading();

            try {
                const response = await this.makeRequest(this.constants.ACTION_GET_ADDONS, {
                    package_id: packageId,
                });
                
                this.logDebug("Add-ons loaded", response);
                
                // Add the HTML to the DOM
                this.elements.addonList.html(response);
                
                // Better add-on detection with more detailed logging
                const responseText = response.trim();
                this.logDebug("Raw add-ons response", responseText);
                
                // First check if the response is empty or contains only whitespace
                if (responseText === "") {
                    this.logDebug("Empty add-ons response");
                    this.hideAddonLoading(false);
                    return;
                }
                
                // Force browser to process the newly added content
                this.elements.addonList[0].offsetHeight;
                
                // Try multiple selector patterns to find add-ons
                let addonElements = this.elements.addonList.children('li');
                
                // If nothing found with the first selector, try a more general one
                if (addonElements.length === 0) {
                    addonElements = this.elements.addonList.find('li');
                    this.logDebug("Using find('li') for add-ons", addonElements.length);
                }
                
                const hasAddons = addonElements.length > 0;
                
                this.logDebug(`Addon elements found: ${addonElements.length}`, hasAddons);
                
                // Add additional debug info about the DOM structure
                if (!hasAddons) {
                    this.logDebug("Add-on list HTML structure:", this.elements.addonList.html());
                    this.logDebug("Add-on container:", this.elements.addonList[0]);
                }
                
                // Update UI based on addon availability
                this.hideAddonLoading(hasAddons);
            } catch (error) {
                this.showError("Failed to load add-on services. Please try again.");
                this.logDebug("Add-on loading error", error);
                this.hideAddonLoading(false);
            }
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
         * @returns {Promise} - Promise that resolves when slots are fetched
         */
        async fetchAvailableSlots(date) {
            if (!this.state.selectedPackageDuration) {
                this.logDebug("No package selected");
                this.renderCalendar({}, date);
                return;
            }

            this.showCalendarLoading();

            let totalDuration = this.state.selectedPackageDuration;

            // Add duration from selected add-ons
            this.state.selectedAddons.forEach(addonId => {
                const addonElement = $(`#cwb-addon-list ${this.constants.SELECTOR_ADDON}${addonId}.${this.constants.STATE_SELECTED}`);
                if (addonElement.length) {
                    totalDuration += parseInt(addonElement.data("duration"), 10);
                }
            });

            const formattedDate = date.toISOString().split("T")[0];

            try {
                const response = await this.makeRequest(this.constants.ACTION_GET_AVAILABLE_SLOTS, {
                    date: formattedDate,
                    duration: totalDuration
                });
                
                if (response.success && response.data?.slots) {
                    this.logDebug("Available slots", response.data.slots);
                    this.renderCalendar(response.data.slots, date);
                } else {
                    this.logDebug("No available slots", response);
                    this.renderCalendar({}, date);
                }
                this.hideCalendarLoading();
            } catch (error) {
                this.showError("Failed to fetch available time slots. Please try again.");
                this.logDebug("Slot fetching error", error);
                this.renderCalendar({}, date);
                this.hideCalendarLoading();
            }
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

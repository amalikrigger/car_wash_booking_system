jQuery(document).ready(function ($) {
    let selectedDate = null;
    let selectedTime = null;
    let selectedDuration = 0;
    let selectedPrice = 0.00;
    let selectedAddons = [];
    let selectedPackageDuration = null; // For time slot calculations
    let currentDate = new Date(); // Start with today's date
    let currentSelectedLocationId = null; // Variable to store the currently selected location ID
    let locationFieldsConfigs = cwb_ajax.location_fields_configs; // Get location fields configs from localized data

    function updateBookingFormFields(locationId) {
        const configArray = locationFieldsConfigs[locationId] || [];
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
                console.log(`${enabled ? 'Show' : 'Hide'} Field: ${field}, Visible: ${fieldConfig ? fieldConfig.is_visible : 'not found'}`);
                fieldElement.toggleClass('cwb-state-hidden', !enabled);
            }
        });
    }


    // Update the booking summary display (No changes needed here)
    function updateBookingSummary() {
        console.log("Updating Summary - Price:", selectedPrice, "Duration:", selectedDuration);

        // Update date and time
        $(".cwb-booking-summary-date h5 span:nth-child(2)").text(selectedDate || "?");
        $(".cwb-booking-summary-time h5 span:nth-child(2)").text(selectedTime || "?");

        // Update duration (hours and minutes)
        $(".cwb-booking-summary-duration h5 span:nth-child(1)").text(Math.floor(selectedDuration / 60));
        $(".cwb-booking-summary-duration h5 span:nth-child(3)").text(selectedDuration % 60);

        // Update price correctly
        $(".cwb-booking-summary-price h5 span:nth-child(2)").text(`${selectedPrice.toFixed(2)}`);

        console.log("Final Updated Price:", selectedPrice.toFixed(2));
    }

    $(".cwb-package").removeClass("cwb-state-selected");
    $("#cwb-addon-list").empty();
    $(".cwb-main-list-item-service-list").addClass("cwb-state-disable");

    // Function to load vehicles based on location ID
    function loadVehicles(locationId) {
        $.ajax({
            url: cwb_ajax.ajax_url,
            type: "POST",
            data: {
                action: "cwb_get_vehicles",
                nonce: cwb_ajax.nonce,
                location_id: locationId,
            },
            success: function (response) {
                console.log("Vehicles loaded: ", response);
                const vehicleList = $("#cwb-vehicle-list");
                vehicleList.html(response);

                // Automatically select the first vehicle and load packages
                const firstVehicle = vehicleList.find(".cwb-vehicle").first();
                if (firstVehicle.length) {
                    firstVehicle.addClass("cwb-state-selected");
                    const firstVehicleId = firstVehicle.data("id");
                    loadPackages(firstVehicleId);
                } else {
                    // If no vehicles, reset packages and addons
                    $("#cwb-package-list").empty();
                    $("#cwb-addon-list").empty();
                    $(".cwb-main-list-item-service-list").addClass("cwb-state-disable");
                }
            },
            error: function () {
                alert("An error occurred while fetching vehicles.");
            },
        });
    }

    // Function to load packages based on vehicle type ID
    function loadPackages(vehicleTypeId) {
        $.ajax({
            url: cwb_ajax.ajax_url,
            type: "POST",
            data: {
                action: "cwb_get_packages",
                nonce: cwb_ajax.nonce,
                vehicle_type_id: vehicleTypeId,
            },
            success: function (response) {
                console.log("Packages loaded: ", response);
                const packageList = $("#cwb-package-list");
                packageList.html(response);
            },
            error: function () {
                alert("An error occurred while fetching packages.");
            },
        });
    }

    function loadAddons(packageId) {
        console.log("Fetching add-ons for Package ID:", packageId);
        $.ajax({
            url: cwb_ajax.ajax_url,
            type: "POST",
            data: {
                action: "cwb_get_addons",
                nonce: cwb_ajax.nonce,
                package_id: packageId,
            },
            success: function (response) {
                console.log("Add-ons loaded:", response);
                const addonList = $("#cwb-addon-list");
                addonList.html(response);

                const serviceListContainer = $(".cwb-main-list-item-service-list");
                if (response.trim() === "" || $(response).find("li").length === 0) {
                    serviceListContainer.addClass("cwb-state-disable");
                } else {
                    serviceListContainer.removeClass("cwb-state-disable");
                }
            },
            error: function () {
                alert("An error occurred while fetching add-ons.");
            },
        });
    }

    // Update the calendar by incrementing or decrementing the date
    function updateCalendar(direction) {
        currentDate.setDate(currentDate.getDate() + direction); // Increment/decrement current date
        fetchAvailableSlots(currentDate); // Re-render calendar
    }

    // Function to render the calendar UI
    function renderCalendar(slots, startDate) {
        const calendarHeader = $(".cwb-calendar-header-caption");
        const calendarSubheaderRow = $(".cwb-calendar-subheader");
        const calendarDataRow = $(".cwb-calendar-data");
        console.log("Rendering calendar for:", startDate);
        console.log("Available slots for the week:", slots);

        // Clear previous content
        calendarSubheaderRow.empty();
        calendarDataRow.empty();

        // Render the week (7 days)
        for (let i = 0; i < 7; i++) {
            const dayDate = new Date(startDate.getTime()); // Create a new Date object to avoid mutation
            dayDate.setDate(startDate.getDate() + i); // Increment the date by `i`

            // Use local time for consistency
            const dayKey = dayDate.toLocaleDateString("en-CA"); // Format as YYYY-MM-DD
            const dayName = dayDate.toLocaleDateString("en-US", {weekday: "short"}); // Localized weekday
            const dayNumber = dayDate.getDate(); // Local day number
            console.log("Day Key:", dayKey);
            console.log("Day Name:", dayName);
            console.log("Day Number:", dayNumber);

            const slotData = slots[dayKey] || []; // Get slots for the current day
            console.log("Slots for the day:", slotData);

            // Add day header
            calendarSubheaderRow.append(`
            <th data-full-date="${dayDate.toLocaleDateString("en-CA")}">
                <div class="cwb-clear-fix">
                    <span class="cwb-calendar-subheader-day-number">${dayNumber}</span>
                    <span class="cwb-calendar-subheader-day-name">${dayName}</span>
                </div>
            </th>
        `);

            // Add time slots or "Not available"
            let dataCell = `<td><div>`;
            if (slotData.length > 0) {
                dataCell += `<ul class="cwb-list-reset">`;
                slotData.forEach((slot) => {
                    dataCell += `<li><a href="#" data-time="${slot}">${slot}</a></li>`;
                });
                dataCell += `</ul>`;
            } else {
                dataCell += `Not available`;
            }
            dataCell += `</div></td>`;
            calendarDataRow.append(dataCell);
        }

        // Update calendar header with the date range
        const startMonth = startDate.toLocaleDateString("en-US", {month: "long", year: "numeric"});
        const endDate = new Date(startDate.getTime());
        endDate.setDate(startDate.getDate() + 6);
        const endMonth = endDate.toLocaleDateString("en-US", {month: "long", year: "numeric"});

        calendarHeader.html(
            startMonth === endMonth
                ? `<span>${startMonth}</span>`
                : `<span>${startMonth}</span> / <span>${endMonth}</span>`
        );
    }

    // Fetch available slots for a specific date
    function fetchAvailableSlots(date) {
        if (!selectedPackageDuration) {
            console.error("No package selected.");
            renderCalendar([], date); // Render "Not available"
            return;
        }

        let addonDurations = [];
        selectedAddons.forEach(addonId => {
            const addonElement = $(`#cwb-addon-list .cwb-service-id-${addonId}.cwb-state-selected`);
            if (addonElement.length) {
                addonDurations.push(parseInt(addonElement.data("duration"), 10));
            }
        });

        // Calculate total duration here on the frontend
        let total_duration = selectedPackageDuration;
        addonDurations.forEach(duration => {
            total_duration += duration;
        });

        const formattedDate = date.toISOString().split("T")[0];
        $.ajax({
            url: cwb_ajax.ajax_url,
            type: "POST",
            data: {
                action: "cwb_get_available_slots",
                nonce: cwb_ajax.nonce,
                date: formattedDate,
                duration: total_duration // Send total duration
            },
            success: function (response) {
                if (response.success && response.data?.slots) {
                    const slotsForWeek = response.data.slots;
                    console.log("Available slots for the week:", slotsForWeek);
                    renderCalendar(slotsForWeek, date);
                } else {
                    console.error("No available slots:", response);
                    renderCalendar([], date);
                }
            },
            error: function () {
                console.error("Failed to fetch available slots.");
                renderCalendar([], date);
            }
        });
    }

    // Arrow click event listeners
    $(".cwb-calendar-header-arrow-left").on("click", function (e) {
        e.preventDefault();
        updateCalendar(-1); // Go back 1 day
    });

    $(".cwb-calendar-header-arrow-right").on("click", function (e) {
        e.preventDefault();
        updateCalendar(1); // Go forward 1 day
    });

    // Initial render on page load
    renderCalendar([], currentDate);

    // On page load, populate vehicles for the default location
    const defaultLocation = $(".cwb-location[data-default='true']");
    if (defaultLocation.length) {
        const defaultLocationId = defaultLocation.data("id");
        loadVehicles(defaultLocationId);
        currentSelectedLocationId = defaultLocationId; // Set initial selected location ID
        updateBookingFormFields(defaultLocationId); // Update form fields for default location
    }

    // Handle location selection
    $(".cwb-location").on("click", function () {
        const locationId = $(this).data("id");
        const isAlreadySelected = $(this).hasClass("cwb-state-selected");

        if (locationId !== currentSelectedLocationId) {
            // Only reset if a different location is selected
            $(".cwb-location").removeClass("cwb-state-selected");
            $(this).addClass("cwb-state-selected");

            // Reset booking flow to default state
            selectedDate = null;
            selectedTime = null;
            selectedDuration = 0;
            selectedPrice = 0.00;
            selectedAddons = [];
            selectedPackageDuration = null;

            $("#cwb-package-list").empty();
            $("#cwb-addon-list").empty();
            $(".cwb-package").removeClass("cwb-state-selected");
            $(".cwb-addon-list li").removeClass("cwb-state-selected");
            $(".cwb-calendar-data li").removeClass("cwb-state-selected");
            $(".cwb-main-list-item-service-list").addClass("cwb-state-disable");

            renderCalendar([], currentDate);
            updateBookingSummary();
            loadVehicles(locationId); // Load vehicles for the new location
            currentSelectedLocationId = locationId; // Update currently selected location ID
            updateBookingFormFields(locationId); // Update form fields for the selected location
        } else if (!isAlreadySelected) {
            // If the clicked location was not already selected but is the same as currentSelectedLocationId (shouldn't happen under normal circumstances but for robustness)
            $(".cwb-location").removeClass("cwb-state-selected");
            $(this).addClass("cwb-state-selected");
        }
        // If the clicked location is already selected, do nothing (no reset)
    });

    // Handle vehicle type selection
    $("#cwb-vehicle-list").on("click", ".cwb-vehicle", function () {
        const vehicleTypeId = $(this).data("id");
        $(".cwb-vehicle").removeClass("cwb-state-selected");
        $(this).addClass("cwb-state-selected");
        loadPackages(vehicleTypeId);
    });

    // Handle package selection
    $("#cwb-package-list").on("click", ".cwb-package", function () {
        const packageId = $(this).data("id");
        const packageDuration = parseInt($(this).data("duration"), 10);
        const packagePrice = parseFloat($(this).data("price"));  // Ensure numeric value
        const isSelected = $(this).hasClass("cwb-state-selected");

        console.log("Package Selected:", packageId, "Duration:", packageDuration, "Price:", packagePrice);

        if (isNaN(packagePrice)) {
            console.error("Invalid package price. Check data attributes:", $(this).attr("data-price"));
            return; // Stop execution if price is invalid
        }

        if (isSelected) {
            // Deselect package
            $(this).removeClass("cwb-state-selected");

            selectedDate = null;
            selectedTime = null;
            selectedAddons = [];
            selectedDuration = 0;
            selectedPrice = 0.00; // Reset price

            renderCalendar([], currentDate);
            updateBookingSummary();
            $("#cwb-addon-list").empty();
            $(".cwb-main-list-item-service-list").addClass("cwb-state-disable");
        } else {
            // Select package
            $(".cwb-package").removeClass("cwb-state-selected");
            $(this).addClass("cwb-state-selected");

            selectedDate = null;
            selectedTime = null;
            selectedAddons = [];
            selectedDuration = packageDuration;
            selectedPrice = packagePrice; // Properly set price
            selectedPackageDuration = packageDuration;

            console.log("Updated Selected Price:", selectedPrice);

            loadAddons(packageId);
            fetchAvailableSlots(currentDate);
            updateBookingSummary();
        }
    });

    // Add-on selection handler with explicit conversions
    $("#cwb-addon-list").on("click", ".cwb-button", function (e) {
        // Prevent the default link behavior
        e.preventDefault();

        const addonItem = $(this).closest("li"); // Get the parent list item
        const addonId = addonItem.data("id");
        const addonDuration = parseInt(addonItem.data("duration"), 10);
        const addonPrice = parseFloat(addonItem.data("price")); // Ensure it's a number

        console.log("Addon Clicked:", addonId, "Duration:", addonDuration, "Price:", addonPrice);

        if (isNaN(addonPrice)) {
            console.error("Invalid addon price. Check data attributes:", addonItem.attr("data-price"));
            return;
        }

        // Changing add-ons requires a fresh time selection
        selectedDate = null;
        selectedTime = null;

        // Toggle the add-on selection and update totals accordingly
        if (addonItem.hasClass("cwb-state-selected")) {
            // Remove the add-on and subtract its values
            addonItem.removeClass("cwb-state-selected");
            selectedAddons = selectedAddons.filter(id => id !== addonId);
            selectedDuration -= addonDuration;
            selectedPrice -= addonPrice;
        } else {
            // Add the add-on and add its values
            addonItem.addClass("cwb-state-selected");
            selectedAddons.push(addonId);
            selectedDuration += addonDuration;
            selectedPrice += addonPrice;
        }

        updateBookingSummary();
        fetchAvailableSlots(currentDate); // Call fetchAvailableSlots to refresh calendar
    });

    // Time slot selection handler (apply selected state to <li>)
    $(document).on("click", ".cwb-calendar-data li", function (e) {
        e.preventDefault();

        // Remove selected state from all time slots
        $(".cwb-calendar-data li").removeClass("cwb-state-selected");

        // Add selected state to clicked slot
        $(this).addClass("cwb-state-selected");

        // Extract time from clicked slot
        selectedTime = $(this).find("a").data("time"); // Get the time from the <a> inside the <li>

        // Extract date from associated table header
        const selectedIndex = $(this).closest("td").index();
        const selectedDateStr = $(".cwb-calendar-subheader th").eq(selectedIndex).attr("data-full-date");

        console.log("✅ Selected Date Str:", selectedDateStr);

        if (selectedDateStr) {
            const parts = selectedDateStr.split("-"); // parts[0] = "2025", parts[1] = "02", parts[2] = "10"
            const dateObj = new Date(parts[0], parts[1] - 1, parts[2]); // local date (month is 0-indexed)
            selectedDate = dateObj.toLocaleDateString("en-US", {
                weekday: "long",
                day: "numeric",
                month: "long",
                year: "numeric"
            });
        }

        console.log("✅ Selected Date:", selectedDate);
        console.log("✅ Selected Time:", selectedTime);

        updateBookingSummary();
    });

    // Handle checkbox toggle for custom checkboxes
    $(".cwb-form-checkbox").on("click", function () {
        const checkbox = $(this).next("input[type='checkbox']");
        const isChecked = checkbox.prop("checked");

        // Toggle the checkbox state
        checkbox.prop("checked", !isChecked);

        // Add or remove the selected state
        $(this).toggleClass("cwb-state-selected", !isChecked);
    });

    // Ensure clicking the label or link does not interfere with the checkbox toggle
    $(".cwb-agreement div").on("click", function (e) {
        e.stopPropagation();
    });
});

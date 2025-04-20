<li class="cwb-main-list-item cwb-main-list-item-vehicle-list cwb-clear-fix">
    <div class="cwb-main-list-item-section-header cwb-clear-fix">
            <span class="cwb-main-list-item-section-header-step" aria-hidden="true">
            <span>2</span>
            <span>/6</span>
            </span>
        <h4 class="cwb-main-list-item-section-header-header" id="vehicle-selection-title">
            <span>Vehicle type</span></h4>
        <h5 class="cwb-main-list-item-section-header-subheader">
            <span>Select vehicle type below.</span>
        </h5>
    </div>
    <div class="cwb-main-list-item-section-content cwb-clear-fix">
        <div class="cwb-vehicle-container" role="region" aria-labelledby="vehicle-selection-title">
            <!-- Loading indicator -->
            <div class="cwb-loading-indicator cwb-state-hidden">
                <div class="cwb-loading-spinner">
                    <i class="fa-solid fa-spinner fa-spin"></i> Loading vehicles...
                </div>
            </div>
            
            <!-- Empty state message -->
            <div class="cwb-empty-state cwb-state-hidden">
                <p>No vehicles are available for this location. Please select a different location.</p>
            </div>
            
            <!-- Vehicle list container with improved accessibility -->
            <ul id="cwb-vehicle-list" class="cwb-vehicle-list cwb-list-reset cwb-clear-fix" role="listbox" aria-label="Available vehicle types">
                <!-- Vehicles will be dynamically populated here -->
                <!-- Each vehicle should have a class="cwb-vehicle-name" element containing the name -->
            </ul>
            
            <!-- Selection help message -->
            <div class="cwb-selection-help cwb-state-hidden">
                <p><i class="fa-solid fa-circle-info"></i> Click on a vehicle type to select it</p>
            </div>
        </div>
    </div>
</li>

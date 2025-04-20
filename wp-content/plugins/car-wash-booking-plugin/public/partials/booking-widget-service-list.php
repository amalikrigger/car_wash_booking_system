<li class="cwb-main-list-item cwb-main-list-item-service-list cwb-clear-fix cwb-state-disable">
    <div class="cwb-main-list-item-section-header cwb-clear-fix">
        <span class="cwb-main-list-item-section-header-step" aria-hidden="true">
            <span>4</span>
            <span>/6</span>
        </span>
        <h4 class="cwb-main-list-item-section-header-header" id="addon-selection-title">
            <span>Add-on options</span>
        </h4>
        <h5 class="cwb-main-list-item-section-header-subheader">
            <span>Add services to your package.</span>
        </h5>
    </div>
    <div class="cwb-main-list-item-section-content cwb-clear-fix">
        <div class="cwb-addon-container" role="region" aria-labelledby="addon-selection-title">
            <!-- Loading indicator -->
            <div class="cwb-loading-indicator cwb-state-hidden">
                <div class="cwb-loading-spinner">
                    <i class="fa-solid fa-spinner fa-spin"></i> Loading add-on services...
                </div>
            </div>
            
            <!-- Empty state message -->
            <div class="cwb-empty-state cwb-state-hidden">
                <p>No add-on services are available for this package.</p>
            </div>
            
            <!-- Add-on service list container with improved accessibility -->
            <ul id="cwb-addon-list" class="cwb-service-list cwb-list-reset cwb-clear-fix" role="group" aria-label="Available add-on services">
                <!-- Add-on services will be dynamically loaded here -->
            </ul>
            
            <!-- Selection help message -->
            <div class="cwb-selection-help cwb-state-hidden">
                <p><i class="fa-solid fa-circle-info"></i> Click on "Add" to select an add-on service</p>
            </div>
        </div>
    </div>
</li>

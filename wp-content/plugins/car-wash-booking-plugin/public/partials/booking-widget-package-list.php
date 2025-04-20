<li class="cwb-main-list-item cwb-main-list-item-package-list cwb-clear-fix">
    <div class="cwb-main-list-item-section-header cwb-clear-fix">
        <span class="cwb-main-list-item-section-header-step" aria-hidden="true">
            <span>3</span>
            <span>/6</span>
        </span>
        <h4 class="cwb-main-list-item-section-header-header" id="package-selection-title">
            <span>Wash packages</span>
        </h4>
        <h5 class="cwb-main-list-item-section-header-subheader">
            <span>Which wash is best for your vehicle?</span>
        </h5>
    </div>
    <div class="cwb-main-list-item-section-content cwb-clear-fix">
        <div class="cwb-package-container" role="region" aria-labelledby="package-selection-title">
            <!-- Loading indicator -->
            <div class="cwb-loading-indicator cwb-state-hidden">
                <div class="cwb-loading-spinner">
                    <i class="fa-solid fa-spinner fa-spin"></i> Loading packages...
                </div>
            </div>
            
            <!-- Empty state message -->
            <div class="cwb-empty-state cwb-state-hidden">
                <p>No packages are available for this vehicle type. Please select a different vehicle.</p>
            </div>
            
            <!-- Package list container with improved accessibility -->
            <ul id="cwb-package-list" class="cwb-package-list cwb-list-reset cwb-clear-fix" role="listbox" aria-label="Available packages">
                <!-- Packages will be dynamically loaded here -->
            </ul>
            
            <!-- Selection help message -->
            <div class="cwb-selection-help cwb-state-hidden">
                <p><i class="fa-solid fa-circle-info"></i> Click on a package to select it</p>
            </div>
        </div>
    </div>
</li>

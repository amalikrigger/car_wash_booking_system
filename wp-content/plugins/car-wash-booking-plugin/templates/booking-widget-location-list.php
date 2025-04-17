<li class="cwb-main-list-item cwb-main-list-item-location-list cwb-clear-fix">
    <div class="cwb-main-list-item-section-header cwb-clear-fix">
            <span class="cwb-main-list-item-section-header-step">
            <span>1</span>
            <span>/6</span>
            </span>
        <h4 class="cwb-main-list-item-section-header-header">
            <span>Select location</span></h4>
        <h5 class="cwb-main-list-item-section-header-subheader">
            <span>Select location below.</span>
        </h5>
    </div>
    <div class="cwb-main-list-item-section-content cwb-clear-fix">
        <ul class="cwb-location-list cwb-list-reset cwb-clear-fix">
            <?php
            // Location rendering logic will be moved to a partial
            $locations = $view_data['locations']; // Access locations from view_data
            $first = true; // Flag to track the first location
            foreach ($locations as $location) {
                $selected = $first ? 'cwb-state-selected' : '';
                $default = $first ? 'data-default="true"' : ''; // Add data-default attribute for the first location
                echo "<li class='cwb-location {$selected}' data-id='" . esc_attr($location['id']) . "' {$default}>
                        <div>
                            <div>" . esc_html($location['name']) . "</div>
                        </div>
                      </li>";
                $first = false;
            }
            ?>
        </ul>
    </div>
</li>
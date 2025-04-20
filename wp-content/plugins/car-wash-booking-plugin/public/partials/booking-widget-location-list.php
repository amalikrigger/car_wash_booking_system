<li class="cwb-main-list-item cwb-main-list-item-location-list cwb-clear-fix">
    <div class="cwb-main-list-item-section-header cwb-clear-fix">
        <span class="cwb-main-list-item-section-header-step" aria-hidden="true">
            <span>1</span>
            <span>/6</span>
        </span>
        <h4 class="cwb-main-list-item-section-header-header" id="location-selection-title">
            <span>Select location</span>
        </h4>
        <h5 class="cwb-main-list-item-section-header-subheader">
            <span>Select location below.</span>
        </h5>
    </div>
    <div class="cwb-main-list-item-section-content cwb-clear-fix">
        <div class="cwb-location-container" role="region" aria-labelledby="location-selection-title">
            <!-- Loading indicator -->
            <div class="cwb-loading-indicator cwb-state-hidden">
                <div class="cwb-loading-spinner">
                    <i class="fa-solid fa-spinner fa-spin"></i> Loading locations...
                </div>
            </div>

            <!-- Empty state message -->
            <div class="cwb-empty-state cwb-state-hidden">
                <p>No locations are available. Please contact us for assistance.</p>
            </div>

            <!-- Location list -->
            <ul class="cwb-location-list cwb-list-reset cwb-clear-fix" role="listbox" aria-label="Available service locations">
                <?php
                $locations = $view_data['locations'] ?? [];

                if (empty($locations)) {
                    echo '<script>document.querySelector(".cwb-empty-state").classList.remove("cwb-state-hidden");</script>';
                } else {
                    $first = true;
                    foreach ($locations as $location) {
                        $selected = $first ? 'cwb-state-selected' : '';
                        $default = $first ? 'data-default="true"' : '';
                        $selected_attr = $first ? 'aria-selected="true"' : 'aria-selected="false"';

                        echo "<li class='cwb-location {$selected}'
                                 data-id='" . esc_attr($location['id']) . "'
                                 {$default}
                                 role='option'
                                 {$selected_attr}
                                 tabindex='" . ($first ? '0' : '-1') . "'>
                                <div>
                                    <div>" . esc_html($location['name']) . "</div>";

                        echo "</div>
                             </li>";
                        $first = false;
                    }
                }
                ?>
            </ul>

            <?php if (count($locations) > 1): ?>
            <div class="cwb-selection-help">
                <p><i class="fa-solid fa-circle-info"></i> Click on a location to select it</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</li>
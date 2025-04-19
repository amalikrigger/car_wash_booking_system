<?php

function cwb_format_duration($duration) {
    $hours = floor($duration / 60);
    $minutes = $duration % 60;

    if ($hours > 0 && $minutes > 0) {
        return "{$hours}h {$minutes}min";
    } elseif ($hours > 0) {
        return "{$hours}h";
    } else {
        return "{$minutes}min";
    }
}


<?php

use App\Models\SchoolYear;

if (! function_exists('activeSchoolYear')) {
    function activeSchoolYear()
    {
        return SchoolYear::where('is_active', true)->first();
    }
}

<?php

/**
 * Date range formatting and seasonal date placeholder detection.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Compose date range formatting and seasonal date placeholder detection in PWE_System_Functions.
 *
 * @internal Composed by core/class-pwe-system-functions.php.
 */
trait PWE_System_Functions_Dates {
    /**
     * Format a date range and optionally include the start and end times.
     */
    public static function transform_dates($start_date, $end_date, $include_hours = true) {
        $format = $include_hours ? "Y/m/d H:i" : "Y/m/d";

        // Convert date strings to DateTime objects.
        $start_date_obj = DateTime::createFromFormat($format, $start_date);
        $end_date_obj = DateTime::createFromFormat($format, $end_date);

        // Check if the conversion was correct.
        if ($start_date_obj && $end_date_obj) {
            // Get the day, month and year from DateTime objects.
            $start_day = $start_date_obj->format("d");
            $end_day = $end_date_obj->format("d");
            $start_month = $start_date_obj->format("m");
            $end_month = $end_date_obj->format("m");
            $year = $start_date_obj->format("Y");

            // Check if months are the same.
            if ($start_month === $end_month) {
                $formatted_date = "{$start_day}-{$end_day}|{$start_month}|{$year}";
            } else {
                $formatted_date = "{$start_day}|{$start_month}-{$end_day}|{$end_month}|{$year}";
            }

            return $formatted_date;
        } else {
            return "Invalid dates";
        }
    }

    /**
     * Check whether the displayed fair date contains a seasonal placeholder.
     */
    public static function isTradeDateExist() {

        $seasons = ["nowa data", "wiosna", "lato", "jesień", "zima", "new date", "spring", "summer", "autumn", "winter"];
        $trade_date_lower = strtolower(do_shortcode('[trade_fair_date]'));

        // Check the displayed date against the seasonal placeholders.
        foreach ($seasons as $season) {
            if (strpos($trade_date_lower, strtolower($season)) !== false) {
                return true;
            }
        }
        return false;
    }
}

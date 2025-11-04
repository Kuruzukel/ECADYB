<?php

/**
 * Convert MongoDB UTCDateTime to Philippine Time (Asia/Manila, UTC+8)
 * 
 * @param MongoDB\BSON\UTCDateTime|null $mongoDate The MongoDB date object
 * @param string $format The desired output format (default: 'Y-m-d h:i:s A')
 * @return string|null Formatted date in Philippine time or null if input is invalid
 */
function convertToPhilippineTime($mongoDate, $format = 'Y-m-d h:i:s A')
{
    if ($mongoDate instanceof MongoDB\BSON\UTCDateTime) {
        try {
            $timestamp = $mongoDate->toDateTime();
            $timestamp->setTimezone(new DateTimeZone('Asia/Manila'));
            return $timestamp->format($format);
        } catch (Exception $e) {
            error_log("Error converting to Philippine time: " . $e->getMessage());
            return null;
        }
    }
    return null;
}

/**
 * Convert MongoDB UTCDateTime to Philippine Time ISO format
 * 
 * @param MongoDB\BSON\UTCDateTime|null $mongoDate The MongoDB date object
 * @return string|null ISO formatted date in Philippine time or null if input is invalid
 */
function convertToPhilippineTimeISO($mongoDate)
{
    return convertToPhilippineTime($mongoDate, 'c');
}

/**
 * Convert MongoDB UTCDateTime to Philippine Time with custom date-time format
 * Format: 2025-11-04T09:20:42 PM
 * 
 * @param MongoDB\BSON\UTCDateTime|null $mongoDate The MongoDB date object
 * @return string|null Formatted date-time in Philippine time or null if input is invalid
 */
function convertToPhilippineTimeCustom($mongoDate)
{
    return convertToPhilippineTime($mongoDate, 'Y-m-d\Th:i:s A');
}

<?php

// ==========================================
// app/Helpers/date_helper.php - PROSTY HELPER
// ==========================================

if (!function_exists('format_date_polish')) {
    function format_date_polish(mixed $date): string
    {
        if (empty($date)) {
            return '';
        }
        
        try {
            if (is_string($date)) {
                $timestamp = strtotime($date);
            } else {
                $timestamp = $date;
            }
            
            return $timestamp ? date('d.m.Y', $timestamp) : '';
        } catch (\Throwable $e) {
            return '';
        }
    }
}

if (!function_exists('format_datetime_polish')) {
    function format_datetime_polish(mixed $datetime): string
    {
        if (empty($datetime)) {
            return '';
        }
        
        try {
            if (is_string($datetime)) {
                $timestamp = strtotime($datetime);
            } else {
                $timestamp = $datetime;
            }
            
            return $timestamp ? date('d.m.Y H:i', $timestamp) : '';
        } catch (\Throwable $e) {
            return '';
        }
    }
}
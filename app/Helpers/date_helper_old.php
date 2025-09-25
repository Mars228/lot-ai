<?php

// ==========================================
// app/Helpers/date_helper.php
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

//////////////////
if (!function_exists('parse_polish_date')) {
    /**
     * Parsuje polską datę DD.MM.YYYY do formatu MySQL YYYY-MM-DD
     */
    function parse_polish_date(string $polishDate): string
    {
        $parts = explode('.', $polishDate);
        
        if (count($parts) !== 3) {
            throw new InvalidArgumentException('Nieprawidłowy format daty. Oczekiwany: DD.MM.YYYY');
        }
        
        $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
        $month = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
        $year = $parts[2];
        
        // Walidacja daty
        if (!checkdate((int)$month, (int)$day, (int)$year)) {
            throw new InvalidArgumentException('Nieprawidłowa data');
        }
        
        return "{$year}-{$month}-{$day}";
    }
}

if (!function_exists('get_week_days')) {
    /**
     * Pobiera dni tygodnia dla podanego roku i tygodnia
     */
    function get_week_days(int $year, int $week): array
    {
        $days = [];
        $start = new DateTime();
        $start->setISODate($year, $week);
        
        for ($i = 0; $i < 7; $i++) {
            $days[] = $start->format('Y-m-d');
            $start->add(new DateInterval('P1D'));
        }
        
        return $days;
    }
}

if (!function_exists('get_month_weeks')) {
    /**
     * Pobiera tygodnie dla podanego roku i miesiąca
     */
    function get_month_weeks(int $year, int $month): array
    {
        $weeks = [];
        $start = new DateTime("{$year}-{$month}-01");
        $end = new DateTime($start->format('Y-m-t'));
        
        $startWeek = (int)$start->format('W');
        $endWeek = (int)$end->format('W');
        
        // Obsługa przejścia roku
        if ($endWeek < $startWeek) {
            $endWeek += 52;
        }
        
        for ($week = $startWeek; $week <= $endWeek; $week++) {
            $weeks[] = $week > 52 ? $week - 52 : $week;
        }
        
        return array_unique($weeks);
    }
}
<?php
/**
 * Utility functions for department colors
 */

/**
 * Get the color associated with a department ID
 * 
 * @param int $departmentId The department ID
 * @return string The color hex code
 */
function getDepartmentColor($departmentId) {
    $colors = [
        1 => '#25cad2', // Arts appliqués
        2 => '#d55e00', // Économique & social
        3 => '#ac103d', // Paramédical
        4 => '#0971ce', // Pédagogique
        5 => '#60269e', // Technique
    ];
    
    return isset($colors[$departmentId]) ? $colors[$departmentId] : '#6b7280'; // Default gray if not found
}

/**
 * Get the color associated with a department name
 * 
 * @param string $departmentName The department name
 * @return string The color hex code
 */
function getDepartmentColorByName($departmentName) {
    $departmentColors = [
        'Arts appliqués' => '#25cad2',
        'Économique & social' => '#d55e00',
        'Paramédical' => '#ac103d',
        'Pédagogique' => '#0971ce',
        'Technique' => '#60269e',
    ];
    
    return isset($departmentColors[$departmentName]) ? $departmentColors[$departmentName] : '#6b7280'; // Default gray if not found
}

/**
 * Get the tailwind background color class for a department ID
 * 
 * @param int $departmentId The department ID
 * @return string The tailwind class
 */
function getDepartmentBgClass($departmentId) {
    $classes = [
        1 => 'bg-[#25cad2]', // Arts appliqués
        2 => 'bg-[#d55e00]', // Économique & social
        3 => 'bg-[#ac103d]', // Paramédical
        4 => 'bg-[#0971ce]', // Pédagogique
        5 => 'bg-[#60269e]', // Technique
    ];
    
    return isset($classes[$departmentId]) ? $classes[$departmentId] : 'bg-gray-500'; // Default gray if not found
}

/**
 * Get the tailwind text color class for a department ID
 * 
 * @param int $departmentId The department ID
 * @return string The tailwind class
 */
function getDepartmentTextClass($departmentId) {
    $classes = [
        1 => 'text-[#25cad2]', // Arts appliqués
        2 => 'text-[#d55e00]', // Économique & social
        3 => 'text-[#ac103d]', // Paramédical
        4 => 'text-[#0971ce]', // Pédagogique
        5 => 'text-[#60269e]', // Technique
    ];
    
    return isset($classes[$departmentId]) ? $classes[$departmentId] : 'text-gray-500'; // Default gray if not found
}

/**
 * Get the tailwind border color class for a department ID
 * 
 * @param int $departmentId The department ID
 * @return string The tailwind class
 */
function getDepartmentBorderClass($departmentId) {
    $classes = [
        1 => 'border-[#25cad2]', // Arts appliqués
        2 => 'border-[#d55e00]', // Économique & social
        3 => 'border-[#ac103d]', // Paramédical
        4 => 'border-[#0971ce]', // Pédagogique
        5 => 'border-[#60269e]', // Technique
    ];
    
    return isset($classes[$departmentId]) ? $classes[$departmentId] : 'border-gray-500'; // Default gray if not found
}

/**
 * Get the department name by ID
 * 
 * @param int $departmentId The department ID
 * @return string The department name
 */
function getDepartmentName($departmentId) {
    $departments = [
        1 => 'Arts appliqués',
        2 => 'Économique & social',
        3 => 'Paramédical',
        4 => 'Pédagogique',
        5 => 'Technique',
    ];
    
    return isset($departments[$departmentId]) ? $departments[$departmentId] : 'Inconnu';
}

<?php

// ==================== UPLOAD ====================

if (!function_exists('upload_url')) {
    function upload_url($path, $default = null)
    {
        if (!$path) {
            return $default ? asset($default) : null;
        }

        // 🔥 Nếu đã có 'uploads/' thì không thêm nữa
        if (str_starts_with($path, 'uploads/')) {
            return asset($path);
        }

        return asset('uploads/' . ltrim($path, '/'));
    }
}

// ==================== FILE ====================

if (!function_exists('file_url')) {
    /**
     * Alias for upload_url (for documents, etc.)
     */
    function file_url($path, $default = null)
    {
        return upload_url($path, $default);
    }
}

// ==================== AVATAR ====================

if (!function_exists('avatar_url')) {
    /**
     * Get avatar URL with fallback
     */
    function avatar_url($path, $gender = null)
    {
        if ($path) {
            return upload_url($path);
        }

        // fallback theo giới tính (tuỳ bạn có ảnh hay chưa)
        return null;
    }
}

// ==================== TEXT ====================

if (!function_exists('full_name')) {
    /**
     * Combine last_name + first_name
     */
    function full_name($last, $first)
    {
        return trim($last . ' ' . $first);
    }
}

// ==================== FORMAT ====================

if (!function_exists('format_date')) {
    /**
     * Format date to d/m/Y
     */
    function format_date($date)
    {
        if (!$date) return null;

        return \Carbon\Carbon::parse($date)->format('d/m/Y');
    }
}

if (!function_exists('format_datetime')) {
    /**
     * Format datetime
     */
    function format_datetime($date)
    {
        if (!$date) return null;

        return \Carbon\Carbon::parse($date)->format('d/m/Y H:i');
    }
}

// ==================== STATUS ====================

if (!function_exists('status_badge_class')) {
    /**
     * Get Tailwind class for status
     */
    function status_badge_class($status)
    {
        return $status
            ? 'bg-green-100 text-green-700'
            : 'bg-red-100 text-red-600';
    }
}

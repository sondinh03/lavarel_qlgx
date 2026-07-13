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

// ==================== MEDIA ====================

if (!function_exists('media_url')) {
    /**
     * Resolve a stored media path to a public URL.
     * Supports uploads/ (public/), storage disk paths, and absolute URLs.
     */
    function media_url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'uploads/')) {
            return asset($path);
        }

        return asset('storage/' . $path);
    }
}

if (!function_exists('delete_stored_media')) {
    /**
     * Delete a media file from public/uploads or the public storage disk.
     */
    function delete_stored_media(?string $path): void
    {
        if (!$path) {
            return;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'uploads/')) {
            @unlink(public_path($path));

            return;
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
        }
    }
}

// ==================== AVATAR ====================

if (!function_exists('avatar_url')) {
    /**
     * Get avatar URL with optional gender fallback when no path is set.
     */
    function avatar_url(?string $path, ?string $gender = null): ?string
    {
        if ($path) {
            return media_url($path);
        }

        if ($gender === 'male') {
            return asset('images/default-male-avatar.png');
        }

        if ($gender === 'female') {
            return asset('images/default-female-avatar.png');
        }

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

// ==================== NOTIFICATIONS ====================

if (! function_exists('notify_users')) {
    /**
     * Gửi notification tới một hoặc nhiều User (database channel).
     *
     * @param  \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection|iterable|\App\Models\User|null  $users
     * @param  \Illuminate\Notifications\Notification  $notification
     */
    function notify_users($users, $notification): void
    {
        if ($users === null) {
            return;
        }

        if ($users instanceof \App\Models\User) {
            $users->notify($notification);

            return;
        }

        $collection = collect($users)->filter()->unique('id');

        if ($collection->isEmpty()) {
            return;
        }

        \Illuminate\Support\Facades\Notification::send($collection, $notification);
    }
}

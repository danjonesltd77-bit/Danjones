<?php

namespace App\Domains\Core\Services;

use App\Domains\Core\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SettingService
{
    /**
     * Get a setting value by key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $type = $this->guessType($default);

        $setting = Setting::firstOrCreate(
            ['key' => $key],
            [
                'value' => $this->formatValue($default, $type),
                'type' => $type,
            ]
        );

        return $this->castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value.
     */
    public function set(string $key, mixed $value, ?string $type = null, ?string $description = null): Setting
    {
        if (is_null($type)) {
            $type = $this->guessType($value);
        }

        $formattedValue = $this->formatValue($value, $type);

        $setting = Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $formattedValue,
                'type' => $type,
                'description' => $description,
            ]
        );

        Cache::forget("setting.{$key}");

        return $setting;
    }

    /**
     * Cast value to the specified type.
     */
    protected function castValue(mixed $value, string $type): mixed
    {
        if (is_null($value)) {
            return null;
        }

        return match ($type) {
            'int', 'integer' => (int) $value,
            'float', 'double' => (float) $value,
            'bool', 'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json', 'array' => json_decode($value, true),
            default => (string) $value,
        };
    }

    /**
     * Format value for storage.
     */
    protected function formatValue(mixed $value, string $type): string
    {
        if ($type === 'json' || is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        if ($type === 'bool' || $type === 'boolean') {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }

    /**
     * Guess type from value.
     */
    protected function guessType(mixed $value): string
    {
        if (is_int($value)) {
            return 'int';
        }
        if (is_float($value)) {
            return 'float';
        }
        if (is_bool($value)) {
            return 'bool';
        }
        if (is_array($value) || is_object($value)) {
            return 'json';
        }

        return 'string';
    }
}

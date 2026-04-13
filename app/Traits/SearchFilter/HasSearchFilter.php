<?php

namespace App\Traits\SearchFilter;

use Illuminate\Http\Request;

trait HasSearchFilter {
    /**
     * Validates and returns the search string.
     */
    protected function getSearch(Request $request): ?string
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9\s.@-]+$/'],
        ]);

        return $validated['search'] ?? null;
    }
}
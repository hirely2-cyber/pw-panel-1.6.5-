<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Authorize the current panel user against a named permission.
     * Aborts with 403 if missing.
     */
    protected function authorize_(string $permission): void
    {
        $user = auth('panel')->user();
        if (! $user || ! $user->can($permission)) {
            abort(403, "Missing permission: {$permission}");
        }
    }
}

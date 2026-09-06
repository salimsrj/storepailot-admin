<?php

namespace App\Http\Requests\Admin;

trait AuthorizesAdmin
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }
}

<?php

namespace App\Actions\Jetstream;

use App\Models\Customer;
use App\Models\User;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Delete the given user.
     */
    public function delete(Customer $customer): void
    {
        $customer->deleteProfilePhoto();
        $customer->tokens->each->delete();
        $customer->delete();
    }
}

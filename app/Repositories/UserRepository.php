<?php

namespace App\Repositories;
use App\Models\User;

class UserRepository
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function findById(int $id): User
    {
        return User::findOrFail($id);
    }

    public function update(int $id, array $data): User
    {
        $user = User::findOrFail($id);
        $user->update($data);
        return $user;
    }

    public function delete($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return null;
        }

        return $user->delete();
    }

    public function getAll()
    {
        return User::all();
    }
}
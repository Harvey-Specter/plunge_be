<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UsersController extends Controller
{
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }
    public function update(UserRequest $request)
    {
        $user = $request->user();
       // $attributes = $request->only(['name', 'email', 'introduction']);
        $attributes = $request->only(['name', 'email']);

        // if ($request->avatar_image_id) {
        //     $image = Image::find($request->avatar_image_id);

        //     $attributes['avatar'] = $image->path;
        // }

        $user->update($attributes);

        return (new UserResource($user))->showSensitiveFields();
    }
}
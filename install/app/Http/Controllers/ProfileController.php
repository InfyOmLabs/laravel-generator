<?php

namespace App\Http\Controllers;

use App\Helpers\TimezoneHelper;
use App\Http\Requests\Profile\ProfileRequest;
use App\Http\Requests\Profile\PasswordRequest;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\Hash;

class ProfileController extends AppBaseController
{
    /**
     * Show the form for editing the profile.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('profile.edit')
            ->withTimezones(collect(TimezoneHelper::getListOfTimezones())->map(function ($timezone) {
                return ['id' => $timezone['timezone'], 'name'=>$timezone['name']];
            }));;
    }

    /**
     * Update the profile
     *
     * @param  \App\Http\Requests\ProfileRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProfileRequest $request)
    {
        auth()->user()->update($request->all());

        return back()->withStatus(__('Profile successfully updated.'));
    }

    /**
     * Change the password
     *
     * @param  \App\Http\Requests\PasswordRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function password(PasswordRequest $request)
    {
        auth()->user()->update(['password' => Hash::make($request->get('password'))]);

        return back()->withPasswordStatus(__('Password successfully updated.'));
    }
}

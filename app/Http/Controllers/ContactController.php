<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('legal.contact');
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:254'], // RFC 5321 max email length
            'message' => ['required', 'string', 'max:2000'],
        ]);

        Mail::to(config('textback.contact_email'))->send(
            new ContactMessage($data['name'], $data['email'], $data['message']),
        );

        return back()->with('sent', true);
    }
}

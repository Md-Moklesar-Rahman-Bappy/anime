<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        // Honeypot
        if ($request->filled('website')) {
            return back();
        }

        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'subject' => 'required|string|in:general,bug,feature,content,dmca,partnership,other',
            'message' => 'required|string|min:10|max:2000',
        ]);

        // Send email to your support inbox
        Mail::raw($validated['message'], function ($mail) use ($validated) {
            $mail->to(config('mail.support_address', 'support@example.com'))
                ->subject('[Contact] ' . ucfirst($validated['subject']) . ' — ' . $validated['name'])
                ->replyTo($validated['email'], $validated['name']);
        });

        return back()->with('contact_sent', true);
    }
}

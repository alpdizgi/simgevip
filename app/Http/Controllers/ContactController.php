<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('store.contact');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sender_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'subject' => ['nullable', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:5000'],
        ], [
            'sender_name.required' => 'Lütfen adınızı yazın.',
            'email.required' => 'Lütfen e-posta adresinizi yazın.',
            'email.email' => 'Geçerli bir e-posta girin.',
            'message.required' => 'Lütfen mesajınızı yazın.',
        ]);

        Message::create([
            'sender_name' => $data['sender_name'],
            'email' => $data['email'],
            'subject' => $data['subject'] ?? 'İletişim Formu',
            'message' => $data['message'],
            'is_read' => false,
        ]);

        return back()->with('success', 'Mesajınız alındı. En kısa sürede size dönüş yapacağız.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class CustomerSupportController extends Controller
{
    public function store(Request $request)
    {
        $this->throttle('customer-support-create:' . Auth::guard('customer')->id(), 5, 'Çok fazla destek talebi oluşturuldu. Lütfen biraz sonra tekrar deneyin.');

        $data = $request->validate([
            'subject' => ['required', 'string', 'min:5', 'max:180'],
            'category' => ['required', 'in:order,product,reservation,account,general'],
            'body' => ['required', 'string', 'min:10', 'max:10000'],
        ]);

        $ticket = DB::transaction(function () use ($data) {
            $ticket = Auth::guard('customer')->user()->supportTickets()->create([
                'subject' => trim($data['subject']),
                'category' => $data['category'],
                'status' => 'open',
                'last_reply_at' => now(),
            ]);
            $ticket->messages()->create([
                'author_type' => 'customer',
                'author_id' => Auth::guard('customer')->id(),
                'body' => trim($data['body']),
            ]);
            return $ticket;
        });

        return redirect()->route('customer.support.show', $ticket)->with('success', 'Destek talebiniz oluşturuldu.');
    }

    public function show(SupportTicket $ticket)
    {
        $this->authorizeCustomer($ticket);
        $ticket->load('messages');
        $ticket->update(['customer_read_at' => now()]);
        return view('store.auth.support', ['ticket' => $ticket]);
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $this->authorizeCustomer($ticket);
        $this->throttle('customer-support-reply:' . Auth::guard('customer')->id(), 10, 'Çok fazla yanıt gönderildi. Lütfen biraz sonra tekrar deneyin.');
        $data = $request->validate(['body' => ['required', 'string', 'min:2', 'max:10000']]);
        DB::transaction(function () use ($ticket, $data) {
            $ticket->messages()->create([
                'author_type' => 'customer',
                'author_id' => Auth::guard('customer')->id(),
                'body' => trim($data['body']),
            ]);
            $ticket->update(['status' => 'open', 'last_reply_at' => now(), 'admin_read_at' => null]);
        });
        return redirect()->route('customer.support.show', $ticket)->with('success', 'Yanıtınız gönderildi.');
    }

    private function authorizeCustomer(SupportTicket $ticket): void
    {
        abort_unless((int) $ticket->customer_id === (int) Auth::guard('customer')->id(), 404);
    }

    private function throttle(string $key, int $max, string $message): void
    {
        if (RateLimiter::tooManyAttempts($key, $max)) {
            throw ValidationException::withMessages(['body' => $message]);
        }

        RateLimiter::hit($key, 600);
    }
}

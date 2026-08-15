<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function create(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        $bookingId = $request->input('booking_id');

        if (!$bookingId) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No booking found.']);
            return redirect()->route('tourist.bookings.index');
        }

        $booking = Booking::with([
            'tourDate.package:id,package_name,price,description,image',
            'pickupLocation:id,name,address',
            'payments'
        ])->findOrFail($bookingId);

        // Ensure the booking belongs to the authenticated user
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if booking already has a PAID payment
        $paidPayment = $booking->payments()->where('payment_status', 'paid')->first();
        if ($paidPayment) {
            Inertia::flash('toast', ['type' => 'info', 'message' => 'This booking already has a completed payment.']);
            return redirect()->route('tourist.bookings.show', $bookingId);
        }

        // Check if booking has a pending payment
        $pendingPayment = $booking->payments()->where('payment_status', 'pending')->first();

        return Inertia::render('tourist/payments/Create', [
            'booking' => $booking,
            'existingPayment' => $pendingPayment,
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'in:gcash,bank_transfer,cash,credit_card'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'proof_of_payment' => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        $booking = Booking::findOrFail($request->booking_id);

        // Ensure the booking belongs to the authenticated user
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if booking already has a PAID payment
        $paidPayment = $booking->payments()->where('payment_status', 'paid')->first();
        if ($paidPayment) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'This booking already has a completed payment.']);
            return redirect()->route('tourist.bookings.show', $request->booking_id);
        }

        // Handle proof of payment upload
        $proofPath = null;
        if ($request->hasFile('proof_of_payment')) {
            $proofPath = $request->file('proof_of_payment')->store('payments/proofs', 'public');
        }

        // Check if there's a pending payment to update
        $pendingPayment = $booking->payments()->where('payment_status', 'pending')->first();

        if ($pendingPayment) {
            // Update existing pending payment
            $pendingPayment->update([
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'transaction_reference' => $request->transaction_reference,
                'proof_of_payment' => $proofPath ?? $pendingPayment->proof_of_payment,
                'notes' => $request->notes,
            ]);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Payment updated successfully!']);
        } else {
            // Create new payment
            Payment::create([
                'booking_id' => $request->booking_id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'transaction_reference' => $request->transaction_reference,
                'proof_of_payment' => $proofPath,
                'notes' => $request->notes,
                'paid_at' => null,
            ]);

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Payment submitted successfully!']);
        }

        // Redirect to bookings index after successful payment
        return redirect()->route('tourist.bookings.index');
    }

    public function show(Payment $payment): Response
    {
        $payment->load('booking.tourDate.package');

        // Ensure the payment belongs to the authenticated user
        if ($payment->booking->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('tourist/payments/Show', [
            'payment' => $payment,
        ]);
    }
}

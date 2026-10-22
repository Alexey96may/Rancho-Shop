<?php

namespace App\Jobs;

use App\Mail\ProductAvailableMail;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendProductAvailableToSubscribersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $productId,
    ) {}

    public function handle(): void
    {
        $product = Product::with('subscribers')->find($this->productId);

        if (!$product) {
            return;
        }

        foreach ($product->subscribers as $user) {
            if (!$user->email) {
                continue;
            }

            $lastNotified = $user->pivot->notified_at
                ? Carbon::parse($user->pivot->notified_at)
                : null;

            if ($lastNotified && $lastNotified->gt(now()->subDay())) {
                continue;
            }

            try {
                Mail::to($user->email)->send(new ProductAvailableMail($product, $user));
            } catch (Throwable $e) {
                Log::error('SendProductAvailableToSubscribersJob: failed', [
                    'product_id' => $this->productId,
                    'user_id' => $user->id,
                    'message' => $e->getMessage(),
                ]);
            }

            $product->subscribers()->updateExistingPivot($user->id, [
                'notified_at' => now(),
            ]);
        }
    }
}

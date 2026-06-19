<?php

namespace App\Http\Controllers;

use App\Models\WithdrawalMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalMethodController extends Controller
{
    /**
     * Store a new withdrawal method for the authenticated user.
     */
    public function store(Request $request)
    {
        $validated = $this->validateMethod($request);

        DB::transaction(function () use ($validated, $request) {
            $makeDefault = $request->boolean('is_default')
                || Auth::user()->withdrawalMethods()->count() === 0;

            if ($makeDefault) {
                Auth::user()->withdrawalMethods()->update(['is_default' => false]);
            }

            $validated['is_default'] = $makeDefault;
            Auth::user()->withdrawalMethods()->create($validated);
        });

        return redirect()->route('profile.edit')
            ->with('status', 'withdrawal-method-saved')
            ->with('success', __('messages.withdrawal_method_saved') ?? 'Withdrawal method saved.');
    }

    /**
     * Update an existing withdrawal method.
     */
    public function update(Request $request, WithdrawalMethod $withdrawalMethod)
    {
        $this->authorizeOwner($withdrawalMethod);

        $validated = $this->validateMethod($request);

        DB::transaction(function () use ($validated, $request, $withdrawalMethod) {
            if ($request->boolean('is_default')) {
                Auth::user()->withdrawalMethods()->update(['is_default' => false]);
                $validated['is_default'] = true;
            }

            $withdrawalMethod->update($validated);
        });

        return redirect()->route('profile.edit')
            ->with('status', 'withdrawal-method-saved')
            ->with('success', __('messages.withdrawal_method_saved') ?? 'Withdrawal method updated.');
    }

    /**
     * Delete a withdrawal method.
     */
    public function destroy(WithdrawalMethod $withdrawalMethod)
    {
        $this->authorizeOwner($withdrawalMethod);

        $wasDefault = $withdrawalMethod->is_default;

        DB::transaction(function () use ($withdrawalMethod, $wasDefault) {
            $withdrawalMethod->delete();

            // Promote another method to default if we just removed the default one.
            if ($wasDefault) {
                $next = Auth::user()->withdrawalMethods()->orderByDesc('id')->first();
                if ($next) {
                    $next->update(['is_default' => true]);
                }
            }
        });

        return redirect()->route('profile.edit')
            ->with('status', 'withdrawal-method-deleted')
            ->with('success', __('messages.withdrawal_method_deleted') ?? 'Withdrawal method removed.');
    }

    /**
     * Mark a method as the user's default.
     */
    public function setDefault(WithdrawalMethod $withdrawalMethod)
    {
        $this->authorizeOwner($withdrawalMethod);

        DB::transaction(function () use ($withdrawalMethod) {
            Auth::user()->withdrawalMethods()->update(['is_default' => false]);
            $withdrawalMethod->update(['is_default' => true]);
        });

        return redirect()->route('profile.edit')
            ->with('status', 'withdrawal-method-default')
            ->with('success', __('messages.withdrawal_method_default_set') ?? 'Default withdrawal method updated.');
    }

    /**
     * Ensure the method belongs to the current user.
     */
    private function authorizeOwner(WithdrawalMethod $withdrawalMethod): void
    {
        abort_unless($withdrawalMethod->user_id === Auth::id(), 403);
    }

    /**
     * Validate the request based on the chosen method type and return only
     * the relevant fields.
     */
    private function validateMethod(Request $request): array
    {
        $base = $request->validate([
            'type'  => 'required|in:bank_transfer,paypal,mobile_wallet',
            'label' => 'nullable|string|max:255',
        ]);

        $type = $base['type'];

        $typeRules = match ($type) {
            'bank_transfer' => [
                'bank_name'           => 'required|string|max:255',
                'account_holder_name' => 'required|string|max:255',
                'account_number'      => 'nullable|string|max:50',
                'iban'                => 'required|string|min:15|max:34',
                'swift_code'          => 'nullable|string|max:11',
            ],
            'paypal' => [
                'paypal_email' => 'required|email|max:255',
            ],
            'mobile_wallet' => [
                'wallet_provider'      => 'required|string|max:255',
                'wallet_mobile_number' => 'required|string|max:30',
                'wallet_holder_name'   => 'required|string|max:255',
            ],
        };

        $typeData = $request->validate($typeRules);

        return array_merge($base, $typeData);
    }
}

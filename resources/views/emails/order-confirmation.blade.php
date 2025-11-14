<x-mail::message>
# {{ __('Order Confirmation') }} #{{ $order->id }}

{{ __('Hello') }} {{ $order->user->name }},

{{ __('Thank you for your order! We\'re processing it now.') }}

## {{ __('Order Details') }}

**{{ __('Order Number:') }}** #{{ $order->id }}
**{{ __('Order Date:') }}** {{ $order->created_at->format('M d, Y') }}
**{{ __('Total Amount:') }}** {{ setting('site_currency', 'AED') }} {{ number_format($order->total_amount, 2) }}

<x-mail::table>
| {{ __('Product') }} | {{ __('Quantity') }} | {{ __('Price') }} |
|:------------- |:-------------:| --------:|
@foreach($order->items as $item)
| {{ $item->product->name }} | {{ $item->quantity }} | {{ setting('site_currency', 'AED') }} {{ number_format($item->price, 2) }} |
@endforeach
</x-mail::table>

<x-mail::button :url="url('/orders/' . $order->id)">
{{ __('View Order Details') }}
</x-mail::button>

{{ __('We\'ll send you another email when your order ships.') }}

{{ __('Thank you for shopping with us!') }}

{{ __('Best regards,') }}<br>
{{ __('The') }} {{ setting('site_name', config('app.name')) }} {{ __('Team') }}
</x-mail::message>

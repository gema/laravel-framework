{{-- Login by email code (MAGIC_LINK_LOGIN): in place of the theme's form and of the app's own copy.
     Steps: the email, the code sent to it, the name on a first login, and the page of the email's link
     when it's opened in another browser --}}
@php($step ??= 'email')

<h2 class="h2 text-center my-4">{{ trans('backpack::base.login') }}</h2>

@if ($step === 'email')
    <form method="POST" action="{{ route('backpack.auth.login') }}" novalidate onsubmit="this.querySelector('button').disabled = true">
        @csrf
        <div class="input-group">
            <input autofocus tabindex="1" type="email" name="email" value="{{ old('email') }}" placeholder="{{ trans('gemadigital::magic_link.email') }}" aria-label="{{ trans('gemadigital::magic_link.email') }}" autocomplete="email" class="form-control {{ $errors->any() ? 'is-invalid' : '' }}">
            <button tabindex="2" type="submit" class="btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-mail">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" />
                    <path d="M3 7l9 6l9 -6" />
                </svg>
                <span>{{ trans('gemadigital::magic_link.send') }}</span>
            </button>
        </div>
        @if ($errors->any())
            <div class="invalid-feedback d-block">{{ $errors->first() }}</div>
        @endif
    </form>

    @include('gemadigital::admin.social_buttons')
@elseif ($step === 'code')
    <style>
        .magic-link-code input { flex: 1 1 0; min-width: 0; max-width: 3.5rem; height: 3.5rem; font-size: 1.5rem; }
    </style>

    <p class="text-secondary text-center">{{ trans('gemadigital::magic_link.code_sent', ['email' => $email]) }}</p>
    <form method="POST" action="{{ route('magic-link.code') }}" novalidate data-magic-link-code onsubmit="if (this.dataset.sent) return false; this.dataset.sent = 1; this.querySelector('button').disabled = true">
        @csrf
        {{-- The 6 digits in 2 groups, as in the email; also sent as code[] without JavaScript --}}
        <div class="magic-link-code d-flex justify-content-center align-items-center gap-2">
            @foreach (range(1, 6) as $digit)
                @if ($digit === 4)
                    <span class="text-secondary px-1" aria-hidden="true">&middot;</span>
                @endif
                <input type="text" name="code[]" inputmode="numeric" pattern="[0-9]*" autocomplete="{{ $digit === 1 ? 'one-time-code' : 'off' }}" @if ($digit === 1) autofocus @endif aria-label="{{ trans('gemadigital::magic_link.digit', ['n' => $digit]) }}" class="form-control text-center px-0 {{ $errors->any() ? 'border-danger' : '' }}">
            @endforeach
        </div>
        @if ($errors->any())
            <div class="invalid-feedback d-block text-center">{{ $errors->first() }}</div>
        @endif
        <button type="submit" class="btn btn-primary w-100 mt-3">{{ trans('backpack::base.login') }}</button>
    </form>
    <div class="text-center mt-3">
        <a href="{{ route('magic-link.restart') }}">{{ trans('gemadigital::magic_link.restart') }}</a>
    </div>

    <script>
        (() => {
            const form = document.querySelector('[data-magic-link-code]');
            const boxes = [...form.querySelectorAll('input[name="code[]"]')];

            // Spreads the digits from a box on: typed, pasted ("378 612") or filled in by the phone; full, it logs in
            const spread = (index, text) => {
                const digits = [...text.replace(/\D/g, '')].slice(0, boxes.length - index);

                boxes[index].value = '';
                digits.forEach((digit, i) => boxes[index + i].value = digit);

                const empty = boxes.find(box => ! box.value);

                if (empty) {
                    empty.focus();
                } else if (digits.length) {
                    form.requestSubmit ? form.requestSubmit() : form.submit();
                }
            };

            boxes.forEach((box, index) => {
                box.addEventListener('focus', () => box.select());
                box.addEventListener('input', () => spread(index, box.value));
                box.addEventListener('paste', event => {
                    event.preventDefault();
                    spread(index, event.clipboardData.getData('text'));
                });
                box.addEventListener('keydown', event => {
                    if (event.key === 'Backspace' && ! box.value && index > 0) {
                        boxes[index - 1].focus();
                    }
                });
            });
        })();
    </script>
@elseif ($step === 'name')
    <form method="POST" action="{{ route('magic-link.register') }}" novalidate onsubmit="this.querySelector('button').disabled = true">
        @csrf
        <p class="text-secondary text-center">{{ trans('gemadigital::magic_link.register', ['email' => $email]) }}</p>
        <div class="mb-3">
            <label class="form-label" for="name">{{ trans('backpack::base.name') }}</label>
            <input autofocus tabindex="1" type="text" name="name" id="name" value="{{ old('name') }}" autocomplete="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button tabindex="2" type="submit" class="btn btn-primary w-100">{{ trans('gemadigital::magic_link.create_account') }}</button>
    </form>
@else
    {{-- The link opened in another browser: logs in only on the click, so mail scanners that open links don't use them up --}}
    <form method="POST" action="{{ route('magic-link', $token) }}" onsubmit="this.querySelector('button').disabled = true">
        @csrf
        <p class="text-secondary text-center">{{ trans('gemadigital::magic_link.continue', ['email' => $email]) }}</p>
        <button autofocus tabindex="1" type="submit" class="btn btn-primary w-100">{{ trans('backpack::base.login') }}</button>
    </form>
@endif

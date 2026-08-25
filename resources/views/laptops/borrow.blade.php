<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Borrow {{ $laptop->name }} | MyHEP</title>
    @include('partials.brand_icons')

</head>
<body>
    <main class="borrow-card">
        <header class="borrow-head">
            <div class="borrow-brand"><img src="{{ asset('images/myhep-mark.png') }}?v=10" alt="">MyHEP</div>
            <h1>{{ $laptop->name }}</h1>
            <p>{{ $laptop->asset_code }} · JHEP laptop borrowing</p>
        </header>
        <section class="borrow-body">
            <div class="borrow-status">{{ __('Enter your NRIC to borrow this laptop or return it if it is already assigned to you.') }}</div>
            <form id="borrowForm">
                <div class="borrow-field"><label for="nric">{{ __('NRIC number') }}</label><input id="nric" name="nric" inputmode="numeric" autocomplete="off" maxlength="20" placeholder="e.g. 900101011234" required></div>
                <p class="borrow-help" id="borrowHelp">{{ __('Enter your NRIC to confirm that you are eligible to borrow.') }}</p>
                <button class="borrow-button" id="borrowButton" type="submit" disabled>{{ __('Take This Laptop') }}</button>
            </form>
            <div class="borrow-result" id="borrowResult" hidden></div>
            <p class="borrow-note">{{ __('Only staff included in the JHEP laptop borrower registry can take a laptop. Your NRIC is used only to verify this loan.') }}</p>
        </section>
    </main>
    <script>
        const input = document.getElementById('nric');
        const help = document.getElementById('borrowHelp');
        const button = document.getElementById('borrowButton');
        const result = document.getElementById('borrowResult');
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        let timer;
        let eligible = false;

        const setHelp = (message, error = false) => { help.textContent = message; help.classList.toggle('error', error); };
        const body = () => JSON.stringify({ nric: input.value });

        input.addEventListener('input', () => {
            input.value = input.value.replace(/[^0-9]/g, '');
            eligible = false;
            button.disabled = true;
            clearTimeout(timer);
            if (input.value.length < 8) { setHelp('Enter your NRIC to confirm that you are eligible to borrow.'); return; }
            setHelp('Checking staff eligibility...');
            timer = setTimeout(async () => {
                try {
                    const response = await fetch(@json(route('laptops.borrow.staff-check', $token)), { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, body: body() });
                    const data = await response.json();
                    eligible = response.ok && data.eligible === true;
                    button.disabled = !eligible;
                    if (eligible) {
                        button.textContent = data.action === 'return' ? 'Return This Laptop' : 'Take This Laptop';
                        setHelp(data.action === 'return' ? 'This laptop is assigned to you. You may return it now.' : 'Staff eligibility confirmed. You may take this laptop.');
                    } else {
                        button.textContent = 'Take This Laptop';
                        setHelp(data.action === 'unavailable' ? 'This laptop is currently assigned to another staff member.' : 'This NRIC is not registered to borrow a JHEP laptop.', true);
                    }
                } catch (_) { setHelp('Unable to verify your NRIC. Please try again.', true); }
            }, 350);
        });

        document.getElementById('borrowForm').addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!eligible) return;
            button.disabled = true;
            setHelp('Recording your laptop loan...');
            try {
                const response = await fetch(@json(route('laptops.borrow.store', $token)), { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, body: body() });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Unable to borrow this laptop.');
                result.textContent = data.message;
                result.hidden = false;
                input.disabled = true;
                setHelp(data.action === 'returned' ? 'Return recorded successfully.' : 'Loan recorded successfully. Please keep the laptop safe.');
            } catch (error) { eligible = false; setHelp(error.message, true); }
        });
    </script>
</body>
</html>

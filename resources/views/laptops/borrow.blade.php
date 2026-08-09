<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Borrow {{ $laptop->name }} | StudentEdge</title>
    @include('partials.brand_icons')
    <style>
        :root{color-scheme:light}*{box-sizing:border-box}body{min-height:100vh;margin:0;display:grid;place-items:center;padding:1.25rem;background:radial-gradient(circle at top right,#ead6bd,transparent 42%),linear-gradient(145deg,#f8f4ee,#e9ddd0);font-family:Inter,ui-sans-serif,system-ui,sans-serif;color:#2e2119}.borrow-card{width:min(100%,440px);overflow:hidden;border:1px solid rgba(108,75,46,.16);border-radius:28px;background:rgba(255,253,249,.88);box-shadow:0 24px 70px rgba(75,48,29,.18)}.borrow-head{padding:1.5rem;background:linear-gradient(135deg,#3b291d,#765237);color:#fff}.borrow-brand{display:flex;align-items:center;gap:.65rem;font-size:.78rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.borrow-brand img{width:31px;height:31px;object-fit:contain}.borrow-head h1{margin:1.2rem 0 .25rem;font-size:1.45rem}.borrow-head p{margin:0;color:#f4e6d7;font-size:.88rem}.borrow-body{padding:1.5rem}.borrow-status{margin-bottom:1rem;padding:.75rem .85rem;border-radius:12px;background:#edf7ef;color:#23633b;font-size:.84rem;font-weight:700}.borrow-field{display:grid;gap:.45rem}.borrow-field label{font-size:.75rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#715b49}.borrow-field input{min-height:52px;width:100%;padding:.8rem .9rem;border:1px solid #cbb9a7;border-radius:13px;background:#fff;font:inherit;font-size:1rem}.borrow-help{min-height:1.35rem;margin:.5rem 0 1.15rem;font-size:.82rem;color:#7b6553}.borrow-help.error{color:#ad3030}.borrow-button{width:100%;min-height:52px;border:0;border-radius:13px;background:#6d4b2e;color:#fff;font:inherit;font-weight:800;cursor:pointer}.borrow-button:disabled{cursor:not-allowed;opacity:.48}.borrow-result{margin-top:1rem;padding:.9rem;border-radius:13px;background:#e7f5eb;color:#245e37;font-size:.88rem;font-weight:700}.borrow-result[hidden]{display:none}.borrow-note{margin:1rem 0 0;color:#826c5a;font-size:.76rem;line-height:1.5}
    </style>
</head>
<body>
    <main class="borrow-card">
        <header class="borrow-head">
            <div class="borrow-brand"><img src="{{ asset('images/studentedge-mark.png') }}?v=10" alt="">StudentEdge</div>
            <h1>{{ $laptop->name }}</h1>
            <p>{{ $laptop->asset_code }} · JHEP laptop borrowing</p>
        </header>
        <section class="borrow-body">
            <div class="borrow-status">Enter your NRIC to borrow this laptop or return it if it is already assigned to you.</div>
            <form id="borrowForm">
                <div class="borrow-field"><label for="nric">NRIC number</label><input id="nric" name="nric" inputmode="numeric" autocomplete="off" maxlength="20" placeholder="e.g. 900101011234" required></div>
                <p class="borrow-help" id="borrowHelp">Enter your NRIC to confirm that you are eligible to borrow.</p>
                <button class="borrow-button" id="borrowButton" type="submit" disabled>Take This Laptop</button>
            </form>
            <div class="borrow-result" id="borrowResult" hidden></div>
            <p class="borrow-note">Only staff included in the JHEP laptop borrower registry can take a laptop. Your NRIC is used only to verify this loan.</p>
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

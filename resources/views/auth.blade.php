<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $mode === 'setup' ? 'Workspace setup title' : 'Login title' }}</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f5f7fb;color:#20283a;font-family:Tahoma,"Segoe UI",Arial,sans-serif;padding:20px}.card{width:min(100%,440px);background:#fff;border:1px solid #edf0f5;border-radius:18px;padding:34px;box-shadow:0 16px 48px #27304b0d}.brand{display:flex;align-items:center;gap:12px;margin-bottom:28px}.mark{width:42px;height:42px;border-radius:13px;background:linear-gradient(145deg,#827aff,#5d55d0);display:grid;place-items:center;color:#fff;font-size:21px;font-weight:bold}.brand strong{font-size:18px}.brand small{display:block;color:#8790a3;font-size:11px;margin-top:4px}h1{font-size:21px;margin:0 0 8px}p{font-size:12px;color:#8790a3;line-height:1.9;margin:0 0 24px}label{display:block;font-size:11px;font-weight:700;margin:15px 0 7px}input{width:100%;height:43px;border:1px solid #e3e7ef;border-radius:9px;padding:0 12px;font:inherit;font-size:12px;outline:none}input:focus{border-color:#635bdb}button{width:100%;height:44px;background:#635bdb;color:#fff;border:0;border-radius:9px;font:inherit;font-size:12px;font-weight:700;margin-top:22px;cursor:pointer}.error{color:#c53d48;font-size:11px;margin-top:6px}.note{background:#f0efff;color:#5a52be;border-radius:9px;padding:12px;font-size:11px;line-height:1.8;margin:18px 0}.language-toggle{position:fixed;inset-block-start:18px;inset-inline-end:18px;width:auto;height:36px;margin:0;padding:0 13px;background:#fff;color:#20283a;border:1px solid #e3e7ef}
    </style>
</head>
<body>
<button id="languageToggle" class="language-toggle" type="button">EN</button>
<main class="card">
    <div class="brand"><span class="mark">ط</span><span><strong data-i18n-key="Talal">Talal</strong><small data-i18n-key="Business management">Business management</small></span></div>
    @if ($mode === 'setup')
        <h1 data-i18n-key="Set up your company workspace">Set up your company workspace</h1>
        <p data-i18n-key="Create the first administrator account to get started. Setup registration is available once.">Create the first administrator account to get started. Setup registration is available once.</p>
        <div class="note" data-i18n-key="Choose a strong password of at least 10 characters. Once this account is created, administrator setup registration is disabled.">Choose a strong password of at least 10 characters. Once this account is created, administrator setup registration is disabled.</div>
        <form method="POST" action="{{ url('/setup') }}">
            @csrf
            <label for="name" data-i18n-key="Full name">Full name</label>
            <input id="name" name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="name">
            @error('name')<div class="error">{{ $message }}</div>@enderror
            <label for="email" data-i18n-key="Email address">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
            @error('email')<div class="error">{{ $message }}</div>@enderror
            <label for="password" data-i18n-key="Password">Password</label>
            <input id="password" name="password" type="password" required minlength="10" autocomplete="new-password">
            @error('password')<div class="error">{{ $message }}</div>@enderror
            <label for="password_confirmation" data-i18n-key="Confirm password">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required minlength="10" autocomplete="new-password">
            <button type="submit" data-i18n-key="Create workspace">Create workspace</button>
        </form>
    @else
        <h1 data-i18n-key="Welcome back">Welcome back</h1>
        <p data-i18n-key="Sign in to access your company workspace.">Sign in to access your company workspace.</p>
        <form method="POST" action="{{ url('/login') }}">
            @csrf
            <label for="email" data-i18n-key="Email address">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username">
            @error('email')<div class="error">{{ $message }}</div>@enderror
            <label for="password" data-i18n-key="Password">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password">
            <label style="display:flex;align-items:center;gap:8px;font-weight:400"><input type="checkbox" name="remember" value="1" style="width:15px;height:15px"><span data-i18n-key="Remember me">Remember me</span></label>
            <button type="submit" data-i18n-key="Sign in">Sign in</button>
        </form>
    @endif
</main>
<script>
    (function () {
        var language = 'ar';
        var translations = {
            ar: @json(trans('ui', [], 'ar')),
            en: @json(trans('ui', [], 'en'))
        };
        var isSetup = {{ $mode === 'setup' ? 'true' : 'false' }};
        function applyTranslations() {
            document.querySelectorAll('[data-i18n-key]').forEach(function (element) {
                var key = element.getAttribute('data-i18n-key');
                element.textContent = translations[language][key] || key;
            });
            document.title = translations[language][isSetup ? 'Workspace setup title' : 'Login title'];
        }
        applyTranslations();
        document.getElementById('languageToggle').addEventListener('click', function () {
            language = language === 'ar' ? 'en' : 'ar';
            document.documentElement.lang = language;
            document.documentElement.dir = language === 'ar' ? 'rtl' : 'ltr';
            applyTranslations();
            this.textContent = language === 'ar' ? 'EN' : 'ع';
        });
    }());
</script>
</body>
</html>

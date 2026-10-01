<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Імпорт заявок</title>
    <style>
        :root { font-family: Arial, sans-serif; color: #172033; background: #f4f6fb; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; display: grid; place-items: center; padding: 24px; }
        main { width: min(100%, 520px); padding: 32px; border: 1px solid #e4e8f0; border-radius: 14px; background: #fff; box-shadow: 0 12px 36px #23365d12; }
        h1 { margin: 0 0 10px; font-size: 26px; }
        p { color: #667085; line-height: 1.55; }
        label { display: block; margin: 22px 0 8px; font-weight: 700; }
        input { width: 100%; padding: 12px; border: 1px solid #cbd2df; border-radius: 8px; }
        button { width: 100%; margin-top: 16px; padding: 12px; border: 0; border-radius: 8px; background: #5267d8; color: #fff; font-size: 15px; font-weight: 700; cursor: pointer; }
        .success { padding: 12px; border-radius: 8px; background: #ecfdf3; color: #067647; }
        .error { margin-top: 8px; color: #b42318; }
        .hint { margin-top: 22px; padding-top: 16px; border-top: 1px solid #eef0f5; font-size: 13px; }
    </style>
</head>
<body>
<main>
    <h1>Імпорт заявок</h1>
    <p>Завантажте файл Excel у форматі XLSX. Великі файли обробляються у фоновому режимі.</p>

    @if (session('success'))
        <p class="success">{{ session('success') }}</p>
    @endif

    <form action="{{ route('leads.import.process') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <label for="file">Файл із заявками</label>
        <input id="file" type="file" name="file" accept=".xlsx" required>
        @error('file') <p class="error">{{ $message }}</p> @enderror
        <button type="submit">Імпортувати</button>
    </form>

    <p class="hint">Для виконання імпорту має працювати Laravel queue worker.</p>
</main>
</body>
</html>

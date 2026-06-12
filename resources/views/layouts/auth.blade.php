<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SchoolMS' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #3b6db5 0%, #6b8fc8 100%);
            display: flex; align-items: center; justify-content: center;
        }
        .auth-card {
            max-width: 460px; width: 100%;
            background: #fff; border-radius: 12px;
            box-shadow: 0 20px 50px rgba(0,0,0,.2);
            padding: 2.5rem;
        }
        .auth-logo { color: var(--brand); font-size: 1.6rem; font-weight: 700; }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="text-center mb-4">
            <i class="fas fa-school auth-logo"></i>
            <h3 class="auth-logo mt-2">SchoolMS</h3>
            <p class="text-muted small mb-0">Multi-tenant School Management</p>
        </div>
        @yield('content')
    </div>
</body>
</html>

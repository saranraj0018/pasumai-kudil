<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Expired :: PasumaiKudil</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #0f766e 0%, #134e4a 100%);
            padding: 20px;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
            max-width: 420px;
            width: 100%;
            padding: 40px 32px;
            text-align: center;
        }

        .icon-wrap {
            width: 84px;
            height: 84px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #ecfdf5;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-wrap svg {
            width: 42px;
            height: 42px;
            stroke: #0f766e;
        }

        h1 {
            font-size: 22px;
            color: #111827;
            margin: 0 0 10px;
        }

        p {
            color: #6b7280;
            font-size: 15px;
            line-height: 1.6;
            margin: 0 0 28px;
        }

        .reload-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 13px 20px;
            background: #0f766e;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .reload-btn:hover {
            background: #0d5f58;
        }

        .reload-btn svg {
            width: 18px;
            height: 18px;
        }

        .reload-btn.spinning svg {
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .code {
            margin-top: 22px;
            font-size: 12px;
            letter-spacing: 1px;
            color: #9ca3af;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="icon-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h1>Your Session Has Expired</h1>
        <p>For your security, this page timed out due to inactivity. Please reload the page and try again.</p>
        <a href="{{ route('admin.login') }}">
        <button type="button" class="reload-btn" id="reloadBtn">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Reload Page</span>
        </button>
        </a>
        <div class="code">Error 419 &middot; Page Expired</div>
    </div>
</body>

</html>

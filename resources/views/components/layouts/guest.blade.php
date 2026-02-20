<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Smart Inventory' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            background-color: #2c3e50;
            color: #333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background-color: #fff;
            border: 1px solid #bdc3c7;
            width: 100%;
            max-width: 400px;
            margin: 20px;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
        }
        .login-header {
            background-color: #2c3e50;
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        .login-header h1 {
            font-size: 18px;
            margin: 0;
        }
        .login-header small {
            font-size: 11px;
            color: #95a5a6;
        }
        .login-body {
            padding: 25px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-control {
            width: 100%;
            padding: 8px 10px;
            font-size: 12px;
            border: 1px solid #bdc3c7;
            border-radius: 3px;
        }
        .btn {
            width: 100%;
            padding: 10px;
            font-size: 12px;
            border: 1px solid transparent;
            cursor: pointer;
            border-radius: 3px;
        }
        .btn-primary {
            background-color: #3498db;
            color: #fff;
            border-color: #2980b9;
        }
        .btn-primary:hover {
            background-color: #2980b9;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 3px;
        }
        .alert-danger {
            background-color: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        .text-center {
            text-align: center;
        }
        .mt-3 {
            margin-top: 15px;
        }
        .text-muted {
            color: #7f8c8d;
            font-size: 11px;
        }
        .demo-accounts {
            background-color: #ecf0f1;
            padding: 10px;
            margin-top: 15px;
            font-size: 11px;
        }

        /* Mobile Responsive */
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            .login-box {
                margin: 10px;
                width: 100%;
            }
            .login-header {
                padding: 15px;
            }
            .login-header h1 {
                font-size: 16px;
            }
            .login-body {
                padding: 20px;
            }
            .form-control {
                font-size: 16px; /* Prevent zoom on iOS */
                padding: 10px;
            }
            .btn {
                padding: 12px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    {{ $slot }}
    @livewireScripts
</body>
</html>

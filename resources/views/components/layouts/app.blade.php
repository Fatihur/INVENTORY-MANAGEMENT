<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Smart Inventory' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            background-color: #f0f0f0;
            color: #333;
            max-width: 100vw;
            overflow-x: hidden;
        }
        .wrapper {
            display: flex;
            min-height: 100vh;
            max-width: 100vw;
            overflow-x: hidden;
        }

        /* Mobile Menu Toggle */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 8px;
            left: 8px;
            z-index: 1001;
            background: #2c3e50;
            color: #fff;
            border: none;
            padding: 10px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            min-width: 40px;
            min-height: 40px;
            touch-action: manipulation;
        }

        /* Sidebar */
        .sidebar {
            width: 220px;
            background-color: #2c3e50;
            color: #fff;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        .sidebar-header {
            padding: 15px;
            background-color: #1a252f;
            border-bottom: 1px solid #34495e;
        }
        .sidebar-header h1 {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
        }
        .sidebar-header small {
            font-size: 10px;
            color: #95a5a6;
        }
        .nav-menu {
            list-style: none;
            padding: 0;
        }
        .nav-item {
            border-bottom: 1px solid #34495e;
        }
        .nav-link {
            display: block;
            padding: 12px 15px;
            color: #ecf0f1;
            text-decoration: none;
            font-size: 12px;
            transition: all 0.2s ease;
        }
        .nav-link:hover {
            background-color: #34495e;
            padding-left: 20px;
        }
        .nav-link.active {
            background-color: #3498db;
            border-left: 4px solid #fff;
        }
        .nav-link i {
            width: 20px;
            display: inline-block;
            text-align: center;
            margin-right: 8px;
        }
        .nav-section {
            padding: 12px 15px;
            font-size: 11px;
            text-transform: uppercase;
            color: #95a5a6;
            font-weight: bold;
            border-bottom: 1px solid #34495e;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s ease;
            user-select: none;
        }
        .nav-section:hover {
            background-color: #34495e;
            color: #ecf0f1;
        }
        .nav-section i.toggle-icon {
            font-size: 10px;
            transition: transform 0.3s ease;
        }
        .nav-section.collapsed i.toggle-icon {
            transform: rotate(-90deg);
        }
        .nav-section.collapsed {
            border-bottom: none;
        }
        .nav-group {
            overflow: hidden;
            transition: max-height 0.3s ease;
            max-height: 1000px;
        }
        .nav-group.collapsed {
            max-height: 0;
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        .sidebar-overlay.active {
            display: block;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 220px;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
            max-width: calc(100vw - 220px);
        }
        .top-bar {
            background-color: #fff;
            border-bottom: 1px solid #bdc3c7;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 45px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .top-bar h2 {
            font-size: 14px;
            font-weight: normal;
            color: #2c3e50;
        }
        .user-info {
            font-size: 12px;
            color: #555;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-info a {
            color: #e74c3c;
            text-decoration: none;
        }
        .content-area {
            padding: 20px;
            flex: 1;
            max-width: 100%;
            overflow-x: hidden;
        }

        /* Cards & Panels */
        .panel {
            background-color: #fff;
            border: 1px solid #bdc3c7;
            margin-bottom: 15px;
            border-radius: 4px;
            transition: box-shadow 0.2s ease;
        }
        .panel:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .panel-header {
            background-color: #ecf0f1;
            padding: 10px 15px;
            border-bottom: 1px solid #bdc3c7;
            font-weight: bold;
            font-size: 12px;
            border-radius: 4px 4px 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .panel-body {
            padding: 15px;
        }

        /* Stats Cards Animation */
        .stat-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .data-table th {
            background-color: #ecf0f1;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #bdc3c7;
            font-weight: bold;
            white-space: nowrap;
        }
        .data-table th.sortable {
            cursor: pointer;
            user-select: none;
        }
        .data-table th.sortable:hover {
            background-color: #e0e0e0;
        }
        .data-table td {
            padding: 10px;
            border-bottom: 1px solid #ecf0f1;
            transition: background-color 0.15s ease;
        }
        .data-table tr {
            transition: background-color 0.15s ease;
        }
        .data-table tr:hover {
            background-color: #f8f9fa;
        }
        .data-table tr:hover td {
            background-color: #f8f9fa;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            font-size: 12px;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            border-radius: 3px;
            transition: all 0.2s ease;
        }
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .btn-primary {
            background-color: #3498db;
            color: #fff;
            border-color: #2980b9;
        }
        .btn-primary:hover:not(:disabled) {
            background-color: #2980b9;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(52,152,219,0.3);
        }
        .btn-success {
            background-color: #27ae60;
            color: #fff;
            border-color: #229954;
        }
        .btn-success:hover:not(:disabled) {
            background-color: #229954;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(39,174,96,0.3);
        }
        .btn-danger {
            background-color: #e74c3c;
            color: #fff;
            border-color: #c0392b;
        }
        .btn-danger:hover:not(:disabled) {
            background-color: #c0392b;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(231,76,60,0.3);
        }
        .btn-default {
            background-color: #ecf0f1;
            color: #333;
            border-color: #bdc3c7;
        }
        .btn-default:hover:not(:disabled) {
            background-color: #bdc3c7;
            transform: translateY(-1px);
        }
        .btn-warning {
            background-color: #f39c12;
            color: #fff;
            border-color: #d68910;
        }
        .btn-warning:hover:not(:disabled) {
            background-color: #d68910;
            transform: translateY(-1px);
        }
        .btn-sm {
            padding: 4px 8px;
            font-size: 11px;
        }
        .btn-icon {
            padding: 6px 8px;
        }

        /* Loading Spinner */
        .spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .spinner-dark {
            border-color: rgba(0,0,0,0.1);
            border-top-color: #333;
        }

        /* Forms */
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
            padding: 6px 10px;
            font-size: 12px;
            border: 1px solid #bdc3c7;
            border-radius: 3px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }
        .input-group {
            display: flex;
            position: relative;
        }
        .input-group .form-control {
            flex: 1;
        }
        .input-group-append {
            display: flex;
        }
        .input-clear {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #95a5a6;
            cursor: pointer;
            padding: 2px 5px;
            font-size: 12px;
        }
        .input-clear:hover {
            color: #e74c3c;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            font-size: 11px;
            border-radius: 3px;
            transition: transform 0.15s ease;
        }
        .badge:hover {
            transform: scale(1.05);
        }
        .badge-success {
            background-color: #27ae60;
            color: #fff;
        }
        .badge-warning {
            background-color: #f39c12;
            color: #fff;
        }
        .badge-danger {
            background-color: #e74c3c;
            color: #fff;
        }
        .badge-info {
            background-color: #3498db;
            color: #fff;
        }
        .badge-default {
            background-color: #95a5a6;
            color: #fff;
        }

        /* Filter Badges */
        .filter-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        .filter-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            background-color: #e8f4f8;
            border: 1px solid #3498db;
            color: #2980b9;
            border-radius: 20px;
            font-size: 11px;
            transition: all 0.2s ease;
        }
        .filter-badge:hover {
            background-color: #d4e6f1;
        }
        .filter-badge .remove {
            cursor: pointer;
            color: #e74c3c;
        }

        /* Pagination */
        .pagination {
            display: flex;
            list-style: none;
            margin-top: 15px;
            gap: 5px;
        }
        .pagination li a, .pagination li span {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            border: 1px solid #bdc3c7;
            color: #333;
            text-decoration: none;
            border-radius: 3px;
            transition: all 0.2s ease;
        }
        .pagination li a:hover {
            background-color: #ecf0f1;
            border-color: #95a5a6;
        }
        .pagination .active span {
            background-color: #3498db;
            color: #fff;
            border-color: #3498db;
        }
        .pagination .disabled span {
            color: #95a5a6;
            cursor: not-allowed;
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 60px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .toast {
            padding: 12px 20px;
            border-radius: 4px;
            color: #fff;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 280px;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
            cursor: pointer;
        }
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .toast.success {
            background-color: #27ae60;
        }
        .toast.error {
            background-color: #e74c3c;
        }
        .toast.warning {
            background-color: #f39c12;
        }
        .toast.info {
            background-color: #3498db;
        }
        .toast-close {
            margin-left: auto;
            background: none;
            border: none;
            color: #fff;
            cursor: pointer;
            opacity: 0.7;
        }
        .toast-close:hover {
            opacity: 1;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeIn 0.2s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .modal-content {
            background: #fff;
            border-radius: 4px;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Responsive Grid Utilities */
        .grid {
            display: grid;
            gap: 15px;
        }
        .grid-cols-1 { grid-template-columns: repeat(1, 1fr); }
        .grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-cols-4 { grid-template-columns: repeat(4, 1fr); }

        /* Flex Utilities */
        .flex-col { flex-direction: column; }
        .flex-row { flex-direction: row; }
        .flex-wrap { flex-wrap: wrap; }

        /* Responsive spacing */
        .responsive-flex {
            display: flex;
            gap: 15px;
        }
        .responsive-flex > * {
            flex: 1;
        }

        /* Mobile card view for tables */
        .mobile-card-view {
            display: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .mobile-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 44px;
                height: 44px;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            }
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
                max-width: 85vw;
            }
            .sidebar.open {
                transform: translateX(0);
                box-shadow: 4px 0 20px rgba(0,0,0,0.3);
            }
            .nav-link {
                padding: 16px 20px;
                font-size: 14px;
            }
            .nav-link i {
                width: 28px;
                font-size: 16px;
            }
            .nav-section {
                padding: 14px 18px;
                font-size: 11px;
                border-bottom: 1px solid #34495e;
                cursor: pointer;
            }
            .nav-section:hover {
                background-color: #34495e;
            }
            .nav-section i.toggle-icon {
                font-size: 10px;
            }
            .nav-group {
                max-height: none !important;
            }
            .nav-group.collapsed {
                max-height: 0 !important;
            }
            .main-content {
                margin-left: 0;
                max-width: 100vw;
                width: 100vw;
                overflow-x: hidden;
            }
            .content-area {
                padding: 10px;
                min-height: calc(100vh - 60px);
                max-width: 100vw;
                width: 100%;
                overflow-x: hidden;
            }
            .top-bar {
                padding: 12px 15px;
                height: auto;
                min-height: 55px;
                position: sticky;
                top: 0;
            }
            .top-bar h2 {
                margin-left: 50px;
                font-size: 14px;
                line-height: 1.3;
            }
            .user-info {
                gap: 8px;
            }
            .user-info span {
                display: none;
            }
            .user-info .btn {
                padding: 8px 12px;
                font-size: 12px;
            }

            /* Grid responsive - ensure single column and fit container */
            .grid {
                width: 100%;
                max-width: 100%;
                grid-template-columns: 1fr !important;
            }
            .grid-cols-1,
            .grid-cols-2,
            .grid-cols-3,
            .grid-cols-4 {
                grid-template-columns: 1fr !important;
                width: 100%;
                max-width: 100%;
            }

            /* Report cards on mobile */
            .grid > div[style*="cursor: pointer"] {
                padding: 15px !important;
                min-height: 100px;
            }
            .grid > div[style*="cursor: pointer"] h3 {
                font-size: 13px !important;
            }

            /* Responsive flex */
            .responsive-flex {
                flex-direction: column;
            }
            .responsive-flex > * {
                flex: none;
                width: 100%;
            }

            /* Form inputs full width on mobile */
            .form-control {
                font-size: 16px; /* Prevent zoom on iOS */
            }
            .input-group {
                width: 100%;
            }

            /* Filter section on mobile */
            .filter-row {
                flex-direction: column !important;
                gap: 10px !important;
            }
            .filter-row > div {
                width: 100% !important;
            }
            .filter-row select,
            .filter-row input {
                width: 100% !important;
            }

            /* Stat cards on mobile */
            .stat-card {
                margin-bottom: 10px;
                min-height: 80px;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
            }
            .stat-card .panel-body {
                width: 100%;
                text-align: center;
                padding: 12px;
            }
            .stat-card .text-2xl {
                font-size: 20px;
            }

            /* Panels on mobile */
            .panel {
                width: 100%;
                max-width: 100%;
                overflow: hidden;
            }

            /* Tables - Make scrollable on mobile */
            .data-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
                font-size: 12px;
                -webkit-overflow-scrolling: touch;
                width: 100%;
                max-width: 100%;
            }
            .data-table th,
            .data-table td {
                padding: 12px 10px;
                min-width: 80px;
            }
            .data-table th:first-child,
            .data-table td:first-child {
                min-width: auto;
            }
            .panel-body {
                overflow-x: auto;
                padding: 12px;
                -webkit-overflow-scrolling: touch;
                width: 100%;
                max-width: 100%;
            }

            /* Touch-friendly action buttons */
            .action-link {
                padding: 8px 10px;
                display: inline-block;
                min-width: 36px;
                text-align: center;
            }

            /* Buttons on mobile */
            .btn {
                padding: 8px 12px;
                font-size: 13px;
                min-height: 36px; /* Touch friendly */
            }
            .btn-sm {
                padding: 6px 10px;
                font-size: 12px;
            }

            /* Action buttons group */
            .action-buttons {
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
                justify-content: flex-end;
                width: 100%;
            }
            .page-header .action-buttons {
                justify-content: stretch;
            }
            .action-buttons .btn,
            .action-buttons a {
                flex: 1;
                min-width: 90px;
                min-height: 40px;
                text-align: center;
                justify-content: center;
                padding: 10px 14px;
                font-size: 13px;
            }

            /* Modal on mobile */
            .modal-content {
                width: 95% !important;
                max-width: none !important;
                margin: 10px;
            }
            .modal-overlay {
                padding: 10px;
                align-items: flex-start;
                overflow-y: auto;
            }

            /* Toast on mobile */
            .toast-container {
                left: 10px;
                right: 10px;
                top: 60px;
            }
            .toast {
                min-width: auto;
                max-width: none;
                width: 100%;
            }

            /* Grid layouts in modals */
            .modal-grid {
                grid-template-columns: 1fr !important;
            }
            .modal-grid > .modal-full-width,
            .modal-grid > [style*="grid-column: span 2"] {
                grid-column: span 1 !important;
            }

            /* Order items grid */
            .items-grid {
                display: flex !important;
                flex-direction: column !important;
                gap: 10px !important;
            }
            .items-grid > div {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 8px !important;
                padding: 10px !important;
            }
            .items-grid .form-control {
                width: 100% !important;
            }

            /* Pagination on mobile */
            .pagination {
                flex-wrap: wrap;
                justify-content: center;
                gap: 4px;
            }
            .pagination li a,
            .pagination li span {
                padding: 10px 14px;
                font-size: 13px;
                min-width: 40px;
                min-height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            /* Filter badges */
            .filter-badges {
                gap: 5px;
            }
            .filter-badge {
                font-size: 10px;
                padding: 3px 8px;
            }

            /* Header actions */
            .page-header {
                flex-direction: column !important;
                gap: 10px !important;
                align-items: stretch !important;
                width: 100%;
                max-width: 100%;
            }
            .page-header h2 {
                font-size: 14px;
                word-break: break-word;
            }
            .page-header .btn {
                width: 100%;
                justify-content: center;
            }

            /* Empty state */
            .empty-state {
                padding: 30px 15px;
            }
            .empty-state-icon {
                font-size: 36px;
            }

            /* Hide some table columns on mobile */
            .hide-mobile {
                display: none !important;
            }
        }

        /* Report cards on mobile */
        .grid > div[style*="cursor: pointer"] {
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        /* Tablet view */
        @media (min-width: 769px) and (max-width: 1024px) {
            .grid-cols-4 {
                grid-template-columns: repeat(2, 1fr);
            }
            .grid-cols-3 {
                grid-template-columns: repeat(2, 1fr);
            }
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
                max-width: calc(100vw - 200px);
            }
        }

        /* Landscape mobile */
        @media (max-width: 768px) and (orientation: landscape) {
            .sidebar {
                max-height: 100vh;
                overflow-y: auto;
            }
            .modal-content {
                max-height: 80vh;
            }
        }

        /* Utilities */
        .mb-3 { margin-bottom: 15px; }
        .mt-3 { margin-top: 15px; }
        .mr-2 { margin-right: 8px; }
        .ml-2 { margin-left: 8px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .d-flex { display: flex; }
        .justify-between { justify-content: space-between; }
        .align-center { align-items: center; }
        .gap-2 { gap: 8px; }
        .gap-3 { gap: 15px; }
        .flex-wrap { flex-wrap: wrap; }
        .w-100 { width: 100%; }

        /* Action Links */
        .action-link {
            color: #3498db;
            text-decoration: none;
            padding: 2px 6px;
            border-radius: 3px;
            transition: all 0.15s ease;
        }
        .action-link:hover {
            background-color: #ebf5fb;
            text-decoration: underline;
        }
        .action-link.danger {
            color: #e74c3c;
        }
        .action-link.danger:hover {
            background-color: #fdedec;
        }
        .action-link.success {
            color: #27ae60;
        }
        .action-link.success:hover {
            background-color: #eafaf1;
        }

        /* Skeleton Loading */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 3px;
        }
        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</head>
<body>
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Mobile Toggle -->
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <div class="wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h1><i class="fas fa-warehouse"></i> SMART INVENTORY</h1>
                <small>v1.0</small>
            </div>

            <ul class="nav-menu">
                {{-- Main Menu --}}
                <li class="nav-section" onclick="toggleNavGroup('nav-main')">
                    <span>Main</span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </li>
                <ul class="nav-group" id="nav-main">
                    <li class="nav-item">
                        <a wire:navigate href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>

                    @can('products.view')
                    <li class="nav-item">
                        <a wire:navigate href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                            <i class="fas fa-box"></i> Products
                        </a>
                    </li>
                    @endcan

                    @can('stock.view')
                    <li class="nav-item">
                        <a wire:navigate href="{{ route('stock.index') }}" class="nav-link {{ request()->routeIs('stock.*') ? 'active' : '' }}">
                            <i class="fas fa-warehouse"></i> Stock
                        </a>
                    </li>
                    @endcan

                    @can('suppliers.view')
                    <li class="nav-item">
                        <a wire:navigate href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                            <i class="fas fa-truck"></i> Suppliers
                        </a>
                    </li>
                    @endcan

                    @can('purchase-orders.view')
                    <li class="nav-item">
                        <a wire:navigate href="{{ route('purchase-orders.index') }}" class="nav-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
                            <i class="fas fa-file-invoice"></i> Purchase Orders
                        </a>
                    </li>
                    @endcan

                    @can('restock.view')
                    <li class="nav-item">
                        <a wire:navigate href="{{ route('restock.recommendations') }}" class="nav-link {{ request()->routeIs('restock.*') ? 'active' : '' }}">
                            <i class="fas fa-sync-alt"></i> Restock
                        </a>
                    </li>
                    @endcan
                </ul>

                {{-- Sales Menu --}}
                @role('owner|admin|manager')
                <li class="nav-section" onclick="toggleNavGroup('nav-sales')">
                    <span>Sales</span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </li>
                <ul class="nav-group" id="nav-sales">
                    <li class="nav-item">
                        <a wire:navigate href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i> Customers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a wire:navigate href="{{ route('sales-orders.index') }}" class="nav-link {{ request()->routeIs('sales-orders.*') ? 'active' : '' }}">
                            <i class="fas fa-shopping-cart"></i> Sales Orders
                        </a>
                    </li>
                </ul>
                @endrole

                {{-- Warehouse Menu --}}
                @role('owner|admin|warehouse')
                <li class="nav-section" onclick="toggleNavGroup('nav-warehouse')">
                    <span>Warehouse</span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </li>
                <ul class="nav-group" id="nav-warehouse">
                    <li class="nav-item">
                        <a wire:navigate href="{{ route('batches.index') }}" class="nav-link {{ request()->routeIs('batches.*') ? 'active' : '' }}">
                            <i class="fas fa-tags"></i> Batches
                        </a>
                    </li>
                    <li class="nav-item">
                        <a wire:navigate href="{{ route('warehouses.index') }}" class="nav-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
                            <i class="fas fa-building"></i> Warehouses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a wire:navigate href="{{ route('stock-opname.index') }}" class="nav-link {{ request()->routeIs('stock-opname.*') ? 'active' : '' }}">
                            <i class="fas fa-clipboard-check"></i> Stock Opname
                        </a>
                    </li>
                    <li class="nav-item">
                        <a wire:navigate href="{{ route('bin-locations.index') }}" class="nav-link {{ request()->routeIs('bin-locations.*') ? 'active' : '' }}">
                            <i class="fas fa-map-marker-alt"></i> Bin Locations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a wire:navigate href="{{ route('settings.scanner') }}" class="nav-link {{ request()->routeIs('settings.scanner') ? 'active' : '' }}">
                            <i class="fas fa-barcode"></i> Scanner Settings
                        </a>
                    </li>
                </ul>
                @endrole

                {{-- Management Menu --}}
                @role('owner|admin|manager|warehouse|purchasing')
                <li class="nav-section" onclick="toggleNavGroup('nav-management')">
                    <span>Management</span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </li>
                <ul class="nav-group" id="nav-management">
                    @role('owner|admin|manager')
                    <li class="nav-item">
                        <a wire:navigate href="{{ route('approvals.index') }}" class="nav-link {{ request()->routeIs('approvals.*') ? 'active' : '' }}">
                            <i class="fas fa-check-circle"></i> Approvals
                        </a>
                    </li>
                    @endrole

                    @role('owner|admin|warehouse|purchasing|manager')
                    <li class="nav-item">
                        <a wire:navigate href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                    </li>
                    @endrole

                    @role('owner|admin')
                    <li class="nav-item">
                        <a wire:navigate href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="fas fa-user-cog"></i> Users
                        </a>
                    </li>
                    @endrole
                </ul>
                @endrole
            </ul>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <h2>{{ $header ?? 'Dashboard' }}</h2>
                <div class="user-info">
                    <span>{{ auth()->user()->name }} ({{ auth()->user()->roles->first()->name }})</span>
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </div>

            <div class="content-area">
                {{ $slot }}
            </div>
        </div>
    </div>

    <script>
        // Mobile Sidebar Toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        }

        // Toggle Nav Group Collapse
        function toggleNavGroup(groupId) {
            const group = document.getElementById(groupId);
            const section = document.querySelector(`[onclick="toggleNavGroup('${groupId}')"]`);

            if (!group || !section) return;

            // Toggle current group
            const willBeCollapsed = !group.classList.contains('collapsed');

            if (willBeCollapsed) {
                // Collapse this group
                group.classList.add('collapsed');
                section.classList.add('collapsed');
            } else {
                // Expand this group and collapse others (accordion behavior)
                group.classList.remove('collapsed');
                section.classList.remove('collapsed');

                // Collapse other groups
                const allGroups = ['nav-main', 'nav-sales', 'nav-warehouse', 'nav-management'];
                allGroups.forEach(otherGroupId => {
                    if (otherGroupId !== groupId) {
                        const otherGroup = document.getElementById(otherGroupId);
                        const otherSection = document.querySelector(`[onclick="toggleNavGroup('${otherGroupId}')"]`);
                        if (otherGroup && otherSection) {
                            otherGroup.classList.add('collapsed');
                            otherSection.classList.add('collapsed');
                            localStorage.setItem(`nav_${otherGroupId}_collapsed`, 'true');
                        }
                    }
                });
            }

            // Save collapse state to localStorage
            localStorage.setItem(`nav_${groupId}_collapsed`, willBeCollapsed);
        }

        // Restore collapse state on page load
        document.addEventListener('DOMContentLoaded', () => {
            const groups = ['nav-main', 'nav-sales', 'nav-warehouse', 'nav-management'];
            let hasExpandedGroup = false;

            // First pass: check if any group should be expanded
            groups.forEach(groupId => {
                const storedValue = localStorage.getItem(`nav_${groupId}_collapsed`);
                if (storedValue === 'false') {
                    hasExpandedGroup = true;
                }
            });

            // Second pass: apply states
            groups.forEach(groupId => {
                const group = document.getElementById(groupId);
                const section = document.querySelector(`[onclick="toggleNavGroup('${groupId}')"]`);

                if (!group || !section) return;

                const storedValue = localStorage.getItem(`nav_${groupId}_collapsed`);
                let isCollapsed;

                if (storedValue === null) {
                    // No stored value: collapse all except the first group (nav-main)
                    isCollapsed = groupId !== 'nav-main';
                } else {
                    isCollapsed = storedValue === 'true';
                }

                // If no group is explicitly expanded and this is the first group, expand it
                if (!hasExpandedGroup && groupId === 'nav-main') {
                    isCollapsed = false;
                }

                if (isCollapsed) {
                    group.classList.add('collapsed');
                    section.classList.add('collapsed');
                } else {
                    group.classList.remove('collapsed');
                    section.classList.remove('collapsed');
                }
            });
        });

        // Close sidebar when clicking on a link (mobile)
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    toggleSidebar();
                }
            });
        });

        // Handle Livewire page navigation to restore sidebar state
        document.addEventListener('livewire:navigated', () => {
            const groups = ['nav-main', 'nav-sales', 'nav-warehouse', 'nav-management'];
            groups.forEach(groupId => {
                const group = document.getElementById(groupId);
                const section = document.querySelector(`[onclick="toggleNavGroup('${groupId}')"]`);

                if (!group || !section) return;

                const storedValue = localStorage.getItem(`nav_${groupId}_collapsed`);
                const isCollapsed = storedValue === 'true';

                if (isCollapsed) {
                    group.classList.add('collapsed');
                    section.classList.add('collapsed');
                } else {
                    group.classList.remove('collapsed');
                    section.classList.remove('collapsed');
                }
            });
        });

        // Toast Notification System
        window.showToast = function(message, type = 'success', duration = 3000) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;

            const icons = {
                success: 'fa-check-circle',
                error: 'fa-times-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };

            toast.innerHTML = `
                <i class="fas ${icons[type]}"></i>
                <span>${message}</span>
                <button class="toast-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;

            container.appendChild(toast);

            // Auto remove
            setTimeout(() => {
                toast.style.animation = 'slideIn 0.3s ease reverse';
                setTimeout(() => toast.remove(), 300);
            }, duration);

            // Click to remove
            toast.addEventListener('click', (e) => {
                if (!e.target.closest('.toast-close')) {
                    toast.style.animation = 'slideIn 0.3s ease reverse';
                    setTimeout(() => toast.remove(), 300);
                }
            });
        };

        // Clear search input
        window.clearSearch = function(inputId) {
            const input = document.getElementById(inputId);
            if (input) {
                input.value = '';
                input.dispatchEvent(new Event('input'));
                input.focus();
            }
        };

        // Confirm delete
        window.confirmDelete = function(message = 'Are you sure you want to delete this item?') {
            return confirm(message);
        };

        // Handle Livewire events for toast notifications
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('toast', (params) => {
                showToast(params.message, params.type || 'success');
            });

            Livewire.on('success', (message) => {
                showToast(message, 'success');
            });

            Livewire.on('error', (message) => {
                showToast(message, 'error');
            });
        });

        // Auto-hide sidebar on resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                document.getElementById('sidebar').classList.remove('open');
                document.getElementById('sidebarOverlay').classList.remove('active');
            }
        });
    </script>

    @livewireScripts
</body>
</html>

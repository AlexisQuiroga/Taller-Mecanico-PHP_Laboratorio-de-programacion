<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TallerMec</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        #sidebar {
            width: 250px;
            min-height: 100vh;
            background-color: #1b2a4a;
            flex-shrink: 0;
        }
        #page-content {
            flex: 1;
            overflow-x: hidden;
        }
        #sidebar .nav-link {
            color: rgba(255,255,255,0.75);
            padding: 0.6rem 1.2rem;
            border-radius: 0.375rem;
            margin: 2px 8px;
        }
        #sidebar .nav-link:hover, #sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: #fff;
        }
        #sidebar .sidebar-header {
            padding: 1.5rem 1.2rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        #sidebar .user-info {
            padding: 1rem 1.2rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        @media (max-width: 768px) {
            #sidebar {
                position: fixed;
                left: -250px;
                top: 0;
                z-index: 1050;
                transition: left 0.3s;
            }
            #sidebar.show {
                left: 0;
            }
            #overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                z-index: 1040;
            }
            #overlay.show {
                display: block;
            }
        }
    </style>
</head>
<body class="bg-light">
<div class="d-flex" id="wrapper">

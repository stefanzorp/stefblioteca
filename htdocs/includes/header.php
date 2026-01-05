<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stefblioteca</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
    /* 1. Forțăm fundalul paginii să fie deschis, dar conținutul să fie vizibil */
    body { 
        background-color: #f0f2f5 !important; 
        color: #212529 !important; 
    }

    /* 2. REPARARE LINK-URI INVIZIBILE: Le forțăm să fie albastre sau negre, NU albe */
    a { 
        color: #0d6efd !important; /* Albastru standard Bootstrap */
        text-decoration: none;
    }
    a:hover { 
        color: #0a58ca !important; 
        text-decoration: underline;
    }

    /* 3. REPARARE BUTOANE: Dacă ai butoane care apar albe, le dăm contrast */
    .btn-primary { background-color: #0d6efd !important; color: white !important; }
    .btn-success { background-color: #198754 !important; color: white !important; }
    .btn-danger  { background-color: #dc3545 !important; color: white !important; }
    .btn-warning { background-color: #ffc107 !important; color: #212529 !important; }

    /* 4. REPARARE NAVBAR: Ne asigurăm că meniul de sus e negru cu scris alb clar */
    .navbar { background-color: #212529 !important; }
    .navbar-brand, .nav-link { color: #ffffff !important; }

    /* 5. BOX-UL ALB central care conține tot textul */
    .main-container { 
        margin-top: 30px; 
        margin-bottom: 50px; 
        background: #ffffff !important; 
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        min-height: 400px;
    }

    /* Forțăm titlurile să fie închise la culoare */
    h1, h2, h3, h4, h5 { color: #2c3e50 !important; }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand" href="/index.php">📚 Stefblioteca</a>
    <div class="navbar-nav ms-auto">
        <a class="nav-link text-white" href="/carti/carti_list.php">Cărți</a>
        <a class="nav-link text-white" href="/contact.php">Contact</a>
    </div>
  </div>
</nav>

<div class="container main-container">